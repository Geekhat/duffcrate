<?php

declare(strict_types=1);

namespace OCA\Crate\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Security\ICredentialsManager;

class SettingsController extends OCSController
{
    use UsesAuthenticatedUser;

    private const VALID_CURRENCIES = [
        'GBP', 'USD', 'EUR', 'JPY', 'AUD', 'CAD', 'CHF', 'SEK', 'NOK', 'DKK',
        'NZD', 'ZAR', 'BRL', 'MXN', 'PLN', 'CZK', 'HUF', 'RUB', 'INR', 'CNY',
        'KRW', 'HKD', 'SGD', 'TWD', 'THB', 'MYR', 'PHP', 'IDR', 'TRY', 'ILS',
    ];

    public function __construct(
        string $appName,
        IRequest $request,
        private readonly IConfig $config,
        private readonly IUserSession $userSession,
        private readonly ICredentialsManager $credentialsManager,
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function getDiscogsToken(): DataResponse
    {
        $token = (string) ($this->credentialsManager->retrieve($this->userId(), 'crate/discogs_token') ?? '');
        return new DataResponse(['hasToken' => $token !== '']);
    }

    #[NoAdminRequired]
    public function setDiscogsToken(string $token = ''): DataResponse
    {
        $uid = $this->userId();
        $trimmed = trim($token);
        if ($trimmed === '') {
            $this->credentialsManager->delete($uid, 'crate/discogs_token');
        } else {
            $this->credentialsManager->store($uid, 'crate/discogs_token', $trimmed);
        }
        return new DataResponse([]);
    }

    #[NoAdminRequired]
    public function getMarketSettings(): DataResponse
    {
        $uid = $this->userId();
        $autoFetch = $this->config->getUserValue($uid, 'crate', 'auto_fetch_market_rates', '0') === '1';
        return new DataResponse([
            'autoFetchMarketRates' => $autoFetch,
            'marketCurrency'       => $this->config->getUserValue($uid, 'crate', 'market_currency', 'GBP'),
        ]);
    }

    #[NoAdminRequired]
    public function setMarketSettings(bool $autoFetchMarketRates = false, string $marketCurrency = 'GBP'): DataResponse
    {
        $uid = $this->userId();
        $currency = strtoupper($marketCurrency);
        if (!in_array($currency, self::VALID_CURRENCIES, true)) {
            return new DataResponse(['error' => 'Invalid currency'], Http::STATUS_BAD_REQUEST);
        }
        $this->config->setUserValue($uid, 'crate', 'auto_fetch_market_rates', $autoFetchMarketRates ? '1' : '0');
        $this->config->setUserValue($uid, 'crate', 'market_currency', $currency);
        return new DataResponse([]);
    }

    /**
     * PUT /api/v1/settings/currency
     * Update just the market currency preference — convenience endpoint for Android app.
     */
    #[NoAdminRequired]
    public function setCurrency(string $currency = 'GBP'): DataResponse
    {
        $c = strtoupper($currency);
        if (!in_array($c, self::VALID_CURRENCIES, true)) {
            return new DataResponse(['error' => 'Invalid currency'], Http::STATUS_BAD_REQUEST);
        }
        $this->config->setUserValue($this->userId(), 'crate', 'market_currency', $c);
        return new DataResponse(['marketCurrency' => $c]);
    }

    /**
     * GET /api/v1/me
     * Returns the current user's profile and app settings — used by the Android app.
     */
    #[NoAdminRequired]
    public function me(): DataResponse
    {
        // userId() already handles the null check
        $uid      = $this->userId();
        $user     = $this->userSession->getUser();
        $currency = $this->config->getUserValue($uid, 'crate', 'market_currency', 'GBP');
        $hasToken = (string) ($this->credentialsManager->retrieve($uid, 'crate/discogs_token') ?? '') !== '';
        $autoFetch = $this->config->getUserValue($uid, 'crate', 'auto_fetch_market_rates', '0') === '1';

        return new DataResponse([
            'userId'              => $uid,
            'displayName'         => $user->getDisplayName(),
            'avatarUrl'           => '/index.php/avatar/' . urlencode($uid) . '/64',
            'hasDiscogsToken'     => $hasToken,
            'marketCurrency'      => $currency,
            'autoFetchMarketRates' => $autoFetch,
        ]);
    }
}
