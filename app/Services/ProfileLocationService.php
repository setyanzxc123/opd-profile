<?php

namespace App\Services;

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use RuntimeException;

class ProfileLocationService
{
    private const CACHE_PREFIX = 'nominatim_search_';

    public function search(string $query): array
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            throw new RuntimeException('Kueri pencarian kosong.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $lowerQuery = function_exists('mb_strtolower') ? mb_strtolower($trimmed, 'UTF-8') : strtolower($trimmed);
        $cacheKey   = self::CACHE_PREFIX . md5($lowerQuery);
        $cacheStore = cache();

        if ($cacheStore) {
            $cached = $cacheStore->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $userAgent = $this->buildUserAgent();
        $client    = Services::curlrequest([
            'baseURI' => 'https://nominatim.openstreetmap.org/',
            'timeout' => 5,
        ]);

        try {
            $response = $client->get('search', [
                'query' => [
                    'format'         => 'jsonv2',
                    'q'              => $trimmed,
                    'addressdetails' => 1,
                    'limit'          => 5,
                ],
                'headers' => [
                    'User-Agent'      => $userAgent,
                    'Accept-Language' => 'id,en;q=0.8',
                ],
            ]);
        } catch (\Throwable $throwable) {
            log_message('error', 'Pencarian Nominatim gagal: {error}', ['error' => $throwable->getMessage()]);

            throw new RuntimeException('Tidak dapat terhubung ke layanan lokasi. Coba lagi nanti.', ResponseInterface::HTTP_BAD_GATEWAY, $throwable);
        }

        if ($response->getStatusCode() !== 200) {
            log_message('warning', 'Nominatim mengembalikan status {status}.', ['status' => $response->getStatusCode()]);

            throw new RuntimeException('Layanan lokasi sedang tidak tersedia.', ResponseInterface::HTTP_BAD_GATEWAY);
        }

        try {
            $decoded = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $throwable) {
            log_message('error', 'Gagal mengurai hasil Nominatim: {error}', ['error' => $throwable->getMessage()]);

            throw new RuntimeException('Data lokasi tidak dapat diproses.', ResponseInterface::HTTP_BAD_GATEWAY, $throwable);
        }

        $results = [];
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $lat = isset($item['lat']) ? (float) $item['lat'] : null;
                $lng = isset($item['lon']) ? (float) $item['lon'] : (isset($item['lng']) ? (float) $item['lng'] : null);

                if ($lat === null || $lng === null) {
                    continue;
                }

                $label = trim((string) ($item['display_name'] ?? ''));
                $boundingBox = [];
                if (isset($item['boundingbox']) && is_array($item['boundingbox']) && count($item['boundingbox']) === 4) {
                    $boundingBox = array_values($item['boundingbox']);
                }

                $results[] = [
                    'label'       => $label !== '' ? $label : sprintf('Lat %.5f, Lng %.5f', $lat, $lng),
                    'lat'         => $lat,
                    'lng'         => $lng,
                    'boundingBox' => $boundingBox,
                ];
            }
        }

        if ($cacheStore) {
            $cacheStore->save($cacheKey, $results, 600);
        }

        return $results;
    }

    private function buildUserAgent(): string
    {
        $config = config('App');
        $baseUrl = rtrim((string) ($config->baseURL ?? ''), '/');
        if ($baseUrl === '') {
            $baseUrl = 'http://localhost';
        }
        $contactEmail = $this->resolveContactEmail();

        $userAgent = 'OPDProfileCMS/1.0 (+' . $baseUrl;
        if ($contactEmail !== '') {
            $userAgent .= '; ' . $contactEmail;
        }

        return $userAgent . ')';
    }

    private function resolveContactEmail(): string
    {
        $contactEmail = trim((string) env('nominatim.contactEmail', ''));
        if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            return $contactEmail;
        }

        try {
            $emailConfig = config('Email');
        } catch (\Throwable $ignore) {
            $emailConfig = null;
        }

        if ($emailConfig && property_exists($emailConfig, 'fromEmail')) {
            $candidate = trim((string) $emailConfig->fromEmail);
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        return '';
    }
}
