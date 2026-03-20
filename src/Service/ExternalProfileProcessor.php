<?php

namespace App\Service;

use App\Entity\ExternalProfile;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ExternalProfileProcessor
{
    public function __construct(private EntityManagerInterface $entityManager, private FileUploader $fileUploader
    )
    {
    }

    /**
     * Handles all file uploads, custom validation, and persistence for a ExternalProfile entity.
     *
     * @throws BusinessRuleException If any custom business validation fails.
     */
    public function processFormSubmission(ExternalProfile $profile): void
    {

        //Verificar Cedula
        $ident = $profile->getId() ?: null;
        $profileCheck = $this->entityManager->getRepository(ExternalProfile::class)->getUserByValueforCheck(
            'nroDocumento',
            $profile->getNroDocumento(),
            $ident,
            'tipoDocumento',
            $profile->getTipoDocumento(),
        );
        if ($profileCheck) {
            throw new BusinessRuleException('Ya existe un usuario registrado con ese documento, por favor verifique.');
        }

        //Verificar el telefono
        $ident = $profile->getId() ?: null;
        $profileCheck = $this->entityManager->getRepository(ExternalProfile::class)->getUserByValueforCheck(
            'telefono',
            $profile->getTelefono(),
            $ident
        );
        if ($profileCheck) {
            throw new BusinessRuleException('Ya existe un usuario registrado con ese telefono, por favor verifique.');
        }

        //verificar paciente en tabla pacientes
        $paciente = $this->entityManager->getRepository(Paciente::class)->findOneBy([
            'tipoDocumento' => $profile->getTipoDocumento(),
            'cedula' => $profile->getNroDocumento(),
            'status' => $this->entityManager->getRepository(StatusRecord::class)->getActive(),
            'sangreTipo' => $profile->getSangreTipo(),
            'sexo' => $profile->getSexo(),
        ]);

        if ($paciente) {
            // Check if the last name they typed matches the one in the DB (case-insensitive)
            // You could also use Date of Birth (Fecha de Nacimiento) which is even safer.
            $dbDate = $paciente->getFechaNacimiento()->format('Y-m-d');
            $formDate = $profile->getFechaNacimiento()->format('Y-m-d');

            if ($dbDate === $formDate) {
                // It's a match! Link them safely.
                $profile->setPaciente($paciente);
            } else {
                // The Cédula exists, but the names don't match. STOP THE PROCESS.
                throw new BusinessRuleException('El documento ingresado ya pertenece a un paciente, pero los datos no coinciden. Por favor, contacte a admisión.');
            }
        } else {
            // OPTIONAL: What if they don't exist in the hospital yet?
            $newPatient = new Paciente();
            $newPatient->setNombre($profile->getNombre());
            $newPatient->setApellido($profile->getApellido());
            $newPatient->setTipoDocumento($profile->getTipoDocumento());
            $newPatient->setCedula($profile->getNroDocumento());
            $newPatient->setDireccion($profile->getDireccion());
            $newPatient->setTelefono($profile->getTelefono());
            $newPatient->setFechaNacimiento($profile->getFechaNacimiento());
            $newPatient->setFallecido(false);
            $newPatient->setSangreTipo($profile->getSangreTipo());
            $newPatient->setSexo($profile->getSangreTipo());
            $newPatient->setCorreo($profile->getWebUser()->getEmail());
            $newPatient->setFoto($profile->getFoto());

            $this->entityManager->persist($newPatient);
            $profile->setPaciente($newPatient);
        }

        //no errors
        $this->entityManager->persist($profile);
        $this->entityManager->flush();
    }
}

