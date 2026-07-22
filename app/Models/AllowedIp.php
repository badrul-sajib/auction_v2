<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\IpUtils;

class AllowedIp extends Model
{
    protected $fillable = ['value', 'label', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Whether the given IP is allowed to access customer order links.
     * An empty (or all-inactive) whitelist means no restriction.
     */
    public const FEATURE_KEY = 'ip_whitelist_enabled';

    public static function featureEnabled(): bool
    {
        return Setting::bool(self::FEATURE_KEY, true);
    }

    public static function allows(?string $ip): bool
    {
        // Master switch off = restriction disabled entirely.
        if (! static::featureEnabled()) {
            return true;
        }

        if ($ip === null) {
            return true;
        }

        $ranges = static::active()->pluck('value')->all();

        if (empty($ranges)) {
            return true; // empty list = open to everyone
        }

        return IpUtils::checkIp($ip, $ranges);
    }
}
