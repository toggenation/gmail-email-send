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
        $redirectUri = Router::url([
            'controller' => 'Auth',
            'action' => 'code',
        ], true);

        Log::write(LogLevel::INFO, "Redirect URI set to $redirectUri");

        return $redirectUri;
    }
}
