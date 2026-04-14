<?php

declare(strict_types=1);

namespace OCA\Crate\Listener;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/** @template-implements IEventListener<AddContentSecurityPolicyEvent> */
class ContentSecurityPolicyListener implements IEventListener
{
    public function handle(Event $event): void
    {
        if (!($event instanceof AddContentSecurityPolicyEvent)) {
            return;
        }

        $csp = new ContentSecurityPolicy();
        // Allow Discogs CDN thumbnails in search results
        $csp->addAllowedImageDomain('https://i.discogs.com');
        $event->addPolicy($csp);
    }
}
