<?php

namespace App\View\Components;

use App\Helpers\ActivityHelper;
use Illuminate\View\Component;

class SupervisionLayout extends Component
{
    public function render()
    {
        return view('components.supervision-layout');
    }

    public function getActivityText($activity)
    {
        return ActivityHelper::getActivityText($activity);
    }

    public function getActionText($action)
    {
        return ActivityHelper::getActionText($action);
    }

    public function getActivityColor($action)
    {
        return ActivityHelper::getActivityColor($action);
    }

    public function getEntityIcon($entity)
    {
        return ActivityHelper::getEntityIcon($entity);
    }
}