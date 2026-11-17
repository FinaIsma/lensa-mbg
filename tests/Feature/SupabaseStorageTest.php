<?php

namespace Tests\Feature;

use App\Models\Evidence;
use App\Services\SupabaseStorageService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SupabaseStorageTest extends TestCase
{
    public function test_can_upload_evidence_to_supabase_bucket(): void
    {
        $service = app(SupabaseStorageService::class);
        $fakeFile = UploadedFile::fake()->image('test_portion.jpg', 400, 300);

        $url = $service->uploadEvidence($fakeFile, 'hasil');

        $this->assertNotEmpty($url);

        $evidence = new Evidence([
            'file_path' => $url,
            'file_type' => 'image/jpeg',
            'is_public' => true,
        ]);

        $this->assertSame($url, $evidence->url);
    }
}
