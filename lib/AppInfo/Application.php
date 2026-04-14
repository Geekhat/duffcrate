<?php

declare(strict_types=1);

namespace OCA\Crate\AppInfo;

use OCA\Crate\Listener\ContentSecurityPolicyListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class Application extends App implements IBootstrap
{
    public const APP_ID = 'crate';

    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void
    {
        $context->registerSearchProvider(\OCA\Crate\Search\Provider::class);
        $context->registerEventListener(AddContentSecurityPolicyEvent::class, ContentSecurityPolicyListener::class);
    }

    public function boot(IBootContext $context): void
    {
    }
}
