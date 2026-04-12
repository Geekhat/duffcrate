<?php

declare(strict_types=1);

namespace OCA\Crate\Controller;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends OCSController
{
    public function __construct(
        string $appName,
        IRequest $request,
        private readonly IConfig $config,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }

    private function userId(): string
    {
        return $this->userSession->getUser()->getUID();
    }

    #[NoAdminRequired]
    public function getDiscogsToken(): DataResponse
    {
        $token = $this->config->getUserValue($this->userId(), 'crate', 'discogs_token', '');
        return new DataResponse(['hasToken' => $token !== '']);
    }

    #[NoAdminRequired]
    public function setDiscogsToken(string $token = ''): DataResponse
    {
        $this->config->setUserValue($this->userId(), 'crate', 'discogs_token', trim($token));
        return new DataResponse([]);
    }
}
