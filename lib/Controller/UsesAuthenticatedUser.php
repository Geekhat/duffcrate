<?php

declare(strict_types=1);

namespace OCA\Crate\Controller;

/**
 * Shared auth helper for OCS controllers.
 *
 * Requires the using class to have an IUserSession property named $userSession.
 */
trait UsesAuthenticatedUser
{
    protected function userId(): string
    {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \OCP\AppFramework\OCS\OCSForbiddenException('Not authenticated');
        }
        return $user->getUID();
    }
}
