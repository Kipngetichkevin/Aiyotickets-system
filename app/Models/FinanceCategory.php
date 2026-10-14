<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** What money is spent on (Rent, Salaries, Marketing…), for the expense reports. */
class FinanceCategory extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function payments()
    {
        return $this->hasMany(FinancePayment::class, 'category_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'category_id');
    }
}
