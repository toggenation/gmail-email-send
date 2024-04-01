<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class AddGmailAuthTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->table('gmail_auth')
            ->addColumn('credentials', 'binary', ['default' => null])
            ->addColumn('email', 'string')
            ->addColumn('token', 'binary', ['default' => null])
            ->addIndex('email', ['unique' => true])
            ->addTimestampsWithTimezone('created', 'modified')
            ->create();
    }
}
