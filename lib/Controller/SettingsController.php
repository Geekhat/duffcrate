<?php

declare(strict_types=1);

namespace OCA\Crate\Controller;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Security\ICredentialsManager;

class SettingsController extends OCSController
{
    public function __construct(
        string $appName,
        IRequest $request,
        private readonly IConfig $config,
        private readonly IUserSession $userSession,
        private readonly ICredentialsManager $credentialsManager,
    ) {
        parent::__construct($appName, $request);
    }

    private function userId(): string
    {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \OCP\AppFramework\OCS\OCSForbiddenException('Not authenticated');
        }
        return $user->getUID();
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
        $this->config->setUserValue($uid, 'crate', 'auto_fetch_market_rates', $autoFetchMarketRates ? '1' : '0');
        $this->config->setUserValue($uid, 'crate', 'market_currency', strtoupper($marketCurrency));
        return new DataResponse([]);
    }

    /**
     * PUT /api/v1/settings/currency
     * Update just the market currency preference — convenience endpoint for Android app.
     */
    #[NoAdminRequired]
    public function setCurrency(string $currency = 'GBP'): DataResponse
    {
        $this->config->setUserValue($this->userId(), 'crate', 'market_currency', strtoupper($currency));
        return new DataResponse(['marketCurrency' => strtoupper($currency)]);
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
