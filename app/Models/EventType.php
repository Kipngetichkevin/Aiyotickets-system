<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;

    protected $table = 'event_types'; // Specify the table name if different from the default
    protected $primaryKey = 'id'; // Specify the primary key if different from the default
    public $incrementing = true; // Indicates if the ID is auto-incrementing
    public $timestamps = true; // Indicates if the model should use timestamps

    protected $fillable = [
        'name',
        'description',
        'icon',
    ];

    // Define any relationships if needed
}