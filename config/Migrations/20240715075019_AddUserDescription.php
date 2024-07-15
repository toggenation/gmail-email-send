<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class AddUserDescription extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('gmail_auth')
            ->addColumn('description', 'string', ['default' => null, 'null' => true])
            ->update();
    }
}
