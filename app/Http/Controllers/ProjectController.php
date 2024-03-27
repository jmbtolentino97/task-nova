<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class ProjectController extends Controller
{
    protected function getModelInstance(): Model
    {
        return new Project();
    }
}
