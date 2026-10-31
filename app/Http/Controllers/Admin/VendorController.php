<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('user')->withCount('packages')->latest()->get();

        return view('admin.vendors', compact('vendors'));
    }

    public function toggle(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['is_verified' => ! $vendor->is_verified]);

        return back()->with('success', $vendor->is_verified ? 'Vendor diverifikasi.' : 'Verifikasi vendor dicabut.');
    }
}
