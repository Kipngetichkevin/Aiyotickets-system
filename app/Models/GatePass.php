<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** A gate link: scan-only access to one event or experience, for staff without an account. */
class GatePass extends Model
{
    protected $fillable = ['event_id', 'experience_id', 'label', 'expires_at'];

    protected $casts = [
        'expires_at'   => 'datetime',
        'revoked_at'   => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['token', 'pin_hash'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function experience()
    {
        return $this->belongsTo(Experience::class);
    }

    /** A new pass with an unguessable token; the PIN (optional) is stored hashed. */
    public static function issue(array $attributes, ?string $pin, User $by): self
    {
        $pass = new self($attributes);
        $pass->forceFill([
            'token'      => Str::random(40),
            'pin_hash'   => $pin ? Hash::make($pin) : null,
            'created_by' => $by->id,
        ])->save();

        return $pass;
    }

    public function isActive(): bool
    {
        return !$this->revoked_at && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function checkPin(string $pin): bool
    {
        return !$this->pin_hash || Hash::check($pin, $this->pin_hash);
    }

    public function url(): string
    {
        return route('gate.show', $this->token);
    }

    public function targetName(): string
    {
        return $this->event?->name ?? $this->experience?->name ?? '—';
    }
}
