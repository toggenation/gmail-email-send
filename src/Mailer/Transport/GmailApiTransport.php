<?php

declare(strict_types=1);

namespace GmailEmailSend\Mailer\Transport;

use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Mailer\Message;
use Cake\Mailer\Transport\SmtpTransport;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Security;
use Exception;
use GmailEmailSend\Model\Table\GmailAuthTable;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message as GmailMessage;
use InvalidArgumentException;

class GmailApiTransport extends SmtpTransport
{
    use LocatorAwareTrait;

    public string $gmailUser;

    public GmailAuthTable $table;

    protected array $_defaultConfig = [
        'username' => 'jmcd1973@gmail.com'
    ];

    public function __construct($config = [])
    {

        $this->gmailUser = $this->getConfig('username');

        $this->table = $this->fetchTable('GmailEmailSend.GmailAuth');
    }
    public function send(Message $message): array
    {
        $strMessage = $this->messageAsString($message);

        $gmailMessage = new GmailMessage();

        $gmailMessage->setRaw(base64_encode($strMessage));

        $client = $this->getClient();

        $service = new Gmail($client);

        $results = $service->users_messages->send($this->gmailUser, $gmailMessage);

        return [
            'message' => $message->getBodyString(),
            'headers' => $message->getHeadersString(),
        ];
    }

    protected function getToken(): array
    {
        $user = $this->getUser();

        return $this->decrypt($user->token);
    }

    protected function decrypt($encrypted): array
    {
        $result = json_decode(
            Security::decrypt(
                stream_get_contents($encrypted),
                Configure::read('Security.CLIENT_SECRET_KEY')
            ),
            true
        );

        return $result;
    }

    protected function getUser()
    {
        return $this->table->find()
            ->where(['email' => $this->gmailUser])
            ->firstOrFail();
    }

    protected function getCredentials(): array
    {
        $user = $this->getUser();

        return $this->decrypt($user->credentials);
    }

    protected function getClient()
    {
        $client = new Client();

        $client->setApplicationName('CakePHP 5 XOAuth2 Test');

        $client->setScopes([
            Gmail::GMAIL_SEND,
        ]);

        $credentials = $this->getCredentials();

        $client->setAuthConfig($credentials);

        $client->setAccessType('offline');

        $client->setPrompt('select_account consent');

        $token = $this->getToken();

        if ($token) {
            $client->setAccessToken($token);
        }

        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }

            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }

    protected function messageAsString(Message $message): string
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

    protected function _prepareMessage(Message $message): string
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

    protected function checkRecipient(Message $message): void
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
