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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:channel_settings,name',
            'factors' => 'nullable|array',
            'factors.*.label' => 'required|string',
            'factors.*.operator' => 'required|in:multiply,percentage,add',
            'factors.*.value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ChannelSetting::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'factors' => $request->factors ? array_values($request->factors) : [],
        ]);

        return redirect()->back()->with('message', 'Channel baru berhasil ditambahkan');
    }

    public function update(Request $request)
    {
        $channel = ChannelSetting::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:channel_settings,name,' . $channel->id,
            'factors' => 'nullable|array',
            'factors.*.label' => 'required|string',
            'factors.*.operator' => 'required|in:multiply,percentage,add',
            'factors.*.value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $channel->update([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'factors' => $request->factors ? array_values($request->factors) : [],
        ]);

        return redirect()->back()->with('message', "Pengaturan {$channel->name} berhasil diperbarui");
    }

    public function delete(Request $request)
    {
        $channel = ChannelSetting::findOrFail($request->id);
        $channel->delete();

        return redirect()->back()->with('message', 'Channel berhasil dihapus');
    }
}
