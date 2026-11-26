<?php

namespace App\Support;

use App\Models\Package;
use App\Models\PortfolioProject;
use Illuminate\Support\Facades\Storage;

class PublicImageStorage
{
    public static function deleteIfUnreferenced(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            $usedByPackage = Package::query()->where('image_path', $path)->exists();
            $usedByProject = PortfolioProject::query()
                ->where('image_path', $path)
                ->orWhere('before_image_path', $path)
                ->orWhere('after_image_path', $path)
                ->exists();

            if (! $usedByPackage && ! $usedByProject) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
