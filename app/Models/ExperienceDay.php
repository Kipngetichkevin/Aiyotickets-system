<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienceDay extends Model
{
    protected $table = 'experience_days';

    protected $fillable = [
        'experience_id', 'day_number', 'date', 'title', 'description', 'sort_order',
    ];

    protected $casts = [
        'date'       => 'date',
        'day_number' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function experience() { return $this->belongsTo(Experience::class); }
    public function activities() { return $this->hasMany(ExperienceActivity::class, 'day_id')->orderBy('sort_order'); }
}