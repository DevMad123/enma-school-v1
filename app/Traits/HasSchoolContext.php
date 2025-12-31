<?php

namespace App\Traits;

use App\Models\School;

trait HasSchoolContext
{
    /**
     * Get the active school
     *
     * @return School|null
     */
    protected function getActiveSchool()
    {
        return School::getActiveSchool();
    }

    /**
     * Ensure a school exists, redirect to school creation if not
     */
    protected function ensureSchoolExists()
    {
        if (!$this->getActiveSchool()) {
            return redirect()->route('admin.schools.create')
                ->with('warning', 'Veuillez d\'abord configurer votre établissement.');
        }

        return null;
    }

    /**
     * Get a school setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSchoolSetting(string $key, $default = null)
    {
        $school = $this->getActiveSchool();
        return $school ? $school->getSetting($key, $default) : $default;
    }
}