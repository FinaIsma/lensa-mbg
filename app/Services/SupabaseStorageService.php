<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    protected string $url;

    protected string $key;

    protected string $defaultBucket;

    protected string $evidenceBucket;

    public function __construct()
    {
        $this->url = rtrim(config('services.supabase.url') ?? 'https://uovagfdbozaeaywzgbpa.supabase.co', '/');
        $this->key = (string) (config('services.supabase.key') ?? '');
        $this->defaultBucket = config('services.supabase.bucket') ?? 'register';
        $this->evidenceBucket = config('services.supabase.bucket_evidence') ?? 'evidences';
    }

    /**
     * Upload evidence with automatic fallback to local storage if network or Supabase fails.
     * Guarantees zero downtime and zero 500 errors during operations and demos.
     */
    public function uploadEvidence(UploadedFile $file, string $folder = ''): string
    {
        $folderName = trim($folder, '/');

        try {
            // Coba unggah ke Supabase Cloud Storage (Bucket: evidences)
            return $this->upload($file, $folderName, $this->evidenceBucket);
        } catch (\Throwable $e) {
            Log::warning('Supabase uploadEvidence gagal, beralih ke storage lokal: '.$e->getMessage());

            // Fallback aman ke storage lokal public
            return $file->store('evidences/'.$folderName, 'public');
        }
    }

    /**
     * Upload an UploadedFile directly to a Supabase Storage bucket.
     *
     * @param  string  $folder  Subfolder inside bucket (e.g. 'ktp', 'kantor', 'surat')
     * @param  string|null  $bucket  Custom bucket name if not default
     * @return string Public URL of the uploaded file
     *
     * @throws Exception
     */
    public function upload(UploadedFile $file, string $folder = '', ?string $bucket = null): string
    {
        $targetBucket = $bucket ?: $this->defaultBucket;
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $filename = time().'_'.Str::random(16).'.'.$extension;
        $path = $folder ? trim($folder, '/').'/'.$filename : $filename;

        if (empty($this->key)) {
            throw new Exception('Supabase API Key (SUPABASE_KEY / SUPABASE_SERVICE_ROLE_KEY) belum dikonfigurasi di file .env.');
        }

        $endpoint = "{$this->url}/storage/v1/object/{$targetBucket}/{$path}";

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileContent = file_get_contents($file->getRealPath());

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->key,
            'apikey' => $this->key,
            'Content-Type' => $mimeType,
            'x-upsert' => 'true',
        ])->withBody(
            $fileContent,
            $mimeType
        )->post($endpoint);

        if (! $response->successful()) {
            $errorMessage = $response->json('message')
                ?? $response->json('error')
                ?? $response->body();
            throw new Exception("Gagal mengunggah file ke Supabase Storage [Bucket: {$targetBucket}, Path: {$path}]: {$errorMessage}");
        }

        return $this->getPublicUrl($path, $targetBucket);
    }

    /**
     * Get the public URL for a file stored in Supabase Storage.
     */
    public function getPublicUrl(string $path, ?string $bucket = null): string
    {
        $targetBucket = $bucket ?: $this->defaultBucket;

        return "{$this->url}/storage/v1/object/public/{$targetBucket}/".ltrim($path, '/');
    }
}
