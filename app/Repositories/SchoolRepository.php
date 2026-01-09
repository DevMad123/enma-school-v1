<?php

namespace App\Repositories;

use App\Models\School;
use Illuminate\Database\Eloquent\Collection;

class SchoolRepository
{
    /**
     * Trouve une école par ID ou échoue
     *
     * @param int $id
     * @return School
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): School
    {
        return School::findOrFail($id);
    }

    /**
     * Trouve la première école
     *
     * @return School|null
     */
    public function getFirst(): ?School
    {
        return School::where('is_active', true)->first();
    }

    /**
     * Récupère toutes les écoles actives
     *
     * @return Collection<int, School>
     */
    public function getActive(): Collection
    {
        return School::where('is_active', true)->get();
    }

    /**
     * Trouve une école par type
     *
     * @param string $type
     * @return School|null
     */
    public function findByType(string $type): ?School
    {
        return School::where('type', $type)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Récupère les écoles par type
     *
     * @param string $type
     * @return Collection<int, School>
     */
    public function getByType(string $type): Collection
    {
        return School::where('type', $type)
            ->where('is_active', true)
            ->get();
    }
}