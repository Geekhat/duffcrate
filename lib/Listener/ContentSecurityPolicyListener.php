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
        // Allow enrichment-source CDN thumbnails in search results and artwork preview
        $csp->addAllowedImageDomain('https://i.discogs.com');
        $csp->addAllowedImageDomain('https://img.discogs.com');
        $csp->addAllowedImageDomain('https://st.discogs.com');
        $csp->addAllowedImageDomain('https://image.tmdb.org');
        $csp->addAllowedImageDomain('https://media.rawg.io');
        $csp->addAllowedImageDomain('https://comicvine.gamespot.com');
        $csp->addAllowedImageDomain('https://static.comicvine.com');
        $csp->addAllowedImageDomain('https://covers.openlibrary.org');
        $event->addPolicy($csp);
    }
}
