<?php

namespace App\Service;

use App\Entity\InternalProfile;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class InternalProfileProcessor
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
    public function processFormSubmission(InternalProfile $profile): void
    {
        //Verificar Cedula
        $ident = $profile->getId() ?: null;
        $profileCheck = $this->entityManager->getRepository(InternalProfile::class)->getUserByValueforCheck(
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
        $profileCheck = $this->entityManager->getRepository(InternalProfile::class)->getUserByValueforCheck(
            'telefono',
            $profile->getTelefono(),
            $ident
        );
        if ($profileCheck) {
            throw new BusinessRuleException('Ya existe un usuario registrado con ese telefono, por favor verifique.');
        }

        //no errors
        $this->entityManager->persist($profile);
        $this->entityManager->flush();
    }
}

