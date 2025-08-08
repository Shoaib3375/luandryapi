<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use LaravelIdea\Helper\App\Models\_IH_Service_C;

class ServiceRepository
{
    public function findById(int $id): ?Service
    {
        return Cache::remember("service:{$id}", 7200, fn() => Service::find($id));
    }

    public function getAll(): Collection|_IH_Service_C|array
    {
        return Cache::remember('services:all', 3600, fn() => Service::all());
    }

    public function clearCache(int $id = null): void
    {
        Cache::forget('services:all');
        if ($id) {
            Cache::forget("service:{$id}");
        }
    }
}
