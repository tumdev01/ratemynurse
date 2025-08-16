<?php

namespace Database\Seeders;

use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ImageSeeder extends Seeder
{
    public function run()
    {
        $userIds = [3,4,5,6,24,25,26,27,28,29];

        foreach ($userIds as $id) {
            $pattern = public_path("images/{$id}.{png,PNG,jpg,JPG}");
            $files = glob($pattern, GLOB_BRACE);

            foreach ($files as $sourcePath) {
                if (!is_file($sourcePath)) continue;

                $extension = File::extension($sourcePath);
                $hashedName = md5(uniqid($id, true)) . '.' . $extension;
                $destPath = 'images/' . $hashedName;
                $destFullPath = public_path($destPath);

                File::ensureDirectoryExists(dirname($destFullPath));
                File::copy($sourcePath, $destFullPath);

                Image::create([
                    'user_id'  => $id,
                    'type'     => 'NURSING',
                    'name'     => (string) $id,
                    'path'     => $destPath,
                    'filetype' => 'image/' . strtolower($extension),
                    'is_cover' => true,
                ]);
            }
        }

        // Nursing Home 
        $userIds = [13,14,15,16,17,18,19,20,21,22,30,31,32,33];

        foreach ($userIds as $id) {
            // หาไฟล์รองรับ png, PNG, jpg, JPG
            $pattern = public_path("images/{$id}-*.{png,PNG,jpg,JPG}");
            $files = glob($pattern, GLOB_BRACE);

            foreach ($files as $sourcePath) {
                if (!is_file($sourcePath)) continue;

                $extension = File::extension($sourcePath);
                $fileBaseName = basename($sourcePath, '.' . $extension);
                $hashedName = md5(uniqid($id, true)) . '.' . $extension;
                $destPath = 'images/' . $hashedName;
                $destFullPath = public_path($destPath);

                File::ensureDirectoryExists(dirname($destFullPath));
                File::copy($sourcePath, $destFullPath);

                Image::create([
                    'user_id'  => $id,
                    'type'     => 'NURSING',
                    'name'     => $fileBaseName,
                    'path'     => $destPath,
                    'filetype' => 'image/' . strtolower($extension),
                    'is_cover' => substr($fileBaseName, -2) === '-1',
                ]);
            }
        }
    }
}
