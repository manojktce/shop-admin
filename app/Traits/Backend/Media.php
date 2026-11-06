<?php

namespace App\Traits\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait Media
{
    public function uploadFile(
        $path, 
        $field, 
        $oldFile = null
    ) {
        $request = request();

        $path = str_replace(url('storage') . '/', '', $path);

        if (!$request->hasFile($field)) {
            return $oldFile;
        }

        $file = $request->file($field);

        if (!($file instanceof UploadedFile)) {
            return $oldFile;
        }

        if ($oldFile) {
            Storage::disk('public')->delete($path . '/' . $oldFile);
        }

        $stored = $file->store($path, 'public');

        return basename($stored);
    }


    public function saveFile(
        $model, 
        $field, 
        $path, 
        $file
    ) {
        if ($file instanceof UploadedFile) {
            $path = str_replace(url('storage') . '/', '', $path);

            if ($model->{$field}) {
                Storage::disk('public')->delete($path . '/' . $model->{$field});
            }

            $stored = $file->store($path, 'public');

            $model->update([$field => basename($stored)]);
        } else if (is_null($file)) {
            if ($model->{$field}) {
                Storage::disk('public')->delete($path . '/' . $model->{$field});
            }
            
            $model->update([$field => null]);
        }
    }

    public function saveMultipleFiles(
        $model,
        $relation,
        $field,
        $path,
        $files
    ) {
        if (empty($files)) return;

        $path = str_replace(url('storage') . '/', '', $path);

        foreach ($files as $file) {
            $stored = $file->store($path, 'public');

            $model->{$relation}()->create([
                $field => basename($stored),
            ]);
        }
    }

    public function removeMultipleFiles(
        $model,
        $relation,
        $field,
        $path,
        $images
    ) {
        if (empty($images)) return;

        $path = str_replace(url('storage') . '/', '', $path);
        
        foreach (
            $model->{$relation}()
                ->whereIn($field, $images)
                ->get() as $record
        ) {
            Storage::disk('public')->delete(
                $path . '/' . $record->{$field}
            );

            $record->delete();
        }
    }

    public function getFiles(
        $model,
        $relation,
        $field
    ) {
        return $model->{$relation}()->pluck($field)->toArray();
    }
}
