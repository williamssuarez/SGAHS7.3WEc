<?php

namespace App\Service;

use App\Entity\InternalProfile;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Exception\BusinessRuleException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class InternalProfileProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileUploader $fileUploader,
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    /**
     * Handles all file uploads, custom validation, and persistence for a ExternalProfile entity.
     *
     * @throws BusinessRuleException If any custom business validation fails.
     */
    public function processFormSubmission(InternalProfile $profile, ?UploadedFile $avatarFile): void
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

        //Subida de archivo (checkear antes si usuario ya tiene foto)
        if ($avatarFile) {
            //search previos picture
            $prevFile = $profile->getWebUser()->getAvatarUrl();
            try {
                //if there's a previous file, delete it
                if ($prevFile) {
                    $this->fileUploader->delete($prevFile);
                }

                //save foto
                $newFileName = $this->fileUploader->upload($avatarFile);
                $profile->getWebUser()->setAvatarUrl($newFileName);

            } catch (\Exception $e) {
                // Translate the low-level FileException into a high-level BusinessRuleException
                throw new BusinessRuleException(
                    'Ocurrió un error al subir la foto del usuario. Detalles: ' . $e->getMessage()
                );
            }
        }

        //no errors
        $profile->getWebUser()->setPassword($this->userPasswordHasher->hashPassword($profile->getWebUser(), '12345678'));
        

        $this->entityManager->persist($profile);
        $this->entityManager->flush();
    }
}

