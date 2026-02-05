<?php

namespace App\Services;

use App\Models\ChannelSetting;
use App\Models\ProductBatch;

class PricingService
{
    /**
     * Calculate suggested selling price for a specific batch and channel.
     * 
     * Formula:
     * 1. Cost = Buy Price + Fixed Operational Cost
     * 2. Price after Margin = Cost + Margin (Nominal or % of Cost)
     * 3. Final Price = (Price after Margin + Shipping Subsidy) / (1 - Fee %) (if fee is %)
     * OR Final Price = Price after Margin + Shipping Subsidy + Fee (if fee is Fixed)
     */
    public static function calculate(ProductBatch $batch, $channelSlug)
    {
        $setting = ChannelSetting::where('slug', $channelSlug)->first();
        if (!$setting) return 0;

        return self::calculateFinalPrice($batch->buy_price, $setting);
    }

    /**
     * Calculate suggested price for a product based on its newest batch HPP.
     */
    public static function calculateForProduct(\App\Models\Product $product, $channelSlug)
    {
        $latestBatch = $product->batches()->orderBy('id', 'desc')->first();
        if (!$latestBatch) return 0;

        $setting = ChannelSetting::where('slug', $channelSlug)->first();
        if (!$setting) return 0;

        return self::calculateFinalPrice($latestBatch->buy_price, $setting);
    }

    /**
     * Shared calculation logic
     */
    private static function calculateFinalPrice($hpp, $setting)
    {
        // 1. Base Cost with Margin
        $baseWithMargin = $hpp;
        if ($setting->margin_type === 'percentage') {
            $baseWithMargin += ($hpp * ($setting->margin_value / 100));
        } else {
            $baseWithMargin += $setting->margin_value;
        }

        // 2. Add Fixed Operational Cost
        $price = $baseWithMargin + $setting->fixed_cost + $setting->shipping_subsidy;

        // 3. Apply Fee/Commission
        if ($setting->fee_type === 'percentage') {
            if ($setting->fee_value < 100) {
                $price = $price / (1 - ($setting->fee_value / 100));
            }
        } else {
            $price += $setting->fee_value;
        }

        return round($price);
    }
}
