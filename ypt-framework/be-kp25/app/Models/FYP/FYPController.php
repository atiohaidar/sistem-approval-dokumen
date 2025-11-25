<?php

namespace App\Models\FYP;

use Illuminate\Database\Eloquent\Model;

class FYPController extends Model
{
    protected $connection = 'framework';
    protected $table = 'controllers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'namespaces',
        'application_id',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
}
