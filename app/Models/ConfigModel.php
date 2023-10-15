<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigModel extends Model
{
    protected $casts = [
        'value' => 'array'
    ];

    protected $table = 'config';

    public $timestamps = false;

}
