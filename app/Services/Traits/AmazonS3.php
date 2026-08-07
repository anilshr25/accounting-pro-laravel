<?php

namespace App\Services\Traits;

use App\Exceptions\InfrastructureConfigurationException;
use App\Services\Infrastructure\ScopedStorageService;
use Illuminate\Support\Str;

trait AmazonS3
{
    protected $disk = 'wasabi';

    public function uploadToS3($uploadPath, $realPath, $visibility = 'private', $thumbPath = null, $fileName = null)
    {
        $storage = app(ScopedStorageService::class);

        $uploadPathReal = "$uploadPath/$fileName";
        $uploadPathThumb = "$uploadPath/thumb/$fileName";

        $uploadPathReal = buildUploadPathUrl($uploadPathReal);
        $uploadPathThumb = buildUploadPathUrl($uploadPathThumb);

        if (empty($realPath) || ! is_file($realPath)) {
            throw new InfrastructureConfigurationException('The source file does not exist for scoped storage upload.');
        }

        $storage->put($uploadPathReal, \File::get($realPath));
        if (! empty($thumbPath) && is_file($thumbPath)) {
            $storage->put($uploadPathThumb, \File::get($thumbPath));
        }

        if (is_file($realPath)) {
            \File::delete($realPath);
        }

        if (is_file($thumbPath)) {
            \File::delete($thumbPath);
        }

        return true;
    }

    public function deleteFromS3($uploadPath, $imageName)
    {
        $storage = app(ScopedStorageService::class);
        $path = [];
        $parentPath = "$uploadPath/$imageName";
        $thumbPath = "$uploadPath/thumb/$imageName";

        $parentPath = buildUploadPathUrl($parentPath);
        $thumbPath = buildUploadPathUrl($thumbPath);

        if (! empty($parentPath)) {
            array_push($path, $parentPath);
        }

        if (! empty($thumbPath)) {
            array_push($path, $thumbPath);
        }
        if (count($path) > 0) {
            return $storage->delete($path);
        }

        return true;
    }

    public function createThumbS3($file, $width = 320, $height = 320)
    {
        $imageFile = \Image::make($file)->resize($width, $height)->stream();
        $imageFile = $imageFile->__toString();

        return $imageFile;
    }

    public function deleteFileFromS3($path)
    {
        return app(ScopedStorageService::class)->delete($path);
    }

    public function setFolderPermission()
    {
        $s3 = app(ScopedStorageService::class)->filesystem();
        $directoryArray = $s3->allDirectories();
        if (count($directoryArray) > 0) {
            foreach ($directoryArray as $index => $dirPath) {
                $s3->setVisibility($dirPath, 'private');
            }
        }

        return true;
    }

    public function setFilePermission()
    {
        $s3 = app(ScopedStorageService::class)->filesystem();
        $fileArray = $s3->allFiles();
        foreach ($fileArray as $i => $file) {
            if (! Str::contains($file, '.jpeg') && ! Str::contains($file, '.png') && ! Str::contains($file, '.jpg')) {
                unset($fileArray[$i]);
            }
        }
        if (count($fileArray) > 0) {
            foreach ($fileArray as $index => $filePath) {
                $s3->setVisibility($filePath, 'private');
            }
        }

        return true;
    }

    public function listFilesAndFolder($path, $type = 'files')
    {
        $s3 = app(ScopedStorageService::class)->filesystem();
        if ($type == 'files') {
            if ($path) {
                return $s3->allFiles($path);
            }

            return $s3->allFiles();
        } else {
            return $s3->allDirectories();
        }
    }

    public function deleteAllFiles($path)
    {
        $storage = app(ScopedStorageService::class);
        $s3 = $storage->filesystem();
        $paths = $s3->allFiles($path);

        return $storage->delete($paths);
    }
}
