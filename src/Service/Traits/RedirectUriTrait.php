<?php

declare(strict_types=1);

namespace GmailEmailSend\Service\Traits;

use Cake\Log\Log;
use Cake\Routing\Router;
use Psr\Log\LogLevel;

trait RedirectUriTrait
{
    protected function getRedirectUri()
    {
        $redirectArray = [
            'controller' => 'Auth',
            'action' => 'code',
        ];

        if (PHP_SAPI === 'cli') {
            $redirectArray = $redirectArray + [
                '_host' => 'localhost',
                'plugin' => 'GmailEmailSend'
            ];
        }
        $redirectUri = Router::url($redirectArray, true);

        Log::write(LogLevel::INFO, "Redirect URI set to $redirectUri");

        return $redirectUri;
    }
}
