<?php

namespace App\Service;

use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class PatientProcessor
{
    public function __construct(private EntityManagerInterface $entityManager, private FileUploader $fileUploader
    ){}

    /**
     * Handles all file uploads, custom validation, and persistence for a Paciente entity.
     *
     * @throws BusinessRuleException If any custom business validation fails.
     */
    public function processFormSubmission(Paciente $paciente, ?UploadedFile $fotoFile): void
    {
        // ----------------------------------------------------
        // A. Business Logic Check 1: Example Custom Validation
        // ----------------------------------------------------
        if (strtoupper($paciente->getNombre()) === 'TEST') {
            throw new BusinessRuleException('El nombre "TEST" está reservado y no puede ser usado.');
        }

        // ----------------------------------------------------
        // B. Business Logic Check 2: Verificar Cedula
        // ----------------------------------------------------
        $ident = $paciente->getId() ? $paciente->getId() : null;
        $pacienteCheck = $this->entityManager->getRepository(Paciente::class)->getPatientbyValueforCheck(
            'cedula',
            $paciente->getCedula(),
            $ident,
            'tipoDocumento',
            $paciente->getTipoDocumento()
        );
        if ($pacienteCheck) {
            throw new BusinessRuleException('Ya existe un paciente registrado con ese documento, por favor verifique.');
        }

        // ----------------------------------------------------
        // C. Business Logic Check 3: Verificar el telefono
        // ----------------------------------------------------
        $ident = $paciente->getId() ? $paciente->getId() : null;
        $pacienteCheck = $this->entityManager->getRepository(Paciente::class)->getPatientbyValueforCheck(
            'telefono',
            $paciente->getTelefono(),
            $ident
        );
        if ($pacienteCheck) {
            throw new BusinessRuleException('Ya existe un paciente registrado con ese telefono, por favor verifique.');
        }

        // ----------------------------------------------------
        // E. Business Logic Check 5: Subida de archivo (checkear antes si paciente ya tiene foto)
        // ----------------------------------------------------
        if ($fotoFile) {
            //search previos picture
            $prevFoto = $paciente->getFoto();
            try {
                //if there's a previous foto, delete it
                if ($prevFoto) {
                    $this->fileUploader->delete($prevFoto);
                }

                //save foto
                $fotoFileName = $this->fileUploader->upload($fotoFile);
                $paciente->setFoto($fotoFileName);

            } catch (\Exception $e) {
                // Translate the low-level FileException into a high-level BusinessRuleException
                throw new BusinessRuleException(
                    'Ocurrió un error al subir la foto del paciente. Detalles: ' . $e->getMessage()
                );
            }
        }

        //no errors
        $this->entityManager->persist($paciente);
        $this->entityManager->flush();
    }
}
