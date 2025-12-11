<?php
// src/Service/FileUploader.php
namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
        private  readonly Filesystem $filesystem,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new \RuntimeException('Unable to upload the file.', 0, $e);

            // para pasar el error crudo
            // throw $e;
        }

        return $fileName;
    }

    public function delete(string $fileName): void
    {
        $filePath = $this->getTargetDirectory() . '/' . $fileName;

        // Check if the file exists and is not a default/placeholder image
        if ($this->filesystem->exists($filePath)) {
            try {
                $this->filesystem->remove($filePath);
            } catch (\Exception $e) {
                // Create a log service and log this
            }
        }
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
