<?php

namespace App\Service;

use App\Exception\BusinessRuleException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class AttachmentUploader
{
    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    public function __construct(
        private string $targetDirectory,
        private Filesystem $filesystem,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        // 1. Validar tamaño no vacío y límite de seguridad
        $fileSize = $file->getSize();
        if ($fileSize <= 0) {
            throw new BusinessRuleException('El archivo subido está vacío o dañado.');
        }

        if ($fileSize > self::MAX_FILE_SIZE) {
            throw new BusinessRuleException('El archivo excede el tamaño máximo permitido de 5MB.');
        }

        // 2. Inspección estricta de Magic Bytes / Mime Type Real (usando finfo internamente)
        $realMimeType = $file->getMimeType();
        if (!array_key_exists($realMimeType, self::ALLOWED_MIME_TYPES)) {
            throw new BusinessRuleException('Tipo de archivo no permitido. Solo se aceptan documentos PDF o imágenes JPG/PNG.');
        }

        // 3. Inspección profunda de estructura según el tipo
        $filePath = $file->getPathname();
        if ($realMimeType === 'application/pdf') {
            $this->validatePdfHeader($filePath);
        } elseif (str_starts_with($realMimeType, 'image/')) {
            $this->validateImageIntegrity($filePath);
        }

        $extension = self::ALLOWED_MIME_TYPES[$realMimeType];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!$this->filesystem->exists($this->targetDirectory)) {
            $this->filesystem->mkdir($this->targetDirectory, 0700);
        }

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new BusinessRuleException('No se pudo guardar el documento médico confidencial.');
        }

        return $fileName;
    }

    private function validatePdfHeader(string $path): void
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            throw new BusinessRuleException('No se pudo leer el archivo PDF para validar su integridad.');
        }
        $header = fread($handle, 5);
        fclose($handle);

        // Todo documento PDF válido comienza con la firma "%PDF-"
        if ($header !== '%PDF-') {
            throw new BusinessRuleException('El archivo no es un documento PDF válido o su estructura interna está corrupta.');
        }
    }

    private function validateImageIntegrity(string $path): void
    {
        // getimagesize verifica que el contenido binario sea una imagen decodificable
        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            throw new BusinessRuleException('El archivo de imagen está corrupto o contiene una estructura interna inválida.');
        }
    }

    public function delete(string $fileName): void
    {
        $filePath = $this->getFilePath($fileName);
        if ($this->filesystem->exists($filePath)) {
            try {
                $this->filesystem->remove($filePath);
            } catch (\Exception $e) {
                // Ignore removal failure or log if needed
            }
        }
    }

    public function getFilePath(string $fileName): string
    {
        return $this->targetDirectory . '/' . basename($fileName);
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
