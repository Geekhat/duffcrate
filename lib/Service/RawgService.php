<?php

declare(strict_types=1);

namespace OCA\Crate\Service;

class RawgService extends AbstractApiService
{
    private const API_BASE = 'https://api.rawg.io/api';

    protected function serviceName(): string
    {
        return 'RAWG';
    }

    protected function credentialKey(): string
    {
        return 'crate/rawg_key';
    }

    /**
     * Search RAWG for games by free-text query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $userId, string $query): array
    {
        $key = $this->getCredential($userId);
        if ($key === '') {
            return [];
        }

        $body = $this->getJson(self::API_BASE . '/games', [
            'key'       => $key,
            'search'    => $query,
            'page_size' => '10',
        ]);

        $results = array_slice((array)($body['results'] ?? []), 0, 10);
        return array_values(array_map(fn(array $r) => $this->normaliseResult($r), $results));
    }

    /**
     * Fetch full game details from RAWG /games/{id}.
     *
     * @return array<string, mixed>
     */
    public function getGame(string $userId, string $gameId): array
    {
        $key = $this->getCredential($userId);
        if ($key === '') {
            return [];
        }

        $body = $this->getJson(self::API_BASE . '/games/' . rawurlencode($gameId), ['key' => $key]);
        if (empty($body)) {
            return [];
        }

        return $this->normaliseGame($body);
    }

    /** @param array<string, mixed> $r */
    private function normaliseResult(array $r): array
    {
        $year = null;
        if (!empty($r['released'])) {
            $year = (int)substr((string)$r['released'], 0, 4) ?: null;
        }

        $genres = array_map(fn(array $g) => $g['name'] ?? '', (array)($r['genres'] ?? []));

        return [
            'rawgId' => (string)($r['id'] ?? ''),
            'title'  => $r['name'] ?? '',
            'year'   => $year,
            'thumb'  => $r['background_image'] ?? null,
            'genres' => $genres ? implode(', ', array_filter($genres)) : null,
        ];
    }

    /** @param array<string, mixed> $r */
    private function normaliseGame(array $r): array
    {
        $year = null;
        if (!empty($r['released'])) {
            $year = (int)substr((string)$r['released'], 0, 4) ?: null;
        }

        $devs      = (array)($r['developers'] ?? []);
        $developer = !empty($devs[0]['name']) ? (string)$devs[0]['name'] : null;

        $pubs      = (array)($r['publishers'] ?? []);
        $publisher = !empty($pubs[0]['name']) ? (string)$pubs[0]['name'] : null;

        $genreNames = array_map(fn(array $g) => $g['name'] ?? '', (array)($r['genres'] ?? []));
        $genres     = $genreNames ? implode(', ', array_filter($genreNames)) : null;

        $desc = strip_tags((string)($r['description'] ?? ''));
        $desc = trim($desc) ?: null;

        return [
            'rawgId'     => (string)($r['id'] ?? ''),
            'title'      => $r['name'] ?? '',
            'artist'     => $developer,
            'year'       => $year,
            'label'      => $publisher,
            'genres'     => $genres,
            'overview'   => $desc,
            'artworkUrl' => $r['background_image'] ?? null,
            'thumb'      => $r['background_image'] ?? null,
        ];
    }
}
