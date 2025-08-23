<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Upload the file to the storage
     * @param UploadedFile $file
     * @return File
     */
    public static function upload(UploadedFile $file)
    {
        $originalName = $file->getClientOriginalName();
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = 'uploads/' . $fileName;

        Storage::disk('public')->put($filePath, file_get_contents($file));

        return File::create([
            'original_name' => $originalName,
            'name' => $fileName,
            'path' => $filePath,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
        ]);
    }

    /**
     * Link the file to the model
     * @param File $file
     * @param string $model_type
     * @param int $model_id
     * @param int $is_main
     */
    public static function linkModel(File $file, $model_type = null, $model_id = null, $is_main = 0)
    {
        //If the file is not found, return
        if(!$file) return;

        //Link the file to the model
        $file->update([
            'is_main' => $is_main,
            'model_type' => $model_type,
            'model_id' => $model_id,
        ]);
    }

    /**
     * Delete the file from the storage and the database
     * @param File $file
     */
    public static function delete(File $file)
    {
        //If the file is not found, return
        if(!$file) return;

        //Delete the file from the storage
        if(Storage::disk('public')->exists($file->path)) Storage::disk('public')->delete($file->path);

        //Delete the file from the database
        $file->delete();
    }
}