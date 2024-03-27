<?php

namespace App\Models;

use App\Models\Scopes\OwnedProjectScope;
use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ProjectObserver::class])]
#[ScopedBy([OwnedProjectScope::class])]
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
}
