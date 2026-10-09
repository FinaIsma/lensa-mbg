<?php

namespace Tests\Unit;

use App\Services\SupabaseStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupabaseStorageServiceTest extends TestCase
{
    public function test_can_upload_file_to_supabase_bucket()
    {
        config([
            'services.supabase.url' => 'https://test-project.supabase.co',
            'services.supabase.key' => 'test-api-key',
            'services.supabase.bucket' => 'register',
        ]);

        Http::fake([
            'https://test-project.supabase.co/storage/v1/object/register/*' => Http::response(['Key' => 'register/ktp/test.jpg'], 200),
        ]);

        $service = new SupabaseStorageService();
        $file = UploadedFile::fake()->image('test.jpg');

        $url = $service->upload($file, 'ktp', 'register');

        $this->assertStringStartsWith('https://test-project.supabase.co/storage/v1/object/public/register/ktp/', $url);
    }

    public function test_throws_exception_when_key_is_missing()
    {
        config([
            'services.supabase.key' => '',
        ]);

        $service = new SupabaseStorageService();
        $file = UploadedFile::fake()->image('test.jpg');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Supabase API Key');

        $service->upload($file, 'ktp', 'register');
    }
}
