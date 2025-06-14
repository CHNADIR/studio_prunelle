<?php

namespace App\Tests\Unit\Service;

use App\Service\UploaderHelper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString; // Ajoutez cette import

class UploaderHelperTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject|SluggerInterface $sluggerMock;
    private \PHPUnit\Framework\MockObject\MockObject|Filesystem $filesystemMock;
    private \PHPUnit\Framework\MockObject\MockObject|LoggerInterface $loggerMock;
    private string $kernelProjectDir;
    private string $expectedPrivateUploadsPath;
    private UploaderHelper $uploaderHelper;

    protected function setUp(): void
    {
        $this->sluggerMock = $this->createMock(SluggerInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->kernelProjectDir = sys_get_temp_dir() . '/studio_prunelle_test_project'; // Dummy project dir for tests
        $this->expectedPrivateUploadsPath = $this->kernelProjectDir . '/' . UploaderHelper::PLANCHE_PRIVATE_STORAGE_PATH;

        $this->uploaderHelper = new UploaderHelper(
            $this->kernelProjectDir,
            $this->sluggerMock,
            $this->filesystemMock,
            $this->loggerMock
        );
    }

    public function testUploadPlancheImageSuccessNewFile(): void
    {
        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('Test Image.jpg');
        $uploadedFileMock->method('guessExtension')->willReturn('jpg');

        $this->sluggerMock->method('slug')
            ->with('Test Image')
            ->willReturn(new UnicodeString('test-image')); // Utilisez UnicodeString ici

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with($this->expectedPrivateUploadsPath)
            ->willReturn(true); // Directory already exists

        $this->filesystemMock->expects($this->never())
            ->method('mkdir');

        $uploadedFileMock->expects($this->once())
            ->method('move')
            ->with(
                $this->equalTo($this->expectedPrivateUploadsPath),
                $this->matchesRegularExpression('/^test-image-[a-f0-9]+\.jpg$/')
            );

        $this->filesystemMock->expects($this->never()) // No existing file to remove
            ->method('remove');

        $newFilename = $this->uploaderHelper->uploadPlancheImage($uploadedFileMock);
        $this->assertMatchesRegularExpression('/^test-image-[a-f0-9]+\.jpg$/', $newFilename);
    }

    public function testUploadPlancheImageSuccessReplacingExistingFile(): void
    {
        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('New Image.png');
        $uploadedFileMock->method('guessExtension')->willReturn('png');

        $this->sluggerMock->method('slug')
            ->with('New Image')
            ->willReturn(new UnicodeString('new-image')); // Utilisez UnicodeString ici

        $this->filesystemMock->method('exists')
            ->willReturnMap([
                [$this->expectedPrivateUploadsPath, true], // Directory exists
                [$this->expectedPrivateUploadsPath . '/old-image.png', true] // Old file exists
            ]);


        $uploadedFileMock->expects($this->once())
            ->method('move')
            ->with(
                $this->equalTo($this->expectedPrivateUploadsPath),
                $this->matchesRegularExpression('/^new-image-[a-f0-9]+\.png$/')
            );

        // Expect remove to be called for the existing file
        $this->filesystemMock->expects($this->once())
            ->method('remove')
            ->with($this->expectedPrivateUploadsPath . '/old-image.png');

        $existingFilename = 'old-image.png';
        $newFilename = $this->uploaderHelper->uploadPlancheImage($uploadedFileMock, $existingFilename);
        $this->assertMatchesRegularExpression('/^new-image-[a-f0-9]+\.png$/', $newFilename);
    }

    public function testUploadPlancheImageCreatesDirectoryIfNotExists(): void
    {
        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('Another Image.gif');
        $uploadedFileMock->method('guessExtension')->willReturn('gif');

        $this->sluggerMock->method('slug')
            ->with('Another Image')
            ->willReturn(new UnicodeString('another-image')); // Utilisez UnicodeString ici

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with($this->expectedPrivateUploadsPath)
            ->willReturn(false); // Directory does NOT exist

        $this->filesystemMock->expects($this->once())
            ->method('mkdir')
            ->with($this->expectedPrivateUploadsPath, 0750);

        $uploadedFileMock->expects($this->once())->method('move');

        $this->uploaderHelper->uploadPlancheImage($uploadedFileMock);
    }

    public function testUploadPlancheImageMoveThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Impossible de sauvegarder l\'image. Veuillez réessayer.');

        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('Error Image.jpg');
        $uploadedFileMock->method('guessExtension')->willReturn('jpg');
        $this->sluggerMock->method('slug')
            ->willReturn(new UnicodeString('error-image')); // Utilisez UnicodeString ici

        $this->filesystemMock->method('exists')->willReturn(true);

        $uploadedFileMock->method('move')
            ->willThrowException(new \Exception('Disk full or something.'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Failed to upload image: Disk full or something.'),
                $this->arrayHasKey('exception')
            );

        $this->uploaderHelper->uploadPlancheImage($uploadedFileMock);
    }

    public function testRemovePlancheImageSuccess(): void
    {
        $filename = 'image-to-remove.jpg';
        $expectedFilePath = $this->expectedPrivateUploadsPath . '/' . $filename;

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with($expectedFilePath)
            ->willReturn(true);

        $this->filesystemMock->expects($this->once())
            ->method('remove')
            ->with($expectedFilePath);

        $this->uploaderHelper->removePlancheImage($filename);
    }

    public function testRemovePlancheImageFileDoesNotExist(): void
    {
        $filename = 'non-existent-image.jpg';
        $expectedFilePath = $this->expectedPrivateUploadsPath . '/' . $filename;

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with($expectedFilePath)
            ->willReturn(false);

        $this->filesystemMock->expects($this->never()) // Remove should not be called
            ->method('remove');

        $this->uploaderHelper->removePlancheImage($filename);
    }

    public function testRemovePlancheImageNullFilename(): void
    {
        $this->filesystemMock->expects($this->never())->method('exists');
        $this->filesystemMock->expects($this->never())->method('remove');

        $this->uploaderHelper->removePlancheImage(null);
    }

    public function testRemovePlancheImageFilesystemThrowsException(): void
    {
        $filename = 'error-on-remove.jpg';
        $expectedFilePath = $this->expectedPrivateUploadsPath . '/' . $filename;

        $this->filesystemMock->method('exists')
            ->with($expectedFilePath)
            ->willReturn(true);

        $this->filesystemMock->method('remove')
            ->with($expectedFilePath)
            ->willThrowException(new \Exception('Permission denied.'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Failed to remove image: Permission denied.'),
                $this->callback(function ($context) use ($filename) {
                    return isset($context['exception']) && $context['filename'] === $filename;
                })
            );

        // The method should not re-throw the exception, it just logs it.
        $this->uploaderHelper->removePlancheImage($filename);
    }

    public function testGetPrivateFilePath(): void
    {
        $filename = 'my-image.png';
        $expectedPath = $this->expectedPrivateUploadsPath . '/' . $filename;
        $this->assertSame($expectedPath, $this->uploaderHelper->getPrivateFilePath($filename));
    }

    protected function tearDown(): void
    {
        // Clean up dummy project directory if it was created by a real filesystem interaction (not typical for unit tests)
        // For these mocked tests, this is not strictly necessary, but good practice if tests ever touch the real FS.
        // if (is_dir($this->kernelProjectDir)) {
        //     // A more robust cleanup would be needed if files were actually created
        // }
        parent::tearDown();
    }
}