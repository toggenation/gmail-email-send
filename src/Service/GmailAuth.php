<?php
declare(strict_types=1);

namespace GmailEmailSend\Service;

use Cake\Http\ServerRequest;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Routing\Router;
use GmailEmailSend\Model\Table\GmailAuthTable;
use GmailEmailSend\Service\Traits\ErrorFormatterTrait;
use Google\Client;
use Google\Service\Gmail;
use Psr\Http\Message\UploadedFileInterface;

class GmailAuth
{
    use LocatorAwareTrait;
    use ErrorFormatterTrait;

    // public GmailAuthTable $table;

    public function __construct(
        public ServerRequest $request,
        public GmailAuthTable $table
    ) {
        // $this->request->getFlash()->success("Yeah boy");

        // $this->table = $this->fetchTable('GmailEmailSend.GmailAuth');
    }

    public function handleUpload($credentials): string|false
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

    public function authUrl(array $credentials, string $state): string
    {
        $client = new Client();

        $client->setApplicationName('CakePHP 5 XOAuth2 Test');

        $client->setScopes([
            Gmail::GMAIL_SEND,
        ]);

        $client->setAuthConfig($credentials);

        $client->setAccessType('offline');

        $client->setPrompt('select_account consent');

        $client->setState($state);

        $redirectUri = Router::url([
            'controller' => 'Auth',
            'action' => 'code',
        ], true);

        $client->setRedirectUri($redirectUri);

        $client->setLoginHint($this->getUser($state)->get('email'));

        return $client->createAuthUrl();
    }

    public function getUser($state)
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
}
