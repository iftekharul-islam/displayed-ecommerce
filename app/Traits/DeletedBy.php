<?php

namespace App\Traits;

trait DeletedBy
{
    public static function bootDeletedBy()
    {
        static::deleting(function ($model) {
            $model->deleted_by = auth()->user()->id ?? null;
            $model->saveQuietly();
        });
    }
}
