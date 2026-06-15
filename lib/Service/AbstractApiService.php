<?php

declare(strict_types=1);

namespace OCA\Crate\Service;

use OCP\Http\Client\IClientService;
use OCP\Security\ICredentialsManager;
use Psr\Log\LoggerInterface;

/**
 * Shared HTTP client wrapper for the external-API enrichment services
 * (TMDB, RAWG, ComicVine, Open Library, PriceCharting).
 *
 * Subclasses supply a service name for log messages and, when applicable,
 * the credential key used to retrieve the per-user API token or key.
 *
 * Error handling is deliberately permissive: network / parse errors are
 * logged (with query-string-stripped URLs, so tokens don't leak) and
 * surfaced as an empty array, so callers can treat "no data" the same
 * whether the upstream is down or the result set is empty.
 */
abstract class AbstractApiService
{
    /** Default request timeout in seconds. */
    protected const DEFAULT_TIMEOUT = 10;

    public function __construct(
        protected readonly IClientService $clientService,
        protected readonly ICredentialsManager $credentialsManager,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /** Short human-readable service name used in log messages. */
    abstract protected function serviceName(): string;

    /**
     * The `crate/...` credential key used to retrieve this service's
     * per-user token or API key. Services that don't use credentials
     * (e.g. Open Library) may return `null`.
     */
    protected function credentialKey(): ?string
    {
        return null;
    }

/** Retrieve the user's stored credential for this service, or '' if missing.
 *  Falls back to the global fallback user's credential if the user has none.
 */
    protected function getCredential(string $userId): string
    {
	$key = $this->credentialKey();
    if ($key === null) {
        return '';
    }

    // Try the current user's own credential first
    $credential = (string)($this->credentialsManager->retrieve($userId, $key) ?? '');
    if ($credential !== '') {
        return $credential;
    }

    // Fall back to the global fallback user's credential
    $fallbackUser = 'geekhat'; // ← change this to your admin username
    return (string)($this->credentialsManager->retrieve($fallbackUser, $key) ?? '');
}

    /**
     * Perform a GET and return the JSON-decoded body.
     * Returns an empty array on any HTTP / parse error, after logging a
     * warning with the query-string-stripped URL.
     *
     * @param array<string, string|int> $query
     * @param array<string, string>     $headers
     * @return array<string, mixed>
     */
    protected function getJson(string $url, array $query = [], array $headers = []): array
    {
        $options = [
            'headers' => array_merge(['Accept' => 'application/json'], $headers),
            'timeout' => static::DEFAULT_TIMEOUT,
        ];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->clientService->newClient()->get($url, $options);
            return json_decode($response->getBody(), true) ?? [];
        } catch (\Exception $e) {
            $this->logWarning($url, $e);
            return [];
        }
    }

    /** Emit a standardised warning log entry for an API error. */
    protected function logWarning(string $url, \Throwable $e): void
    {
        $this->logger->warning($this->serviceName() . ' API error for {url}: {msg}', [
            'url' => strtok($url, '?') ?: $url,
            'msg' => $e->getMessage(),
            'app' => 'crate',
        ]);
    }
}
