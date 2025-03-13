<?php

declare(strict_types=1);

namespace GmailEmailSend\Database\Type;

use Cake\Core\Configure;
use Cake\Database\Driver;
use Cake\Database\Type\BaseType;
use Cake\Utility\Security;
use PDO;

class EncryptedType extends BaseType
{
    protected string $key;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    public function toPHP(mixed $value, Driver $driver): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->decrypt($value);
    }

    public function marshal(mixed $value): mixed
    {
        if (is_array($value) || $value === null) {
            return $value;
        }

        return $this->decrypt($value);
    }

    public function toDatabase(mixed $value, Driver $driver): mixed
    {
        return $this->encrypt($value);
    }

    public function toStatement(mixed $value, Driver $driver): int
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_STR;
    }

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

        return json_decode(
            Security::decrypt(
                $encrypted,
                Configure::read('Security.CLIENT_SECRET_KEY')
            ),
            // array return type
            associative: true
        );
    }
}
