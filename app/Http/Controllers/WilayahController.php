<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WilayahController extends Controller
{
    private $baseApi = 'https://emsifa.github.io/api-wilayah-indonesia/api';

    private function getLocalData()
    {
        $path = public_path('data_wilayah.json');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            return json_decode($content, true);
        }
        return null;
    }

    public function getProvinces()
    {
        $local = $this->getLocalData();
        if ($local && !empty($local['provinces'])) {
            $provinces = $local['provinces'];
            if (isset($provinces['value'])) {
                $provinces = $provinces['value'];
            }
            return response()->json($provinces);
        }

        try {
            $response = Http::withoutVerifying()->timeout(5)->get("{$this->baseApi}/provinces.json");
            if ($response->successful()) {
                $json = $response->json();
                return response()->json(isset($json['value']) ? $json['value'] : $json);
            }
        } catch (\Exception $e) {}

        return response()->json([
            ['id' => '11', 'name' => 'ACEH'],
            ['id' => '12', 'name' => 'SUMATERA UTARA'],
            ['id' => '13', 'name' => 'SUMATERA BARAT'],
            ['id' => '14', 'name' => 'RIAU'],
            ['id' => '15', 'name' => 'JAMBI'],
            ['id' => '16', 'name' => 'SUMATERA SELATAN'],
            ['id' => '17', 'name' => 'BENGKULU'],
            ['id' => '18', 'name' => 'LAMPUNG'],
            ['id' => '19', 'name' => 'KEPULAUAN BANGKA BELITUNG'],
            ['id' => '21', 'name' => 'KEPULAUAN RIAU'],
            ['id' => '31', 'name' => 'DKI JAKARTA'],
            ['id' => '32', 'name' => 'JAWA BARAT'],
            ['id' => '33', 'name' => 'JAWA TENGAH'],
            ['id' => '34', 'name' => 'DI YOGYAKARTA'],
            ['id' => '35', 'name' => 'JAWA TIMUR'],
            ['id' => '36', 'name' => 'BANTEN'],
            ['id' => '51', 'name' => 'BALI'],
            ['id' => '52', 'name' => 'NUSA TENGGARA BARAT'],
            ['id' => '53', 'name' => 'NUSA TENGGARA TIMUR'],
            ['id' => '61', 'name' => 'KALIMANTAN BARAT'],
            ['id' => '62', 'name' => 'KALIMANTAN TENGAH'],
            ['id' => '63', 'name' => 'KALIMANTAN SELATAN'],
            ['id' => '64', 'name' => 'KALIMANTAN TIMUR'],
            ['id' => '65', 'name' => 'KALIMANTAN UTARA'],
            ['id' => '71', 'name' => 'SULAWESI UTARA'],
            ['id' => '72', 'name' => 'SULAWESI TENGAH'],
            ['id' => '73', 'name' => 'SULAWESI SELATAN'],
            ['id' => '74', 'name' => 'SULAWESI TENGGARA'],
            ['id' => '75', 'name' => 'GORONTALO'],
            ['id' => '76', 'name' => 'SULAWESI BARAT'],
            ['id' => '81', 'name' => 'MALUKU'],
            ['id' => '82', 'name' => 'MALUKU UTARA'],
            ['id' => '91', 'name' => 'PAPUA BARAT'],
            ['id' => '92', 'name' => 'PAPUA'],
            ['id' => '93', 'name' => 'PAPUA SELATAN'],
            ['id' => '94', 'name' => 'PAPUA TENGAH'],
            ['id' => '95', 'name' => 'PAPUA PEGUNUNGAN'],
            ['id' => '96', 'name' => 'PAPUA BARAT DAYA']
        ]);
    }

    public function getRegencies($provinceId)
    {
        $local = $this->getLocalData();
        if ($local && !empty($local['regencies'][$provinceId])) {
            $regencies = $local['regencies'][$provinceId];
            if (isset($regencies['value'])) {
                $regencies = $regencies['value'];
            }
            return response()->json($regencies);
        }

        try {
            $response = Http::withoutVerifying()->timeout(5)->get("{$this->baseApi}/regencies/{$provinceId}.json");
            if ($response->successful()) {
                $json = $response->json();
                return response()->json(isset($json['value']) ? $json['value'] : $json);
            }
        } catch (\Exception $e) {}

        return response()->json([]);
    }

    public function getDistricts($regencyId)
    {
        $path = public_path('data_districts.json');
        if (file_exists($path)) {
            $content = @file_get_contents($path);
            if ($content) {
                $districtsData = json_decode($content, true);
                if (isset($districtsData[$regencyId])) {
                    $item = $districtsData[$regencyId];
                    if (isset($item['value'])) {
                        $item = $item['value'];
                    }
                    return response()->json($item);
                }
            }
        }

        try {
            $response = Http::withoutVerifying()->timeout(3)->get("{$this->baseApi}/districts/{$regencyId}.json");
            if ($response->successful()) {
                $json = $response->json();
                return response()->json(isset($json['value']) ? $json['value'] : $json);
            }
        } catch (\Exception $e) {}

        return response()->json([]);
    }
}
