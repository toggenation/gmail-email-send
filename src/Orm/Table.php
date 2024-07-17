<?php

declare(strict_types=1);

namespace GmailEmailSend\Orm;

use Cake\Database\Schema\TableSchemaInterface;

class Table extends \Cake\ORM\Table
{
    public function getSchema(): TableSchemaInterface
    {
        return parent::getSchema()
            ->setColumnType('token', 'encrypted')
            ->setColumnType('credentials', 'encrypted');
    }
}
