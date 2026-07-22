<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    protected $table = 'merchants'; // Specify the table name if different
    protected $primaryKey = 'id'; // Specify the primary key if different
    public $incrementing = true; // Indicates if the IDs are auto-incrementing
    public $timestamps = true; // Indicates if the model should be timestamped

    protected $fillable = [
        'user_id',      // Foreign key to the users table
        'name',         // Merchant's name
        'phone',        // Merchant's phone number
        'email',        // Merchant's email address
        'description',  // Merchant's description
        'location',     // Merchant's location
    ];

    // Define the relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class); // Assuming 'user_id' is the foreign key in the merchants table
    }
}