<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use LaravelIdea\Helper\App\Models\_IH_Service_C;

class ServiceRepository
{
    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }

    public function getAll(): Collection|_IH_Service_C|array
    {
        return Service::all();
    }
}
