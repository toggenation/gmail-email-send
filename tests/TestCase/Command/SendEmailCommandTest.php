<?php
declare(strict_types=1);

namespace GmailEmailSend\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use GmailEmailSend\Command\SendEmailCommand;

/**
 * GmailEmailSend\Command\SendEmailCommand Test Case
 *
 * @uses \GmailEmailSend\Command\SendEmailCommand
 */
class SendEmailCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Test buildOptionParser method
     *
     * @return void
     * @uses \GmailEmailSend\Command\SendEmailCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test execute method
     *
     * @return void
     * @uses \GmailEmailSend\Command\SendEmailCommand::execute()
     */
    public function testExecute(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
