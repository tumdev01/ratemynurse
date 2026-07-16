<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class MemberContact extends Model
{
    use HasFactory;

    protected $table = 'member_contacts';

    protected $fillable = [
        'member_id',
        'provider_user_id',
        'provider_accepted',
        'provider_profile_id',
        'provider_type',
        'provider_role',
        'type',
        'description',
        'start_date',
        'end_date',
        'facebook',
        'lineid',
        'email',
        'phone',
    ];

    /* =======================
     | Relationships
     ======================= */

    // ผู้จอง
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    // เจ้าของ profile (provider user)
    public function providerUser()
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    // 🔥 provider profile (NursingProfile | NursingHomeProfile)
    public function providerProfile(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'provider_type',
            'provider_profile_id'
        );
    }

    // calendar events
    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /* =======================
     | Scopes
     ======================= */

    /**
     * Scope: เฉพาะ contact ที่ provider accepted แล้ว
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('provider_accepted', true);
    }

    /**
     * Scope: เฉพาะ provider type ที่กำหนด
     */
    public function scopeOfProviderType(Builder $query, string $providerType): Builder
    {
        return $query->where('provider_type', $providerType);
    }

    /* =======================
     | Helpers
     ======================= */

    /**
     * เช็คว่า user นี้เป็น provider เจ้าของ contact หรือไม่
     */
    public function isOwnedByProvider(User $user): bool
    {
        return $this->provider_user_id === $user->id;
    }

    /**
     * ดึง collection ของ provider_accepted contacts พร้อม provider profile
     * แยกตาม provider type (NursingProfile, NursingHomeProfile)
     *
     * @return Collection ['nursing' => Collection, 'nursing_home' => Collection]
     */
    public static function getAcceptedByProviderType(): Collection
    {
        $contacts = static::accepted()
            ->with(['providerProfile', 'member', 'providerUser'])
            ->get();

        return collect([
            'nursing'      => $contacts->filter(fn ($c) => $c->provider_type === NursingProfile::class),
            'nursing_home' => $contacts->filter(fn ($c) => $c->provider_type === NursingHomeProfile::class),
        ]);
    }

    /**
     * ดึง accepted contacts ของ member คนนี้ พร้อม provider profile
     *
     * @return Collection
     */
    public static function getAcceptedForMember(int $memberId): Collection
    {
        return static::accepted()
            ->where('member_id', $memberId)
            ->with(['providerProfile', 'providerUser'])
            ->get()
            ->groupBy('provider_type');
    }
}
