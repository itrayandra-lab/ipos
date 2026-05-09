<?php

namespace App\Services;

use App\Models\ChannelSetting;
use App\Models\ProductBatch;

class PricingService
{
    /**
     * Calculate suggested selling price for a specific batch and channel.
     */
    public static function calculate(ProductBatch $batch, $channelSlug)
    {
        $setting = ChannelSetting::where('slug', $channelSlug)->first();
        if (!$setting) return 0;

        // Current requirement: Use price from variant for POS/Invoice
        if ($batch->variant && $batch->variant->price > 0) {
            return $batch->variant->price;
        }

        return self::calculateFinalPrice($batch->buy_price, $setting);
    }

    /**
     * Calculate all Rayandra pricing components for a product variant.
     * 
     * Formula:
     * - HPP RAYANDRA = hpp beli * Product Tier Multiplier
     * - Margin HPP = HPP RAYANDRA - hpp beli
     * - RAY STORE = HPP RAYANDRA (if not adjusted)
     * - HET Online = RAY STORE / (1 - Fee Online%) * (1 + PPN%)
     */
    public static function calculateRayandraPricing($hppBeli, $tierId, $adjustedRayStore = null)
    {
        $tier = \App\Models\ProductTier::find($tierId);
        $multiplier = $tier ? $tier->multiplier : 1;
        
        $hppRayandra = $hppBeli * $multiplier;
        $marginHpp = $hppRayandra - $hppBeli;
        
        $rayStore = ($adjustedRayStore !== null) ? $adjustedRayStore : $hppRayandra;
        
        $storeSetting = \App\Models\StoreSetting::getActiveSetting();
        $feeOnline = ($storeSetting->fee_online_percent ?? 4) / 100;
        $tax = ($storeSetting->tax_percent ?? 11) / 100;
        
        $hetOnline = 0;
        if ((1 - $feeOnline) > 0) {
            $hetOnline = ($rayStore / (1 - $feeOnline)) * (1 + $tax);
        }

        return [
            'hpp_beli' => $hppBeli,
            'hpp_rayandra' => round($hppRayandra),
            'margin_hpp' => round($marginHpp),
            'ray_store' => round($rayStore),
            'het_online' => round($hetOnline),
        ];
    }

    private static function calculateFinalPrice($hpp, $setting)
    {
        return $setting->calculatePrice($hpp);
    }
}
