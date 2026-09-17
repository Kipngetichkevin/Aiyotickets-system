<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System;
use App\Helpers\SystemHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $system    = System::settings();
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $currencies = $this->currencies();

        return view('admin.system.index', compact('system', 'timezones', 'currencies'));
    }

    /**
     * Save the settings.
     */
    public function update(Request $request)
    {
        $system = System::settings();

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slogan'           => 'nullable|string|max:255',
            'timezone'         => 'required|string',
            'date_format'      => 'required|string|max:20',
            'time_format'      => 'required|string|max:20',
            'currency'         => 'required|string|max:10',
            'currency_symbol'  => 'required|string|max:10',
            'primary_color'    => 'required|string|max:7',
            'secondary_color'  => 'required|string|max:7',
            'contact_email'    => 'nullable|email|max:255',
            'contact_phone'    => 'nullable|string|max:50',
            'address'          => 'nullable|string',
            'pagination_limit' => 'required|integer|min:5|max:100',
            'custom_css'       => 'nullable|string',
            'custom_js'        => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords'    => 'nullable|string',
            'logo'             => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            'logo_dark'        => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            'logo_icon'        => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:1024',
            'favicon'          => 'nullable|mimes:jpg,jpeg,png,gif,svg,ico,webp|max:1024',
        ]);

        // Strip files from the flat data
        $data = collect($validated)->except(['logo', 'logo_dark', 'logo_icon', 'favicon'])->toArray();

        // Handle image uploads — store each with a stable filename per field
        $imageFields = [
            'logo'      => 'images/logo',
            'logo_dark' => 'images/logo',
            'logo_icon' => 'images/logo',
            'favicon'   => 'images',
        ];

        foreach ($imageFields as $field => $folder) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $file      = $request->file($field);
            $extension = $file->getClientOriginalExtension();
            $filename  = $field . '.' . $extension;

            // Delete old file
            if ($system->$field && Storage::disk('public')->exists($system->$field)) {
                Storage::disk('public')->delete($system->$field);
            }

            // Ensure directory exists
            Storage::disk('public')->makeDirectory($folder);

            $data[$field] = $file->storeAs($folder, $filename, 'public');
        }

        $system->update($data);

        SystemHelper::clearCache();
        Cache::forget('system_settings');

        return back()->with('success', 'System settings updated successfully.');
    }

    /**
     * Clear all caches (system settings + Laravel cache).
     */
    public function clearCache()
    {
        SystemHelper::clearCache();
        Cache::forget('system_settings');

        return back()->with('success', 'Cache cleared successfully.');
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance()
    {
        $system = System::settings();
        $system->update([
            'maintenance_mode' => !$system->maintenance_mode,
        ]);

        SystemHelper::clearCache();
        Cache::forget('system_settings');

        return back()->with('success', $system->maintenance_mode
            ? 'Maintenance mode enabled.'
            : 'Maintenance mode disabled.');
    }

    /**
     * Curated currency list.
     */
    private function currencies(): array
    {
        return [
            'KES' => 'Kenyan Shilling (KSh)',
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'TZS' => 'Tanzanian Shilling (TSh)',
            'UGX' => 'Ugandan Shilling (USh)',
            'ZAR' => 'South African Rand (R)',
            'NGN' => 'Nigerian Naira (₦)',
            'USDT' => 'USDT (Crypto)',
        ];
    }
}