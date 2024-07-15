<?php
declare(strict_types=1);

namespace GmailEmailSend\Command;

use Cake\Chronos\Chronos;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Utility\Text;
use GmailEmailSend\Mailer\Transport\GmailApiTransport;

/**
 * SendEmail command.
 */
class SendEmailCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $mailer = new Mailer([
            'log' => true,
        ]);

        $sender = Configure::read('GmailEmailSend.SENDER');
        $to = Configure::readOrFail('GmailEmailSend.TO');

        $contentId = Text::uuid();

        $mailer->setEmailFormat('html')
            ->setTo(...$to)
            ->setFrom(...$sender)
            ->setAttachments(['cakephp.png' => [
                'file' => WWW_ROOT . 'img/cake-logo.png',
                'mimetype' => 'image/png',
                'contentId' => $contentId,
            ]])
            ->setSubject('Test of the Gmail Send XOAUTH2 ' . Chronos::now('Australia/Melbourne')->toAtomString())
            // config in app_local.php
            // ->setTransport('gmailApi')
            ->setTransport(new GmailApiTransport(['username' => $sender[0]]))
            ->viewBuilder()
            ->setTemplate('GmailEmailSend.gmail_api_template')
            ->setLayout('GmailEmailSend.gmail_api_layout')
            ->setVars([
                'one' => 'One var',
                'two' => 'Two var',
                'contentId' => $contentId,
            ]);

        /**
         * @var array{headers: string, message: string}
         */
        $message = $mailer->deliver();
        // dd($message);
        $list = Text::toList(array_keys($mailer->getMessage()->getTo()));

        $io->out('Message sent to ' . $list);
    }
}
