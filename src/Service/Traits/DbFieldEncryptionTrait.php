<?php

declare(strict_types=1);

namespace GmailEmailSend\Service\Traits;

use Cake\Core\Configure;
use Cake\Utility\Security;

trait DbFieldEncryptionTrait
{
    public function encrypt($unencrypted): string
    {
        return Security::encrypt(
            json_encode($unencrypted),
            Configure::read('Security.CLIENT_SECRET_KEY')
        );
    }

    public function decrypt($encrypted): ?array
    {
        if (is_null($encrypted)) {
            return null;
        }

        $decrypted =  Security::decrypt(
            $encrypted,
            Configure::read('Security.CLIENT_SECRET_KEY')
        );

        $result = json_decode(
            $decrypted,
            // array return type
            associative: true,
            flags: JSON_OBJECT_AS_ARRAY | JSON_INVALID_UTF8_IGNORE
        );
        if (is_string($result)) {
            dd([$result, $encrypted]);
        }
        return $result;
    }
}
