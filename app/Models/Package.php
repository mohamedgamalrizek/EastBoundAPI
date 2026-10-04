<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Package extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Package')->logOnly(['title', 'price', 'status'])->setDescriptionForEvent(fn (string $event) => $event);
    }

    protected $fillable = [
        'category_id',
        'title',
        'destination',
        'category',
        'price',
        'child_price',
        'single_supplement',
        'duration_days',
        'duration_nights',
        'description',
        'inclusions',
        'exclusions',
        'image',
        'status',
    ];

    /** "What's included" list — one item per line, blank lines ignored. */
    public function inclusionList(): array
    {
        return $this->linesOf($this->inclusions);
    }

    public function exclusionList(): array
    {
        return $this->linesOf($this->exclusions);
    }

    private function linesOf(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    // Named packageCategory() (not category()) because the legacy display
    // column `category` (string) would otherwise shadow the relation accessor.
    public function packageCategory()
    {
        return $this->belongsTo(\App\Models\PackageCategory::class, 'category_id');
    }

    /** Day-by-day plan shown on the public package page. */
    public function itineraries()
    {
        return $this->hasMany(PackageItinerary::class)->orderBy('day_number')->orderBy('id');
    }

    public function bookings()         { return $this->hasMany(Booking::class); }
    public function visaApplications() { return $this->hasMany(VisaApplication::class); }
    public function tourSchedules()    { return $this->hasMany(TourSchedule::class); }
    public function wishlists()        { return $this->hasMany(Wishlist::class); }
    public function reviews()          { return $this->hasMany(Review::class); }

    /** Only the reviews the public may see. */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->approved()->latest();
    }

    /**
     * Star rating from approved reviews only, so a package cannot be lifted
     * by reviews still awaiting moderation. Null — not zero — when nobody has
     * reviewed it, because "unrated" and "rated 0" are different claims and
     * the page renders them differently.
     */
    public function getAvgRatingAttribute(): ?float
    {
        $avg = $this->relationLoaded('approvedReviews')
            ? $this->approvedReviews->avg('rating')
            : $this->reviews()->approved()->avg('rating');

        return $avg === null ? null : round((float) $avg, 1);
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->relationLoaded('approvedReviews')
            ? $this->approvedReviews->count()
            : (int) $this->reviews()->approved()->count();
    }

    // Coloured badge for the status column (matches theme bullet-badge styling)
    public function statusBadge(): string
    {
        $class = $this->status === 'active' ? 'success' : 'danger';
        return "<span class='bullet-badge bullet-badge-{$class}'>" . ucfirst($this->status) . "</span>";
    }
}
