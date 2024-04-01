<?php
declare(strict_types=1);

namespace GmailEmailSend\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GmailAuthFixture
 */
class GmailAuthFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'gmail_auth';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'credentials' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'token' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
