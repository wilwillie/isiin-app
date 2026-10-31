<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $packages = Package::visible()
            ->with(['vendor', 'items'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->input('q') . '%'))
            ->when($request->filled('room_type'), fn ($q) => $q->where('room_type', $request->input('room_type')))
            ->when($request->filled('style'), fn ($q) => $q->where('style', $request->input('style')))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('packages.index', compact('packages'));
    }

    public function show(Package $package)
    {
        abort_unless($package->is_active && $package->vendor->is_verified, 404);

        $package->load(['vendor', 'items', 'reviews.customer']);

        return view('packages.show', [
            'package'   => $package,
            'avgRating' => $package->reviews->avg('rating'),
        ]);
    }
}
