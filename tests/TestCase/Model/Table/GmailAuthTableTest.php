<?php
declare(strict_types=1);

namespace GmailEmailSend\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use GmailEmailSend\Model\Table\GmailAuthTable;

/**
 * GmailEmailSend\Model\Table\GmailAuthTable Test Case
 */
class GmailAuthTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \GmailEmailSend\Model\Table\GmailAuthTable
     */
    protected $GmailAuth;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.GmailEmailSend.GmailAuth',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('GmailAuth') ? [] : ['className' => GmailAuthTable::class];
        $this->GmailAuth = $this->getTableLocator()->get('GmailAuth', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->GmailAuth);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \GmailEmailSend\Model\Table\GmailAuthTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
