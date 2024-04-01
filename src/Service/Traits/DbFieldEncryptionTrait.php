<?php
declare(strict_types=1);

namespace GmailEmailSend\Service\Traits;

use Cake\Core\Configure;
use Cake\Utility\Security;

trait DbFieldEncryptionTrait
{
    public function encrypt($unencrypted)
    {
        return Security::encrypt(
            json_encode($unencrypted),
            Configure::read('Security.CLIENT_SECRET_KEY')
        );
    }

    public function decrypt($encrypted)
    {
        return json_decode(
            Security::decrypt(
                stream_get_contents($encrypted),
                Configure::read('Security.CLIENT_SECRET_KEY')
            ),
            true
        );
    }
}
