<?php

namespace App\Service;

use App\Exception\BusinessRuleException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileUploader
{
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    public function __construct(
        private string     $targetDirectory,
        private Filesystem $filesystem,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        // 1. Validar tamaño no vacío y límite de seguridad
        $fileSize = $file->getSize();
        if ($fileSize <= 0) {
            throw new BusinessRuleException('La imagen subida está vacía o dañada.');
        }

        if ($fileSize > self::MAX_FILE_SIZE) {
            throw new BusinessRuleException('La imagen excede el tamaño máximo permitido de 5MB.');
        }

        // 2. Inspección estricta de Magic Bytes / Mime Type Real (finfo)
        $realMimeType = $file->getMimeType();
        if (!array_key_exists($realMimeType, self::ALLOWED_IMAGE_MIMES)) {
            throw new BusinessRuleException('Tipo de imagen no permitido. Solo se aceptan formatos JPG, PNG o WEBP.');
        }

        // 3. Inspección profunda de estructura binaria (getimagesize)
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new BusinessRuleException('El archivo no es una imagen válida o contiene una estructura corrupta.');
        }

        $extension = self::ALLOWED_IMAGE_MIMES[$realMimeType];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!$this->filesystem->exists($this->targetDirectory)) {
            $this->filesystem->mkdir($this->targetDirectory, 0755);
        }

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new BusinessRuleException('No se pudo guardar la imagen de perfil, por favor intente más tarde.');
        }

        return $fileName;
    }

    public function delete(string $fileName): void
    {
        $filePath = $this->getTargetDirectory() . '/' . basename($fileName);

        if ($this->filesystem->exists($filePath)) {
            try {
                $this->filesystem->remove($filePath);
            } catch (\Exception $e) {
                // Ignore removal failure or log if needed
            }
        }
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
