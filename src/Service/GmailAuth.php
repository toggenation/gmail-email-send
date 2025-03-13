<?php
declare(strict_types=1);

namespace GmailEmailSend\Service;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\LocatorAwareTrait;
use Exception;
use GmailEmailSend\Model\Entity\GmailAuth as EntityGmailAuth;
use GmailEmailSend\Model\Table\GmailAuthTable;
use GmailEmailSend\Service\Traits\ErrorFormatterTrait;
use GmailEmailSend\Service\Traits\RedirectUriTrait;
use Google\Client;
use Google\Service\Gmail;
use Psr\Http\Message\UploadedFileInterface;

class GmailAuth
{
    use LocatorAwareTrait;
    use ErrorFormatterTrait;
    use RedirectUriTrait;
    use InstanceConfigTrait;

    protected array $_defaultConfig = [
        'applicationName' => 'Toggen Gmail API Auth Service',
    ];

    public function __construct(
        public ?ServerRequest $request = null,
        public ?GmailAuthTable $table = null,
        public array $config = []
    ) {
        $this->setAppName($config);
    }

    protected function setAppName(array $config): void
    {
        $appName = Configure::read('GmailEmailSend.applicationName');

        if (isset($config['applicationName'])) {
            $appName = $config['applicationName'];
        } elseif (is_null($appName)) {
            $appName = $this->getConfig('applicationName');
        }

        $this->setConfig('applicationName', $appName);
    }

    protected function getAppName(): ?string
    {
        return $this->getConfig('applicationName');
    }

    protected function getTokenStoredInDb($user): array
    {
        return $this->getUser($user)->get('token');
    }

    protected function getUser(string $gmailAddress): EntityGmailAuth
    {
        return $this->table->find()
            ->where(['email' => $gmailAddress])
            ->firstOrFail();
    }

    protected function getCredentials($user): array
    {
        return $this->getUser($user)->get('credentials');
    }

    public function getClient($gmailUser): Client
    {
        $credentials = $this->getCredentials($gmailUser);

        $client = $this->setClient($credentials);

        $client->setAccessType('offline');

        $client->setPrompt('none');

        $accessToken = $this->getTokenStoredInDb($gmailUser);

        if (!$accessToken) {
            throw new Exception('Missing Token');
        }

        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            $refreshToken = $client->getRefreshToken();

            if ($refreshToken === null) {
                throw new Exception('Could not get get refresh token');
            }

            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            $user = $this->getUser($gmailUser);

            $user->token = $client->getAccessToken();

            // save the new token
            if ($this->table->save($user) === false) {
                throw new Exception('Could not save updated token');
            }
        }

        return $client;
    }

    public function handleUpload(UploadedFileInterface $credentials): string|false
    {
        if ($credentials->getError() !== 0) {
            return __('You need to upload a client_secret*.json file');
        }

        return $this->getCredentialErrors($credentials);
    }

    public function getJsonCredentialsAsArray(UploadedFileInterface $credentials): ?array
    {
        return json_decode(
            file_get_contents($credentials->getStream()->getMetadata('uri')),
            true
        );
    }

    public function setClient($credentials)
    {
        $client = new Client();

        $client->setApplicationName($this->getAppName());

        $client->setScopes([
            Gmail::GMAIL_SEND,
        ]);

        $client->setAuthConfig($credentials);

        $client->setRedirectUri($this->getRedirectUri());

        return $client;
    }

    public function authUrl(array $credentials, string $state): string
    {
        $client = $this->setClient($credentials);

        $client->setAccessType('offline');

        $client->setPrompt('select_account consent');

        $client->setState($state);

        $client->setLoginHint($this->getUserFromState($state)->get('email'));

        return $client->createAuthUrl();
    }

    public function getUserFromState($state)
    {
        return $this->table->find()
            ->where(['state' => $state])
            ->firstOrFail();
    }

    /**
     * @return string|false Return string of errors or false
     */
    public function getCredentialErrors($credentials): string|false
    {
        $credentialContents = $this->getJsonCredentialsAsArray($credentials);

        $validator = $this->table->getValidator('ClientSecret');

        $errors = $validator->validate($credentialContents);

        $errors = $this->formatErrors($errors);

        $errors = strlen($errors) > 0 ? $errors : false;

        return $errors;
    }

    public function getToken(mixed $credentials, string $code): array
    {
        $client = $this->setClient($credentials);

        $client->setRedirectUri($this->getRedirectUri());

        $accessToken = $client->fetchAccessTokenWithAuthCode($code);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new CakeException(join(', ', $accessToken));
        }

        $client->setAccessToken($accessToken);

        return $client->getAccessToken();
    }
}
