<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienceActivity extends Model
{
    protected $table = 'experience_activities';

    protected $fillable = [
        'experience_id', 'day_id', 'title', 'time_label', 'description',
        'tags', 'pro_tip', 'image', 'sort_order',
    ];

    protected $casts = [
        'tags'       => 'array',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function experience() { return $this->belongsTo(Experience::class); }
    public function day()        { return $this->belongsTo(ExperienceDay::class, 'day_id'); }
}