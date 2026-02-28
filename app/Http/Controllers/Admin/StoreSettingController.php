<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class StoreSettingController extends Controller
{
    public function index()
    {
        $setting = StoreSetting::getActiveSetting();
        return view('admin.settings.store', compact('setting'))->with('sb', 'Settings');
    }

    public function update(Request $request)
    {
        $setting = StoreSetting::getActiveSetting();

        $request->validate([
            'store_name' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'shopee_url' => 'nullable|url|max:255',
            'tokopedia_url' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string',
        ]);

        $data = $request->except(['logo', '_token']);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo-' . time() . '.' . $file->getClientOriginalExtension();

            $path = public_path('assets/img/store');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $file->move($path, $filename);

            // Delete old logo if exists and not default
            if ($setting->logo_path) {
                $oldPath = public_path($setting->logo_path);
                if (File::exists($oldPath) && strpos($setting->logo_path, 'logo-black.png') === false) {
                    File::delete($oldPath);
                }
            }

            $data['logo_path'] = 'assets/img/store/' . $filename;
        }

        $setting->update($data);

        return redirect()->back()->with('message', 'Pengaturan toko berhasil diperbarui.');
    }
}
