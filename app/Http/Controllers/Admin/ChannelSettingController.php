<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChannelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChannelSettingController extends Controller
{
    public function index()
    {
        $channels = ChannelSetting::all();
        return view('admin.settings.channels', compact('channels'))->with('sb', 'ChannelSettings');
    }

    public function update(Request $request)
    {
        $channel = ChannelSetting::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'margin_type' => 'required|in:fixed,percentage',
            'margin_value' => 'required|numeric|min:0',
            'fee_type' => 'required|in:fixed,percentage',
            'fee_value' => 'required|numeric|min:0',
            'fixed_cost' => 'required|numeric|min:0',
            'shipping_subsidy' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $channel->update($request->all());

        return redirect()->back()->with('message', "Pengaturan {$channel->name} berhasil diperbarui");
    }
}
