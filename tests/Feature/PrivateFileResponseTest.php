<?php

namespace Tests\Feature;

use App\Support\DisputeEvidenceStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PrivateFileResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_files_are_streamed_with_private_headers(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('disputes/1/evidence.txt', 'private evidence');

        $response = app(DisputeEvidenceStorage::class)->response('disputes/1/evidence.txt');
        ob_start();
        $response->sendContent();
        $body = ob_get_clean();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            ['private', 'no-store'],
            array_map('trim', explode(',', $response->headers->get('Cache-Control')))
        );
        $this->assertSame('private evidence', $body);
    }

    public function test_missing_private_files_return_not_found(): void
    {
        Storage::fake('private');

        $this->expectException(NotFoundHttpException::class);

        app(DisputeEvidenceStorage::class)->response('missing.txt');
    }
}
