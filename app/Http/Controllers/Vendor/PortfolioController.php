<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use App\Models\Review;
use App\Models\Vendor;
use App\Support\PublicImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'min_rating' => ['nullable', 'numeric', 'between:1,5'],
            'sort' => ['nullable', Rule::in(['rating', 'reviews', 'name'])],
        ]);

        $reviewStats = Review::query()
            ->join('orders', 'reviews.order_id', '=', 'orders.id')
            ->whereRaw("orders.status = 'completed'")
            ->select('orders.vendor_id')
            ->selectRaw('AVG(reviews.rating) as average_rating')
            ->selectRaw('COUNT(reviews.id) as review_count')
            ->groupBy('orders.vendor_id');

        $vendors = Vendor::query()
            ->leftJoinSub($reviewStats, 'review_stats', fn ($join) => $join->on('vendors.id', '=', 'review_stats.vendor_id'))
            ->select('vendors.*')
            ->selectRaw('review_stats.average_rating as completed_order_reviews_avg_rating')
            ->selectRaw('COALESCE(review_stats.review_count, 0) as completed_order_reviews_count')
            ->where('is_verified', true)
            ->when($filters['q'] ?? null, fn ($query, $name) => $query->where('business_name', 'like', '%' . $name . '%'))
            ->when($filters['city'] ?? null, fn ($query, $city) => $query->where('city', 'like', '%' . $city . '%'))
            ->when($filters['min_rating'] ?? null, fn ($query, $rating) => $query->whereRaw(
                'review_stats.average_rating >= CAST(? AS REAL)',
                [(float) $rating]
            ));

        match ($filters['sort'] ?? 'rating') {
            'reviews' => $vendors->orderByDesc('completed_order_reviews_count')->orderBy('business_name'),
            'name' => $vendors->orderBy('business_name'),
            default => $vendors->orderByDesc('completed_order_reviews_avg_rating')
                ->orderByDesc('completed_order_reviews_count')
                ->orderBy('business_name'),
        };

        $vendors = $vendors->paginate(12)->withQueryString();

        return view('vendors.index', compact('vendors'));
    }

    public function manage(): View
    {
        $vendor = $this->vendor();
        $projects = $vendor->portfolioProjects;

        return view('vendor.portfolio.manage', compact('vendor', 'projects'));
    }

    public function show(Vendor $vendor): View
    {
        abort_unless($vendor->is_verified, 404);

        $vendor->load('portfolioProjects');
        $packages = $vendor->packages()->where('is_active', true)->latest()->get();
        $completedOrders = $vendor->orders()
            ->where('status', 'completed')
            ->with('package:id,name,room_type')
            ->latest('updated_at')
            ->get();
        $reviews = $vendor->completedOrderReviews()
            ->with(['customer', 'package'])
            ->latest()
            ->get();
        $averageRating = $reviews->avg('rating');

        return view('vendors.portfolio.show', compact('vendor', 'packages', 'completedOrders', 'reviews', 'averageRating'));
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $vendor = $this->vendor();
        $data = $request->validate($this->projectRules('project.'));
        $payload = $data['project'];

        foreach ([
            'image' => 'image_path',
            'before_image' => 'before_image_path',
            'after_image' => 'after_image_path',
        ] as $field => $pathAttribute) {
            if ($request->hasFile('project.' . $field)) {
                $payload[$pathAttribute] = $request->file('project.' . $field)->store('portfolio', 'public');
            }

            unset($payload[$field]);
        }

        $vendor->portfolioProjects()->create($payload);

        return redirect()->route('vendor.portfolio.manage')->with('success', 'Proyek portofolio ditambahkan.');
    }

    public function updateProject(Request $request, PortfolioProject $portfolioProject): RedirectResponse
    {
        $this->ensureOwner($portfolioProject);

        $prefix = 'projects.' . $portfolioProject->id . '.';
        $rules = $this->projectRules($prefix);
        $rules[$prefix . 'remove_before_image'] = ['nullable', 'boolean'];
        $rules[$prefix . 'remove_after_image'] = ['nullable', 'boolean'];
        $data = $request->validate($rules);
        $payload = $data['projects'][$portfolioProject->id];
        $oldImages = [];

        foreach ([
            'image' => ['image_path', null],
            'before_image' => ['before_image_path', 'remove_before_image'],
            'after_image' => ['after_image_path', 'remove_after_image'],
        ] as $field => [$pathAttribute, $removeField]) {
            if ($request->hasFile($prefix . $field)) {
                $oldImages[] = $portfolioProject->{$pathAttribute};
                $payload[$pathAttribute] = $request->file($prefix . $field)->store('portfolio', 'public');
            } elseif ($removeField && $request->boolean($prefix . $removeField)) {
                $oldImages[] = $portfolioProject->{$pathAttribute};
                $payload[$pathAttribute] = null;
            }

            unset($payload[$field]);
            if ($removeField) {
                unset($payload[$removeField]);
            }
        }

        $portfolioProject->update($payload);

        PublicImageStorage::deleteIfUnreferenced($oldImages);

        return redirect()->route('vendor.portfolio.manage')->with('success', 'Proyek portofolio diperbarui.');
    }

    public function destroyProject(PortfolioProject $portfolioProject): RedirectResponse
    {
        $this->ensureOwner($portfolioProject);

        $imagePaths = [
            $portfolioProject->image_path,
            $portfolioProject->before_image_path,
            $portfolioProject->after_image_path,
        ];
        $portfolioProject->delete();

        PublicImageStorage::deleteIfUnreferenced($imagePaths);

        return redirect()->route('vendor.portfolio.manage')->with('success', 'Proyek portofolio dihapus.');
    }

    private function vendor(): Vendor
    {
        $vendor = Auth::user()->vendor;
        abort_unless($vendor, 403);

        return $vendor;
    }

    private function ensureOwner(PortfolioProject $portfolioProject): void
    {
        abort_unless($portfolioProject->vendor_id === $this->vendor()->id, 403);
    }

    private function projectRules(string $prefix): array
    {
        return [
            $prefix . 'title' => ['required', 'string', 'max:120'],
            $prefix . 'room_type' => ['required', Rule::in(array_keys(config('isiin.room_types')))],
            $prefix . 'location' => ['required', 'string', 'max:120'],
            $prefix . 'completion_year' => ['required', 'integer', 'between:1900,' . now()->year],
            $prefix . 'description' => ['required', 'string', 'max:2000'],
            $prefix . 'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            $prefix . 'before_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            $prefix . 'after_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
