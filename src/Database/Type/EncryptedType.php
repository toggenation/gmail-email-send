<?php
declare(strict_types=1);

// in src/Database/Type/JsonType.php


namespace GmailEmailSend\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type\BaseType;
use GmailEmailSend\Service\Traits\DbFieldEncryptionTrait;
use PDO;

class EncryptedType extends BaseType
{
    use DbFieldEncryptionTrait;

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
}
