<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    use BelongsToProject;

    protected $table = 'redirect_info';

    protected $fillable = [
        'project_id',
        'url_old',
        'url_new',
    ];

    public $timestamps = false;
}
