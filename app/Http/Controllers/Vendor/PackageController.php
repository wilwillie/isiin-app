<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index()
    {
        $packages = $this->vendor()->packages()->withCount('items')->latest()->get();

        return view('vendor.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('vendor.packages.form', [
            'package'   => new Package(['duration_days' => 7, 'is_active' => true]),
            'itemsText' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $payload = $this->payload($request, $data);
        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('packages', 'public');
        }

        $package = $this->vendor()->packages()->create($payload);
        $this->syncItems($package, $data['items'] ?? null);

        return redirect()->route('vendor.packages.index')->with('success', 'Paket ditambahkan.');
    }

    public function edit(Package $package)
    {
        $this->ensureOwner($package);

        $itemsText = $package->items
            ->map(fn ($item) => $item->name . ' | ' . $item->quantity)
            ->implode("\n");

        return view('vendor.packages.form', compact('package', 'itemsText'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $this->ensureOwner($package);
        $data = $request->validate($this->rules());

        $payload = $this->payload($request, $data);
        if ($request->hasFile('image')) {
            if ($package->image_path) {
                Storage::disk('public')->delete($package->image_path);
            }
            $payload['image_path'] = $request->file('image')->store('packages', 'public');
        }

        $package->update($payload);
        $this->syncItems($package, $data['items'] ?? null);

        return redirect()->route('vendor.packages.index')->with('success', 'Paket diperbarui.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $this->ensureOwner($package);

        // Paket yang sudah punya pesanan tidak dihapus, hanya dinonaktifkan.
        if ($package->orders()->exists()) {
            $package->update(['is_active' => false]);

            return back()->with('success', 'Paket sudah punya pesanan, jadi dinonaktifkan (tidak dihapus).');
        }

        if ($package->image_path) {
            Storage::disk('public')->delete($package->image_path);
        }
        $package->delete();

        return back()->with('success', 'Paket dihapus.');
    }

    private function vendor(): Vendor
    {
        $vendor = Auth::user()->vendor;
        abort_unless($vendor, 403);

        return $vendor;
    }

    private function ensureOwner(Package $package): void
    {
        abort_unless($package->vendor_id === $this->vendor()->id, 403);
    }

    private function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:120'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'room_type'        => ['required', Rule::in(array_keys(config('isiin.room_types')))],
            'style'            => ['required', Rule::in(config('isiin.styles'))],
            'price'            => ['required', 'integer', 'min:0'],
            'duration_days'    => ['required', 'integer', 'between:1,365'],
            'delivery_fee'     => ['nullable', 'integer', 'min:0'],
            'installation_fee' => ['nullable', 'integer', 'min:0'],
            'deposit'          => ['nullable', 'integer', 'min:0'],
            'image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'items'            => ['nullable', 'string', 'max:3000'],
        ];
    }

    private function payload(Request $request, array $data): array
    {
        $payload = Arr::only($data, ['name', 'description', 'room_type', 'style', 'price', 'duration_days']);
        $payload['delivery_fee'] = (int) ($data['delivery_fee'] ?? 0);
        $payload['installation_fee'] = (int) ($data['installation_fee'] ?? 0);
        $payload['deposit'] = (int) ($data['deposit'] ?? 0);
        $payload['is_active'] = $request->boolean('is_active');

        return $payload;
    }

    /** Daftar furniture: satu baris per item, format "Nama | jumlah". */
    private function syncItems(Package $package, ?string $raw): void
    {
        $package->items()->delete();

        foreach (preg_split('/\r\n|\r|\n/', (string) $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            [$name, $qty] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '1');

            $package->items()->create([
                'name'     => Str::limit($name, 120, ''),
                'quantity' => max(1, (int) $qty),
            ]);
        }
    }
}
