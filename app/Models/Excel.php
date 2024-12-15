<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Excel extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'excels'; 
    public $timestamps = false;

    /**
     * 
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'date',
    ];
} 