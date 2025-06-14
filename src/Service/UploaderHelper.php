<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;

class UploaderHelper
{
    private string $privateUploadsPath;
    private SluggerInterface $slugger;
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    // Chemin relatif depuis la racine du projet vers le stockage privé des planches
    public const PLANCHE_PRIVATE_STORAGE_PATH = 'var/uploads/planches'; // Vérifiez que c'est bien ce chemin

    public function __construct(
        string $kernelProjectDir,
        SluggerInterface $slugger,
        Filesystem $filesystem, // Assurez-vous que Filesystem est injecté
        LoggerInterface $logger
    ) {
        $this->privateUploadsPath = $kernelProjectDir . '/' . self::PLANCHE_PRIVATE_STORAGE_PATH;
        $this->slugger = $slugger;
        $this->filesystem = $filesystem; // Et assigné
        $this->logger = $logger;
    }

    public function uploadPlancheImage(UploadedFile $uploadedFile, ?string $existingFilename = null): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        // Crée le répertoire s'il n'existe pas
        if (!$this->filesystem->exists($this->privateUploadsPath)) {
            $this->filesystem->mkdir($this->privateUploadsPath, 0750); // Permissions plus restrictives
        }

        try {
            $uploadedFile->move(
                $this->privateUploadsPath, // Déplacer vers le stockage privé
                $newFilename
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload image: ' . $e->getMessage(), ['exception' => $e]);
            throw new \RuntimeException('Impossible de sauvegarder l\'image. Veuillez réessayer.');
        }

        if ($existingFilename) {
            $this->removePlancheImage($existingFilename);
        }

        return $newFilename;
    }

    public function removePlancheImage(?string $filename): void
    {
        if ($filename) {
            $filePath = $this->privateUploadsPath . '/' . $filename;
            if ($this->filesystem->exists($filePath)) {
                try {
                    $this->filesystem->remove($filePath);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to remove image: ' . $e->getMessage(), ['exception' => $e, 'filename' => $filename]);
                }
            }
        }
    }

    // La méthode getPublicPath n'est plus pertinente pour les fichiers privés.
    // public function getPublicPath(string $filename): string
    // {
    //     return '/' . self::PLANCHE_PUBLIC_PATH . '/' . $filename; // ANCIENNE LOGIQUE
    // }

    public function getPrivateFilePath(string $filename): string
    {
        // $filename est juste le nom du fichier, ex: "image-abc.jpg"
        // Le sous-dossier 'planches' est déjà dans $this->privateUploadsPath
        return $this->privateUploadsPath . '/' . $filename;
    }
}