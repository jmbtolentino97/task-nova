<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    /**
     * Handle the Project "creating" event.
     */
    public function creating(Project $project): void
    {
        $project->created_by = auth()->user()->id;
        $project->updated_by = auth()->user()->id;
    }

    /**
     * Handle the Project "updating" event.
     */
    public function updating(Project $project): void
    {
        $project->updated_by = auth()->user()->id;
    }
}
