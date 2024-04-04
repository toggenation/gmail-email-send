<?php
declare(strict_types=1);

namespace GmailEmailSend\Service;

use Cake\Http\ServerRequest;
use Cake\ORM\Locator\LocatorAwareTrait;
use GmailEmailSend\Model\Table\GmailAuthTable;
use GmailEmailSend\Service\Traits\ErrorFormatterTrait;
use Psr\Http\Message\UploadedFileInterface;

class GmailAuth
{
    use LocatorAwareTrait;
    use ErrorFormatterTrait;

    public GmailAuthTable $table;

    public function __construct(public ServerRequest $request)
    {
        // $this->request->getFlash()->success("Yeah boy");

        $this->table = $this->fetchTable('GmailEmailSend.GmailAuth');
    }

    public function handleUpload($credentials): string|false
    {
        if ($credentials->getError() !== 0) {
            return __('You need to upload a client_secret*.json file');
        }

        return $this->validateUpload($credentials);
    }

    public function getCredentialsAsJson(UploadedFileInterface $credentials)
    {
        return json_decode(
            file_get_contents($credentials->getStream()->getMetadata('uri')),
            true
        );
    }

    public function validateUpload($credentials): string|false
    {
        $credentialContents = $this->getCredentialsAsJson($credentials);

        $validator = $this->table->getValidator('ClientSecret');

        $errors = $validator->validate($credentialContents);

        $errors = $this->formatErrors($errors);

        $errors = strlen($errors) > 0 ? $errors : false;

        return $errors;
    }
}
