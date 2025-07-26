<?php

namespace App\Repositories;

use App\Models\Service;

class ServiceRepository
{
    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }

    public function getAll()
    {
        return Service::all();
    }
}