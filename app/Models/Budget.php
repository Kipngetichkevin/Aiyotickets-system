<?php

// app/Models/Budget.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service;


class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'point_of_contact',
        'phone',
        'email',
        'address',
        'role',
        'category',
        'cost',
        'contract_signature',
        'logo',
    ];

    // Define any relationships or additional methods here

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function getGrandTotalCostAttribute()
    {
        return $this->services->sum(function ($service) {
            return $service->price * $service->qty;
        });
    }


}