<?php

declare(strict_types=1);

namespace OCA\Crate\Service;

use OCP\Http\Client\IClientService;
use OCP\IConfig;

class DiscogsService
{
    private const API_BASE = 'https://api.discogs.com';
    private const USER_AGENT = 'CrateNextcloudApp/0.1 +https://gitea.macecloud.co.uk/macebox/crate';

    public function __construct(
        private readonly IClientService $clientService,
        private readonly IConfig $config,
    ) {
    }

    /**
     * Search Discogs by free-text query (artist, album, or both).
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $userId, string $query): array
    {
        return $this->request($userId, ['q' => $query, 'type' => 'release']);
    }

    /**
     * Search Discogs by barcode.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchByBarcode(string $userId, string $barcode): array
    {
        return $this->request($userId, ['barcode' => $barcode, 'type' => 'release']);
    }

    /**
     * @param array<string, string> $params
     * @return array<int, array<string, mixed>>
     */
    private function request(string $userId, array $params): array
    {
        $token = $this->config->getUserValue($userId, 'crate', 'discogs_token', '');
        if ($token === '') {
            return [];
        }

        $params['token'] = $token;
        $params['per_page'] = '10';

        $client = $this->clientService->newClient();
        $response = $client->get(self::API_BASE . '/database/search', [
            'query' => $params,
            'headers' => [
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'application/json',
            ],
            'timeout' => 10,
        ]);

        $body = json_decode($response->getBody(), true);
        $results = $body['results'] ?? [];

        return array_values(array_map(
            fn(array $r) => $this->normalise($r),
            array_slice($results, 0, 10),
        ));
    }

    /**
     * Normalise a Discogs search result into a consistent shape for the frontend.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function normalise(array $result): array
    {
        // Discogs title format: "Artist Name - Album Title"
        $rawTitle = $result['title'] ?? '';
        $parts = explode(' - ', $rawTitle, 2);
        $artist = trim($parts[0] ?? '');
        $album = trim($parts[1] ?? $rawTitle);

        // Map Discogs format array to our single-format string
        $formats = array_map('strtolower', (array)($result['format'] ?? []));
        $format = $this->mapFormat($formats);

        $year = isset($result['year']) ? (int)$result['year'] : null;
        if ($year === 0) {
            $year = null;
        }

        return [
            'discogsId' => (string)($result['id'] ?? ''),
            'artist'    => $artist,
            'title'     => $album,
            'format'    => $format,
            'year'      => $year,
            'thumb'     => $result['thumb'] ?? null,
            'label'     => implode(', ', array_slice((array)($result['label'] ?? []), 0, 2)),
            'country'   => $result['country'] ?? null,
        ];
    }

    /**
     * Map a Discogs formats array to our canonical format string.
     *
     * @param string[] $formats
     */
    private function mapFormat(array $formats): string
    {
        if (in_array('vinyl', $formats, true)) {
            return 'Vinyl';
        }
        if (in_array('sacd', $formats, true)) {
            return 'SACD';
        }
        if (in_array('cd', $formats, true)) {
            return 'CD';
        }
        if (in_array('cassette', $formats, true)) {
            return 'Cassette';
        }
        if (in_array('minidisc', $formats, true)) {
            return 'MiniDisc';
        }
        return 'CD'; // sensible default for music
    }
}
