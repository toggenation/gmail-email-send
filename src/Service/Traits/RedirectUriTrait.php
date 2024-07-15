<?php
declare(strict_types=1);

namespace GmailEmailSend\Service\Traits;

use Cake\Routing\Router;

trait RedirectUriTrait
{
    protected function getRedirectUri()
    {
        return Router::url([
            'controller' => 'Auth',
            'action' => 'code',
        ], true);
    }
}
