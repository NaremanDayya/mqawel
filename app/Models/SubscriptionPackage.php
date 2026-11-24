<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPackage extends Model
{
    use HasFactory;

    protected $fillable= [
        'picture',
        'title',
        'description',
        'period',
        'price',
        'currency',
        'has_workers',
        'has_projects',
        'has_storages',
        'has_items',
        'has_item_categories',
        'has_item_movements',
        'has_workers_report',
        'has_worker_expenses_report',
        'has_expired_files_report',
        'has_project_expenses_report',
        'max_projects',
        'max_workers',
        'traditional_cost_items',
        'savings_note',
        'feature_bullets',
        'cta_label',
        'is_active',
        'yearly_price',
        'title_en',
        'description_en',
        'savings_note_en',
        'cta_label_en',
        'is_trial',
        'trial_days',
        'master_id',
    ];

    protected $casts = [
        'traditional_cost_items' => 'array',
        'feature_bullets' => 'array',
        'is_trial' => 'boolean',
        'trial_days' => 'integer',
        'yearly_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // The trial is a free period BEFORE the normal subscription: it only needs a length, and
        // never changes the package's prices.
        static::saving(function (SubscriptionPackage $package) {
            if ($package->is_trial) {
                $package->trial_days = max(1, (int) ($package->trial_days ?: 14));
            }
        });
    }

    /**
     * A text the admin typed on the package, in the language the website is being read in. In English the
     * Arabic title and description are the fallback (there is nothing better to show), while the button and
     * the savings note fall back to nothing so the page's own default wording is used instead of Arabic.
     *
     * @param  'title'|'description'|'savings_note'|'cta_label'  $field
     */
    public function localized(string $field): ?string
    {
        $arabic = $this->getAttribute($field);

        if (app()->getLocale() !== 'en') {
            return $arabic;
        }

        $english = $this->getAttribute($field.'_en');

        if (filled($english)) {
            return $english;
        }

        return in_array($field, ['title', 'description'], true) ? $arabic : null;
    }

    /**
     * The text of one cost item or feature bullet ($field is 'label' or 'text') in the current language,
     * using its English version when the website is in English and one was entered.
     *
     * @param  array<string, mixed>  $item
     */
    public static function localizedItem(array $item, string $field): ?string
    {
        $arabic = $item[$field] ?? null;

        if (app()->getLocale() === 'en' && filled($item[$field.'_en'] ?? null)) {
            return $item[$field.'_en'];
        }

        return $arabic;
    }

    /**
     * What one month costs on this package.
     */
    public function monthlyRate(): float
    {
        return $this->period > 0 ? (float) $this->price / $this->period : (float) $this->price;
    }

    /**
     * What a 12-month subscription costs: the admin's yearly price when one is set, otherwise
     * 10 months' worth (two months free).
     */
    public function yearlyPrice(): float
    {
        $yearly = $this->getAttribute('yearly_price');

        return filled($yearly) ? (float) $yearly : round($this->monthlyRate() * 10, 2);
    }

    /**
     * The price of a subscription of $months months: 12 months use the yearly price, any other
     * length is the monthly rate times the months.
     */
    public function priceForMonths(int $months): float
    {
        return $months === 12 ? $this->yearlyPrice() : round($this->monthlyRate() * $months, 3);
    }

    /**
     * The last day a paid subscription to this package covers when it starts on $start.
     */
    public function endingDateFrom(string $start): string
    {
        return date('Y-m-d', strtotime('+ '.$this->period.' month', strtotime($start)));
    }

    /**
     * The last day of the free trial when it starts on $start (the last day counts).
     */
    public function trialEndingDate(string $start): string
    {
        return date('Y-m-d', strtotime('+ '.(max(1, (int) $this->trial_days) - 1).' day', strtotime($start)));
    }

    public function subscriptions() : HasMany {
        return $this->hasMany(Subscription::class, 'package_id');
    }
}
