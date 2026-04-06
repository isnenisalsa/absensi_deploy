<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'nrp';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
}
