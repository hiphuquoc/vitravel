<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    protected $table = 'redirect_info';

    protected $fillable = [
        'url_old',
        'url_new',
    ];

    public $timestamps = false;
}
