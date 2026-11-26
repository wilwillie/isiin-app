<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PrototypeController extends Controller
{
    private const PACKAGES = [
        'japandi' => [
            'name' => 'Japandi Living',
            'room' => 'Ruang tamu',
            'style' => 'Japandi',
            'price' => 1850000,
            'duration' => '14 hari',
            'color' => '#d8c6ad',
            'items' => ['Sofa 2-seater', 'Meja kopi kayu', 'Karpet natural', 'Lampu sudut'],
        ],
        'minimalis' => [
            'name' => 'Minimalis Cerah',
            'room' => 'Ruang tamu',
            'style' => 'Modern minimalis',
            'price' => 1550000,
            'duration' => '14 hari',
            'color' => '#cbd7d1',
            'items' => ['Sofa compact', 'Meja sisi', 'Karpet tekstur', 'Dekorasi meja'],
        ],
        'kamar' => [
            'name' => 'Kamar Hangat',
            'room' => 'Kamar utama',
            'style' => 'Scandinavian',
            'price' => 1250000,
            'duration' => '14 hari',
            'color' => '#d5c7c1',
            'items' => ['Headboard kain', 'Meja samping', 'Lampu tidur', 'Seprai dekoratif'],
        ],
    ];

    private const SCREENS = ['home', 'catalog', 'detail', 'booking', 'payment', 'success', 'orders', 'profile'];

    public function __invoke(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'screen' => ['nullable', 'string', Rule::in(self::SCREENS)],
            'package' => ['nullable', 'string', Rule::in(array_keys(self::PACKAGES))],
            'q' => ['nullable', 'string', 'max:80'],
        ]);
        $screen = $validated['screen'] ?? 'home';
        $packageId = $validated['package'] ?? null;
        $query = trim($validated['q'] ?? '');
        $selectedPackage = $packageId ? self::PACKAGES[$packageId] : null;

        if (in_array($screen, ['detail', 'booking', 'payment', 'success'], true) && ! $selectedPackage) {
            return redirect()->route('prototype.app', ['screen' => 'catalog'])
                ->with('error', 'Pilih paket terlebih dahulu untuk melanjutkan simulasi.');
        }

        $packages = collect(self::PACKAGES)
            ->filter(function (array $package) use ($query): bool {
                if ($query === '') {
                    return true;
                }

                $searchable = Str::lower(implode(' ', [
                    $package['name'],
                    $package['room'],
                    $package['style'],
                    ...$package['items'],
                ]));

                return Str::contains($searchable, Str::lower($query));
            });
        $featuredPackages = collect(self::PACKAGES)->take(2);
        $activeTab = match ($screen) {
            'catalog', 'detail', 'booking', 'payment' => 'catalog',
            'success', 'orders' => 'orders',
            default => $screen,
        };

        return view('prototype.app', compact(
            'screen',
            'packageId',
            'selectedPackage',
            'packages',
            'featuredPackages',
            'query',
            'activeTab',
        ));
    }
}
