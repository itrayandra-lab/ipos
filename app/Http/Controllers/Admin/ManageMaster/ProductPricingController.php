<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use App\Models\ProductTier;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Services\PricingService;
use Illuminate\Http\Request;
use DataTables;

class ProductPricingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user && $user->role === 'sales') {
                abort(403, 'Akses ditolak. Role Sales tidak diizinkan mengakses halaman Harga Produk.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $stats = $this->getStats();
        $tiers = \App\Models\ProductTier::all();
        return view('admin.manage_master.products.pricing')->with([
            'sb'    => 'ProductPricing',
            'stats' => $stats,
            'tiers' => $tiers,
        ]);
    }

    private function getStats(): array
    {
        $total     = ProductVariant::count();
        $approved  = ProductVariant::where('is_approved', true)->count();
        $pending   = ProductVariant::where('is_approved', false)->where('hpp_rayandra', '>', 0)->count();
        $belumHitung = ProductVariant::where('hpp_rayandra', 0)->count();

        return compact('total', 'approved', 'pending', 'belumHitung');
    }

    public function getall(Request $request)
    {
        $tiers = ProductTier::all();
        $variants = ProductVariant::with(['netto.product.merek', 'netto.product.productTier'])
            ->select('product_variants.*');

        // Apply filter from pills
        $filter = $request->input('filter', 'all');
        if ($filter === 'belum') {
            // Belum Hitung: hpp_rayandra = 0 (belum pernah dihitung)
            $variants->where('hpp_rayandra', 0)->orWhere('hpp_rayandra', null);
        } elseif ($filter === 'pending') {
            // Pending: sudah dihitung tapi belum di-approve
            $variants->where('hpp_rayandra', '>', 0)->where('is_approved', false);
        } elseif ($filter === 'approved') {
            $variants->where('is_approved', true);
        }
        // 'all' = no filter

        return DataTables::of($variants)
            ->addIndexColumn()
            ->filterColumn('product_info', function($query, $keyword) {
                $query->whereHas('netto.product', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhereHas('merek', function($qm) use ($keyword) {
                          $qm->where('name', 'like', "%{$keyword}%");
                      });
                });
            })
            ->orderColumn('product_info', function($query, $order) {
                $query->join('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
                      ->join('products', 'product_nettos.product_id', '=', 'products.id')
                      ->orderBy('products.name', $order);
            })
            ->addColumn('product_info', function ($v) {
                $merek  = $v->netto->product->merek->name ?? '';
                $name   = $v->netto->product->name ?? '';
                $netto  = $v->netto->netto_value . ' ' . $v->netto->satuan;
                return "
                    <div>
                        <div class='font-weight-bold text-dark' style='line-height:1.4'>{$merek} {$name} {$netto}</div>
                    </div>
                ";
            })
            ->addColumn('tier_col', function ($v) {
                return $v->productTier->name ?? ($v->netto->product->productTier->name ?? '<span class="text-muted">-</span>');
            })
            ->addColumn('tax_status_col', function ($v) {
                return $v->tax_status ? '<span class="badge badge-info">PPN</span>' : '<span class="badge badge-secondary">Non PPN</span>';
            })
            ->addColumn('hpp_beli_col', function ($v) {
                return 'Rp ' . number_format($v->product_hpp, 0, ',', '.');
            })
            ->addColumn('margin_hpp_col', function ($v) {
                if ($v->hpp_rayandra <= 0) {
                    return "<span class='badge badge-light text-muted px-3 py-2'><i class='fas fa-calculator mr-1'></i>Belum dihitung</span>";
                }
                $hppRay  = number_format($v->hpp_rayandra, 0, ',', '.');
                $margin  = number_format($v->margin_hpp, 0, ',', '.');
                $pct     = $v->product_hpp > 0
                    ? round(($v->margin_hpp / $v->product_hpp) * 100, 1)
                    : 0;
                $barColor = $pct >= 30 ? '#10b981' : ($pct >= 15 ? '#f59e0b' : '#ef4444');
                return "
                    <div class='small'>
                        <div class='d-flex justify-content-between mb-1'>
                            <span class='text-muted'>HPP Ray:</span>
                            <span class='font-weight-bold text-info'>{$hppRay}</span>
                        </div>
                        <div class='d-flex justify-content-between mb-1'>
                            <span class='text-muted'>Margin:</span>
                            <span class='font-weight-bold text-success'>{$margin}</span>
                        </div>
                        <div class='progress mt-1' style='height:4px;border-radius:4px;'>
                            <div class='progress-bar' style='width:{$pct}%;background:{$barColor};' title='Margin {$pct}%'></div>
                        </div>
                        <small class='text-muted'>{$pct}% margin</small>
                    </div>
                ";
            })
            ->addColumn('ray_store_col', function ($v) {
                return 'Rp ' . number_format($v->ray_store, 0, ',', '.');
            })
            ->addColumn('het_product_col', function ($v) {
                if ($v->het_online <= 0 && !$v->is_approved) {
                    return "<span class='text-muted small'>-</span>";
                }
                
                $settings  = StoreSetting::getActiveSetting();
                $feeOnline = ($settings->fee_online_percent ?? 4) / 100;
                $tax       = $v->tax_status ? (($settings->tax_percent ?? 11) / 100) : 0;
                $rawHet    = 0;
                if ((1 - $feeOnline) > 0) {
                    $rawHet = ($v->ray_store / (1 - $feeOnline)) * (1 + $tax);
                }

                $rawHetStr = number_format($rawHet, 0, ',', '.');
                $hetFinal  = number_format($v->het_online, 0, ',', '.');
                $status = $v->is_approved
                    ? "<span class='badge badge-success px-2 py-1 mt-1'><i class='fas fa-check-circle mr-1'></i>Approved</span>"
                    : "<span class='badge badge-warning px-2 py-1 mt-1'><i class='fas fa-clock mr-1'></i>Pending</span>";
                    
                return "
                    <div class='text-center'>
                        <div class='small text-muted mb-1' title='HET Rumus'>HET: {$rawHetStr}</div>
                        <strong class='d-block text-primary' title='HET Final (Rounded)'>{$hetFinal}</strong>
                        {$status}
                    </div>
                ";
            })
            ->addColumn('action', function ($v) {
                $merek  = $v->netto->product->merek->name ?? '';
                $name   = $v->netto->product->name ?? '';
                $netto  = $v->netto->netto_value . ' ' . $v->netto->satuan;
                $fullName = "{$merek} {$name} {$netto}";
                
                $currentTierId = $v->product_tier_id ?? ($v->netto->product->product_tier_id ?? '');

                $processBtn = "
                    <button class='btn btn-sm btn-primary btn-process mb-1 w-100' 
                            data-id='{$v->id}' 
                            data-name='{$fullName}'
                            data-tier='{$currentTierId}'
                            data-tax='{$v->tax_status}'
                            data-hpp='{$v->product_hpp}'
                            data-ray='{$v->ray_store}'
                            data-approved='{$v->is_approved}'>
                        <i class='fas fa-edit mr-1'></i>Proses
                    </button>
                ";
                
                $approveBtn = "";
                if ($v->hpp_rayandra > 0) {
                    $approveClass = $v->is_approved ? 'btn-danger' : 'btn-success';
                    $approveText  = $v->is_approved ? 'Unapprove' : 'Approve';
                    $approveIcon  = $v->is_approved ? 'fa-times' : 'fa-check';
                    $approveBtn   = "
                        <button class='btn btn-sm {$approveClass} btn-approve w-100' 
                                data-id='{$v->id}' data-status='" . ($v->is_approved ? 0 : 1) . "'>
                            <i class='fas {$approveIcon} mr-1'></i>{$approveText}
                        </button>
                    ";
                }
                
                return "<div style='min-width:100px'>{$processBtn}{$approveBtn}</div>";
            })
            ->rawColumns(['product_info', 'tier_col', 'tax_status_col', 'margin_hpp_col', 'het_product_col', 'action'])
            ->make(true);
    }

    /** Save all pricing data from modal and recalculate */
    public function savePricing(Request $request)
    {
        $v = ProductVariant::findOrFail($request->id);
        
        $v->product_tier_id = $request->tier_id;
        $v->tax_status      = $request->tax_status;
        $v->product_hpp     = $request->product_hpp;
        $v->ray_store       = $request->ray_store;
        $v->save();
        
        // Recalculate
        $v->recalculatePricing();
        
        return response()->json(['message' => 'Data pricing berhasil disimpan']);
    }

    /** Update HPP Modal (product_hpp) dan langsung recalculate */
    public function updateHpp(Request $request)
    {
        $request->validate([
            'id'    => 'required|exists:product_variants,id',
            'value' => 'required',
        ]);

        $variant = ProductVariant::findOrFail($request->id);
        $value   = (int) str_replace(['.', ','], '', $request->value);

        $variant->product_hpp = $value;
        $variant->price_real  = $value; // Keep price_real in sync
        $variant->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'HPP Modal berhasil diperbarui. Klik "Hitung" untuk kalkulasi ulang harga.',
        ]);
    }

    /** Update Tier for variant and recalculate */
    public function updateTier(Request $request)
    {
        $request->validate([
            'id'    => 'required|exists:product_variants,id',
            'value' => 'nullable|exists:product_tiers,id',
        ]);

        $variant = ProductVariant::findOrFail($request->id);
        $variant->product_tier_id = $request->value;
        $variant->save();

        if ($variant->product_hpp > 0) {
            $pricing = PricingService::calculateRayandraPricing(
                $variant->product_hpp,
                $variant->product_tier_id,
                $variant->ray_store > 0 ? $variant->ray_store : null
            );
            $variant->hpp_rayandra = $pricing['hpp_rayandra'];
            $variant->margin_hpp   = $pricing['margin_hpp'];
            if ($variant->ray_store <= 0) {
                $variant->ray_store = $pricing['ray_store'];
            }
            $settings   = StoreSetting::getActiveSetting();
            $feeOnline  = ($settings->fee_online_percent ?? 4) / 100;
            $tax        = $variant->tax_status ? (($settings->tax_percent ?? 11) / 100) : 0;
            if ((1 - $feeOnline) > 0) {
                $rawHet = ($variant->ray_store / (1 - $feeOnline)) * (1 + $tax);
                $variant->het_online = ceil($rawHet / 1000) * 1000;
            }
            $variant->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Tier berhasil diperbarui dan harga dikalkulasi ulang.',
        ]);
    }

    /** Update Tax Status */
    public function updateTaxStatus(Request $request)
    {
        $request->validate([
            'id'    => 'required|exists:product_variants,id',
            'value' => 'required|boolean',
        ]);

        $variant = ProductVariant::findOrFail($request->id);
        $variant->tax_status = $request->value;
        $variant->save();

        if ($variant->ray_store > 0) {
            $settings   = StoreSetting::getActiveSetting();
            $feeOnline  = ($settings->fee_online_percent ?? 4) / 100;
            $tax        = $variant->tax_status ? (($settings->tax_percent ?? 11) / 100) : 0;
            if ((1 - $feeOnline) > 0) {
                $rawHet = ($variant->ray_store / (1 - $feeOnline)) * (1 + $tax);
                $variant->het_online = ceil($rawHet / 1000) * 1000;
            }
            $variant->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Status Pajak berhasil diperbarui. HET Online dikalkulasi ulang.',
        ]);
    }

    /** Recalculate pricing for a single variant */
    public function recalculate(Request $request)
    {
        $request->validate(['id' => 'required|exists:product_variants,id']);

        $variant = ProductVariant::with('netto.product')->findOrFail($request->id);
        $tierId  = $variant->product_tier_id ?? ($variant->netto->product->product_tier_id ?? null);

        if ($variant->product_hpp <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'HPP Modal belum diisi. Masukkan HPP terlebih dahulu.',
            ], 422);
        }

        $pricing = PricingService::calculateRayandraPricing(
            $variant->product_hpp,
            $tierId,
            $variant->ray_store > 0 ? $variant->ray_store : null
        );

        $variant->hpp_rayandra = $pricing['hpp_rayandra'];
        $variant->margin_hpp   = $pricing['margin_hpp'];
        // Only set ray_store if it hasn't been manually set
        if ($variant->ray_store <= 0) {
            $variant->ray_store = $pricing['ray_store'];
        }
        // Recalculate het_online based on current ray_store
        $settings   = StoreSetting::getActiveSetting();
        $feeOnline  = ($settings->fee_online_percent ?? 4) / 100;
        $tax        = $variant->tax_status ? (($settings->tax_percent ?? 11) / 100) : 0;
        if ((1 - $feeOnline) > 0) {
            $rawHet = ($variant->ray_store / (1 - $feeOnline)) * (1 + $tax);
            $variant->het_online = ceil($rawHet / 1000) * 1000;
        }
        $variant->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kalkulasi harga berhasil diperbarui.',
            'data'    => [
                'hpp_rayandra' => $variant->hpp_rayandra,
                'margin_hpp'   => $variant->margin_hpp,
                'ray_store'    => $variant->ray_store,
                'het_online'   => $variant->het_online,
            ],
        ]);
    }

    /** Recalculate ALL variants in bulk */
    public function recalculateAll(Request $request)
    {
        $variants = ProductVariant::with('netto.product')
            ->where('product_hpp', '>', 0)
            ->get();

        $settings  = StoreSetting::getActiveSetting();
        $feeOnline = ($settings->fee_online_percent ?? 4) / 100;
        $tax       = ($settings->tax_percent ?? 11) / 100;

        $count = 0;
        foreach ($variants as $variant) {
            $tierId  = $variant->product_tier_id ?? ($variant->netto->product->product_tier_id ?? null);
            $pricing = PricingService::calculateRayandraPricing(
                $variant->product_hpp,
                $tierId,
                $variant->ray_store > 0 ? $variant->ray_store : null
            );

            $variant->hpp_rayandra = $pricing['hpp_rayandra'];
            $variant->margin_hpp   = $pricing['margin_hpp'];
            if ($variant->ray_store <= 0) {
                $variant->ray_store = $pricing['ray_store'];
            }
            if ((1 - $feeOnline) > 0) {
                $tax = $variant->tax_status ? (($settings->tax_percent ?? 11) / 100) : 0;
                $rawHet = ($variant->ray_store / (1 - $feeOnline)) * (1 + $tax);
                $variant->het_online = ceil($rawHet / 1000) * 1000;
            }
            $variant->save();
            $count++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => "{$count} varian berhasil dihitung ulang.",
        ]);
    }

    /** Update Ray Store price */
    public function updateRayStore(Request $request)
    {
        $variant = ProductVariant::findOrFail($request->id);
        $value   = (int) str_replace(['.', ','], '', $request->value);
        $variant->ray_store = $value;

        $settings  = StoreSetting::getActiveSetting();
        $feeOnline = ($settings->fee_online_percent ?? 4) / 100;
        $tax       = $variant->tax_status ? (($settings->tax_percent ?? 11) / 100) : 0;

        if ((1 - $feeOnline) > 0) {
            $rawHet = ($variant->ray_store / (1 - $feeOnline)) * (1 + $tax);
            $variant->het_online = ceil($rawHet / 1000) * 1000;
        }

        $variant->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Ray Store berhasil diperbarui. HET Online dihitung ulang.',
        ]);
    }

    /** Approve / Unapprove */
    public function approve(Request $request)
    {
        $variant             = ProductVariant::findOrFail($request->id);
        $variant->is_approved = (bool) $request->status;

        if ($variant->is_approved && $variant->het_online > 0) {
            // NOTE: price is NOT updated here during development phase.
            // It will only be updated when fully launched.
        } else {
            $variant->het_online = 0;
        }

        $variant->save();

        $msg = $variant->is_approved
            ? 'Harga telah di-Approve.'
            : 'Approval dibatalkan.';

        return response()->json(['status' => 'success', 'message' => $msg]);
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }
}
