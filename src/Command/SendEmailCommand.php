<?php
declare(strict_types=1);

namespace GmailEmailSend\Command;

use Cake\Chronos\Chronos;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
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

        $sender = ['jmcd1973@gmail.com', 'James McDonald 1973'];

        $mailer->setEmailFormat('html')
            ->setTo('james@toggen.com.au', 'James McDonald')
            ->addTo('jmcd1973@gmail.com', 'James Gmail 1973')
            ->setFrom(...$sender)
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
