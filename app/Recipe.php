<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'image',
        'recipe_name',
        'recipe_description',
    ];
}
