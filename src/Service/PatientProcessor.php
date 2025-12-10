<?php

namespace App\Service;

use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PatientProcessor
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploader $fileUploader,
        // Agregar servicios adicionales aca para que el autowire los inyecte
    ){}

    /**
     * Handles all file uploads, custom validation, and persistence for a Paciente entity.
     *
     * @throws BusinessRuleException If any custom business validation fails.
     */
    public function processFormSubmission(Paciente $paciente, ?UploadedFile $fotoFile): void
    {
        // ----------------------------------------------------
        // A. Business Logic Check 1: Subida de archivo
        // ----------------------------------------------------
        if ($fotoFile) {
            try {
                $fotoFileName = $this->fileUploader->upload($fotoFile);
                $paciente->setFoto($fotoFileName);

            } catch (\Exception $e) {
                // Translate the low-level FileException into a high-level BusinessRuleException
                throw new BusinessRuleException(
                    'Ocurrió un error al subir la foto del paciente. Detalles: ' . $e->getMessage()
                );
            }
        }

        // ----------------------------------------------------
        // B. Business Logic Check 2: Example Custom Validation
        // ----------------------------------------------------
        if (strtoupper($paciente->getNombre()) === 'TEST') {
            throw new BusinessRuleException('El nombre "TEST" está reservado y no puede ser usado.');
        }

        // ----------------------------------------------------
        // C. Business Logic Check 3: Verificar Cedula
        // ----------------------------------------------------
        $pacienteCheck = $this->entityManager->getRepository(Paciente::class)->findOneBy([
            'cedula' => $paciente->getCedula(),
            'status' => $this->entityManager->getRepository(StatusRecord::class)->getActive()
        ]);
        if ($pacienteCheck) {
            throw new BusinessRuleException('Ya existe un paciente registrado con esa cedula, por favor verifique.');
        }

        // ----------------------------------------------------
        // D. Business Logic Check 4: Verificar el telefono
        // ----------------------------------------------------
        $pacienteCheck = $this->entityManager->getRepository(Paciente::class)->findOneBy([
            'telefono' => $paciente->getTelefono(),
            'status' => $this->entityManager->getRepository(StatusRecord::class)->getActive()
        ]);
        if ($pacienteCheck) {
            throw new BusinessRuleException('Ya existe un paciente registrado con ese telefono, por favor verifique.');
        }

        //no errors
        $this->entityManager->persist($paciente);
        $this->entityManager->flush();
    }
}
