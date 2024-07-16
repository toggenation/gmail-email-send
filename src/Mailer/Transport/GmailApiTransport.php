<?php
declare(strict_types=1);

namespace GmailEmailSend\Mailer\Transport;

use Cake\Core\Exception\CakeException;
use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message as CakeMessage;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Routing\Router;
use GmailEmailSend\Model\Table\GmailAuthTable;
use GmailEmailSend\Service\GmailAuth;
use Google\Service\Gmail;
use Google\Service\Gmail\Message as GmailMessage;

class GmailApiTransport extends AbstractTransport
{
    use LocatorAwareTrait;

    public array $_content;

    public GmailAuthTable $table;

    public GmailAuth $auth;

    protected string $appName;

    protected array $_defaultConfig = [
        'username' => 'ytoggen@gmail.com',
    ];

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->table = $this->fetchTable('GmailEmailSend.GmailAuth');

        $this->auth = new GmailAuth(Router::getRequest(), $this->table);
    }

    public function createGmailMessage(CakeMessage $message): GmailMessage
    {
        $strMessage = $this->messageAsString($message);

        $gmailMessage = new GmailMessage();

        $gmailMessage->setRaw(base64_encode($strMessage));

        return $gmailMessage;
    }

    public function send(CakeMessage $message): array
    {
        $gmailMessage = $this->createGmailMessage($message);

        $client = $this->auth->getClient(
            $this->getConfig('username')
        );

        $service = new Gmail($client);

        $service->users_messages->send('me', $gmailMessage);

        return [
            'message' => $message->getBodyString(),
            'headers' => $message->getHeadersString(),
        ];
    }

    protected function messageAsString(CakeMessage $message): string
    {
        $this->checkRecipient($message);

        $headers = $message->getHeadersString([
            'from',
            'sender',
            'replyTo',
            'readReceipt',
            'to',
            'cc',
            'subject',
            'returnPath',
        ]);

        $message = $this->_prepareMessage($message);

        $this->_content = ['headers' => $headers, 'message' => $message];

        return $headers . "\r\n\r\n" . $message;
    }

    protected function _prepareMessage(CakeMessage $message): string
    {
        $lines = $message->getBody();

        $messages = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, '.')) {
                $messages[] = '.' . $line;
            } else {
                $messages[] = $line;
            }
        }

        return implode("\r\n", $messages);
    }

    protected function checkRecipient(CakeMessage $message): void
    {
        if (
            $message->getTo() === []
            && $message->getCc() === []
            && $message->getBcc() === []
        ) {
            throw new CakeException(
                'You must specify at least one recipient.'
                    . ' Use one of `setTo`, `setCc` or `setBcc` to define a recipient.'
            );
        }
    }
}
