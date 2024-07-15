<?php
declare(strict_types=1);

namespace GmailEmailSend\Mailer\Transport;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message as CakeMessage;
use Cake\ORM\Locator\LocatorAwareTrait;
use Exception;
use GmailEmailSend\Model\Table\GmailAuthTable;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message as GmailMessage;

class GmailApiTransport extends AbstractTransport
{
    use LocatorAwareTrait;

    public array $_content;

    public GmailAuthTable $table;

    protected string $applicationName;

    protected array $_defaultConfig = [
        'username' => 'ytoggen@gmail.com',
        'applicationName' => 'Toggen Gmail API Email Send',
    ];

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->applicationName = Configure::read('GmailEmailSend.applicationName')
            ?? $this->getConfig('applicationName');

        $this->table = $this->fetchTable('GmailEmailSend.GmailAuth');
    }

    public function send(CakeMessage $message): array
    {
        $strMessage = $this->messageAsString($message);

        $gmailMessage = new GmailMessage();

        $gmailMessage->setRaw(base64_encode($strMessage));

        $client = $this->getClient();

        $service = new Gmail($client);

        $service->users_messages->send('me', $gmailMessage);

        return [
            'message' => $message->getBodyString(),
            'headers' => $message->getHeadersString(),
        ];
    }

    protected function getTokenStoredInDb(): array
    {
        return $this->getUser()->get('token');
    }

    protected function getUser()
    {
        return $this->table->find()
            ->where(['email' => $this->getConfig('username')])
            ->firstOrFail();
    }

    protected function getCredentials(): array
    {
        return $this->getUser()->get('credentials');
    }

    protected function getClient()
    {
        $client = new Client();

        $client->setApplicationName($this->applicationName);

        $client->setScopes([
            Gmail::GMAIL_SEND,
        ]);

        $credentials = $this->getCredentials();

        $client->setAuthConfig($credentials);

        $client->setAccessType('offline');

        $client->setPrompt('select_account consent');

        $token = $this->getTokenStoredInDb();

        if ($token) {
            $client->setAccessToken($token);
        }

        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.

            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                $user = $this->getUser();

                $user->token = $client->getAccessToken();

                // save the new token
                if ($this->table->save($user) === false) {
                    throw new Exception('Could not save updated token');
                }
            } else {
                throw new CakeException('Could not refresh the access/refresh token non-interactively');
            }
        }

        return $client;
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
