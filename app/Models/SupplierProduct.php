<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_sale_price' => 'decimal:3',
        'avg_sale_price' => 'decimal:3',
        'max_sale_price' => 'decimal:3',
        'carbon_footprint' => 'decimal:3',
        'minimum_order_qty' => 'integer',
    ];

    protected $appends = ['price_range_formatted', 'carbon_footprint_formatted', 'eco_rating'];

    // Carbon footprint ratings
    const ECO_RATING_EXCELLENT = 'excellent';   // 0-1 kg CO2
    const ECO_RATING_GOOD = 'good';             // 1-3 kg CO2
    const ECO_RATING_AVERAGE = 'average';       // 3-5 kg CO2
    const ECO_RATING_POOR = 'poor';             // 5-10 kg CO2
    const ECO_RATING_VERY_POOR = 'very_poor';   // >10 kg CO2

    // Relations
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeByPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('avg_sale_price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('avg_sale_price', '<=', $maxPrice);
        }
        return $query;
    }

    public function scopeByCarbonFootprint($query, $maxFootprint = null)
    {
        if ($maxFootprint !== null) {
            $query->where('carbon_footprint', '<=', $maxFootprint);
        }
        return $query;
    }

    public function scopeEcoFriendly($query)
    {
        return $query->where('carbon_footprint', '<=', 3.0); // Good or better
    }

    public function scopeByMinimumOrder($query, $maxMinimum = null)
    {
        if ($maxMinimum !== null) {
            $query->where('minimum_order_qty', '<=', $maxMinimum);
        }
        return $query;
    }

    public function scopeInternational($query)
    {
        return $query->whereHas('supplier', function ($q) {
            $q->where('is_international', true);
        });
    }

    public function scopeLocal($query)
    {
        return $query->whereHas('supplier', function ($q) {
            $q->where('is_international', false);
        });
    }

    // Accessors
    public function getPriceRangeFormattedAttribute()
    {
        return sprintf(
            '%s - %s DZD (avg: %s DZD)',
            number_format($this->min_sale_price, 3),
            number_format($this->max_sale_price, 3),
            number_format($this->avg_sale_price, 3)
        );
    }

    public function getCarbonFootprintFormattedAttribute()
    {
        return number_format($this->carbon_footprint, 3) . ' kg CO2';
    }

    public function getEcoRatingAttribute()
    {
        return match (true) {
            $this->carbon_footprint <= 1 => self::ECO_RATING_EXCELLENT,
            $this->carbon_footprint <= 3 => self::ECO_RATING_GOOD,
            $this->carbon_footprint <= 5 => self::ECO_RATING_AVERAGE,
            $this->carbon_footprint <= 10 => self::ECO_RATING_POOR,
            default => self::ECO_RATING_VERY_POOR,
        };
    }

    // Helper methods
    public function getPriceForQuantity(int $quantity): float
    {
        // For bulk orders, use minimum price, otherwise average
        if ($quantity >= $this->minimum_order_qty * 10) {
            return $this->min_sale_price;
        } elseif ($quantity >= $this->minimum_order_qty * 5) {
            return ($this->min_sale_price + $this->avg_sale_price) / 2;
        } else {
            return $this->avg_sale_price;
        }
    }

    public function getTotalCostForQuantity(int $quantity): float
    {
        return $this->getPriceForQuantity($quantity) * $quantity;
    }

    public function getTotalCarbonFootprintForQuantity(int $quantity): float
    {
        return $this->carbon_footprint * $quantity;
    }

    public function canOrderQuantity(int $quantity): bool
    {
        return $quantity >= $this->minimum_order_qty;
    }

    public function isEcoFriendly(): bool
    {
        return $this->carbon_footprint <= 3.0;
    }

    public function getEcoRatingColor(): string
    {
        return match ($this->eco_rating) {
            self::ECO_RATING_EXCELLENT => 'success',
            self::ECO_RATING_GOOD => 'info',
            self::ECO_RATING_AVERAGE => 'warning',
            self::ECO_RATING_POOR => 'danger',
            self::ECO_RATING_VERY_POOR => 'dark',
            default => 'secondary',
        };
    }

    public function getEcoRatingText(): string
    {
        return match ($this->eco_rating) {
            self::ECO_RATING_EXCELLENT => 'Excellent',
            self::ECO_RATING_GOOD => 'Good',
            self::ECO_RATING_AVERAGE => 'Average',
            self::ECO_RATING_POOR => 'Poor',
            self::ECO_RATING_VERY_POOR => 'Very Poor',
            default => 'Unknown',
        };
    }

    public function getShippingInfo(): array
    {
        if (!$this->supplier) {
            return [];
        }

        $shippingCosts = $this->supplier->getShippingCosts();
        
        return array_merge($shippingCosts, [
            'total_product_cost' => $this->avg_sale_price,
            'research_cost' => $this->supplier->research_cost,
        ]);
    }

    public function compareWith(SupplierProduct $other): array
    {
        return [
            'price_difference' => $this->avg_sale_price - $other->avg_sale_price,
            'carbon_difference' => $this->carbon_footprint - $other->carbon_footprint,
            'min_order_difference' => $this->minimum_order_qty - $other->minimum_order_qty,
            'supplier_type_same' => $this->supplier->is_international === $other->supplier->is_international,
        ];
    }
}
