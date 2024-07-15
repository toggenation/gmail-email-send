<?php

declare(strict_types=1);

namespace GmailEmailSend\Mailer;

use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Utility\Text;

/**
 * Test mailer.
 */
class TestMailer extends Mailer
{
    /**
     * Mailer's name.
     *
     * @var string
     */
    public static string $name = 'Test';

    public function __construct($config)
    {
        parent::__construct($config);
    }
    public function sendTest($to)
    {
        $sender = Configure::read('GmailEmailSend.SENDER');
       
        $contentId = Text::uuid();

        $attachment =  WWW_ROOT . 'img/cake-logo.png';

        $this->setEmailFormat('html')
            ->setTo($to)
            ->setFrom(...$sender)
            ->setSubject('Test of the Gmail Send XOAUTH2 ' . Chronos::now('Australia/Melbourne')->toAtomString())
            // config in app_local.php
            ->setTransport('gmailApi')
            ->setAttachments([
                'screenshot.png' => [
                    'file' => $attachment,
                    'mimetype' => mime_content_type($attachment),
                    'contentId' => $contentId
                ]
            ])
            // ->setTransport(new GmailApiTransport(['username' => $sender[0]]))
            ->viewBuilder()
            ->setTemplate('GmailEmailSend.gmail_api_template')
            ->setLayout('GmailEmailSend.gmail_api_layout')
        
            ->setVars([
                'one' => 'One var',
                'two' => 'Two var',
                'contentId' => $contentId
            ]);

        /**
         * @var array{headers: string, message: string}
         */
        $message = $this->deliver();

        return $message;
    }
}
