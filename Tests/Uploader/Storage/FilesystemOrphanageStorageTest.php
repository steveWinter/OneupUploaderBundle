<?php

namespace Oneup\UploaderBundle\Tests\Uploader\Storage;

use Oneup\UploaderBundle\Uploader\Chunk\Storage\FilesystemStorage as FilesystemChunkStorage;
use Oneup\UploaderBundle\Uploader\File\FilesystemFile;
use Oneup\UploaderBundle\Uploader\Storage\FilesystemOrphanageStorage;
use Oneup\UploaderBundle\Uploader\Storage\FilesystemStorage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;

class FilesystemOrphanageStorageTest extends OrphanageTest
{
    public function setUp()
    {
        $this->numberOfPayloads = 5;
        $this->tempDirectory = sys_get_temp_dir().'/orphanage';
        $this->realDirectory = sys_get_temp_dir().'/storage';
        $this->payloads = [];

        $filesystem = new Filesystem();
        $filesystem->mkdir($this->tempDirectory);
        $filesystem->mkdir($this->realDirectory);

        for ($i = 0; $i < $this->numberOfPayloads; ++$i) {
            // create temporary file
            $file = tempnam(sys_get_temp_dir(), 'uploader');

            $pointer = fopen($file, 'w+');
            fwrite($pointer, str_repeat('A', 1024), 1024);
            fclose($pointer);

            // create an uploaded file to upload
            // TODO at EOL of SF 3.4 this can be removed
            if(Kernel::VERSION_ID < 40400) {
                $uploadedFile = new UploadedFile($file, $i.'grumpycat.jpeg', null, null, null, true);
            } else {
                $uploadedFile = new UploadedFile($file, $i.'grumpycat.jpeg', null, null, true);
            }

            $this->payloads[] = new FilesystemFile($uploadedFile);
        }

        // create underlying storage
        $this->storage = new FilesystemStorage($this->realDirectory);
        // is ignored anyways
        $chunkStorage = new FilesystemChunkStorage('/tmp/');

        // create orphanage
        $session = new Session(new MockArraySessionStorage());
        $session->start();

        $config = ['directory' => $this->tempDirectory];

        $this->orphanage = new FilesystemOrphanageStorage($this->storage, $session, $chunkStorage, $config, 'cat');
    }
}
