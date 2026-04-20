<?php

declare(strict_types=1);

namespace OCA\Crate\Service;

class PriceChartingService extends AbstractApiService
{
    private const API_BASE = 'https://www.pricecharting.com/api';

    protected function serviceName(): string
    {
        return 'PriceCharting';
    }

    protected function credentialKey(): string
    {
        return 'crate/pricecharting_token';
    }

    public function getToken(string $userId): string
    {
        return $this->getCredential($userId);
    }

    public function hasToken(string $userId): bool
    {
        return $this->getCredential($userId) !== '';
    }

    /**
     * Search PriceCharting for a product by title.
     * Returns up to 10 results: [{priceChartingId, title, platform}]
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $userId, string $query): array
    {
        $token = $this->getCredential($userId);
        if ($token === '') {
            return [];
        }

        $body = $this->getJson(self::API_BASE . '/products', [
            'q'     => $query,
            'token' => $token,
        ]);

        $products = array_slice((array)($body['products'] ?? []), 0, 10);
        return array_values(array_map(fn(array $p) => [
            'priceChartingId' => (string)($p['id'] ?? ''),
            'title'           => (string)($p['product-name'] ?? ''),
            'platform'        => (string)($p['console-name'] ?? ''),
        ], $products));
    }

    /**
     * Fetch prices for a product by PriceCharting ID.
     * Prices are stored in cents USD; we return them as dollars (float).
     *
     * @return array{loose: float|null, cib: float|null, new: float|null}|null
     */
    public function getPrices(string $userId, string $productId): ?array
    {
        $token = $this->getCredential($userId);
        if ($token === '') {
            return null;
        }

        $body = $this->getJson(self::API_BASE . '/product/' . rawurlencode($productId), [
            'token' => $token,
        ]);

        if (empty($body)) {
            return null;
        }

        return [
            'loose' => isset($body['loose-price']) ? round((int)$body['loose-price'] / 100, 2) : null,
            'cib'   => isset($body['cib-price'])   ? round((int)$body['cib-price']   / 100, 2) : null,
            'new'   => isset($body['new-price'])   ? round((int)$body['new-price']   / 100, 2) : null,
        ];
    }

    /**
     * Search for the best matching product and return its prices.
     * Returns null if no token, no results, or API failure.
     *
     * @return array{loose: float|null, cib: float|null, new: float|null}|null
     */
    public function searchAndFetchPrices(string $userId, string $query): ?array
    {
        $results = $this->search($userId, $query);
        if (empty($results)) {
            return null;
        }

        $productId = $results[0]['priceChartingId'];
        if ($productId === '') {
            return null;
        }

        return $this->getPrices($userId, $productId);
    }
}
