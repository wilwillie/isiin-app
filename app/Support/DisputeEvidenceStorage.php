<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DisputeEvidenceStorage
{
    /**
     * @param  array<int, UploadedFile>  $photos
     * @return array<int, string>
     */
    public function store(array $photos, int $disputeId): array
    {
        $disk = $this->disk();
        $paths = [];

        try {
            foreach ($photos as $photo) {
                $path = $disk->putFile('disputes/' . $disputeId, $photo);
                if (! is_string($path)) {
                    throw new RuntimeException('Bukti sengketa gagal disimpan.');
                }

                $paths[] = $path;
            }
        } catch (Throwable $exception) {
            $this->delete($paths);

            throw $exception;
        }

        return $paths;
    }

    /**
     * @param  array<int, string>  $paths
     */
    public function delete(array $paths): void
    {
        if ($paths !== []) {
            $this->disk()->delete($paths);
        }
    }

    public function response(string $path): StreamedResponse
    {
        return PrivateFileResponse::stream($this->disk(), $path);
    }

    private function disk(): Filesystem
    {
        return Storage::disk('private');
    }
}
