<?php
declare(strict_types=1);

namespace GmailEmailSend\Controller;

use Cake\Core\Exception\CakeException;
use Cake\Datasource\ModelAwareTrait;
use Cake\Log\LogTrait;
use GmailEmailSend\Service\Traits\DbFieldEncryptionTrait;
use Google\Client;
use Google\Service\Gmail;
use Throwable;

/**
 * Code Controller
 */
class AuthController extends AppController
{
    use ModelAwareTrait;

    public function initialize(): void
    {
        parent::initialize();
    }

    use LogTrait;
    use DbFieldEncryptionTrait;

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $table = $this->fetchTable('GmailEmailSend.GmailAuth');

        $auth = $table->find('all');

        $authRecords = $this->paginate($auth);

        $this->set(compact('authRecords'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod('POST');

        $table = $this->fetchTable('GmailAuth');

        $gmailAuth = $table->get($id);

        if ($table->delete($gmailAuth)) {
            $this->Flash->success(__('Successfully deleted {0}', $gmailAuth->email));
        } else {
            $this->Flash->error(__('Could not delete {0}', $gmailAuth->email));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function view($id = null)
    {
        $table = $this->fetchTable('GmailEmailSend.GmailAuth');

        $entity = $table->get($id);

        $decrypted = $this->decrypt($entity->credentials);

        $token =  $this->decrypt($entity->token);

        dd(json_encode([$decrypted, $token], JSON_PRETTY_PRINT));
    }

    public function code()
    {
        // http://127.0.0.1:8080/getToken.php?code=4/0AeaYSHDjt5X-9rj0E_3N59gPfXHha16tcOwtk7WLdKWtj8IESOViYikwqvdtQ2LpS3jG-Q&scope=https://www.googleapis.com/auth/gmail.compose%20https://www.googleapis.com/auth/gmail.addons.current.action.compose%20https://www.googleapis.com/auth/gmail.send
        $params = $this->request->getQueryParams(); // code, state, scope

        $table = $this->fetchTable('GmailEmailSend.GmailAuth');
        /**
         * @var \GmailEmailSend\Model\Entity\GmailAuth $gmailUser
         */
        $gmailUser = $table->get($params['state']);

        $decrypted = $this->decrypt($gmailUser->credentials);

        $client = new Client();

        $client->setApplicationName('CakePHP 5 XOAuth2 Test');

        $client->setScopes([
            Gmail::GMAIL_SEND,
        ]);

        $client->setAuthConfig($decrypted);

        $this->log(print_r($params, true));

        $accessToken = $client->fetchAccessTokenWithAuthCode($params['code']);

        $this->log(print_r($accessToken, true));

        $client->setAccessToken($accessToken);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new CakeException(join(', ', $accessToken));
        }

        /**
         * @var \GmailEmailSend\Model\Entity\GmailAuth $gmailUser
         */
        $gmailUser = $table->get($params['state']);

        $gmailUser->token = $this->encrypt($client->getAccessToken());

        $table->save($gmailUser);

        $this->set(compact('params'));
    }

    public function getToken()
    {
        if ($this->request->is('POST')) {
            /**
             * @var \Laminas\Diactoros\UploadedFile $credentials
             */
            /**
             * @var \Psr\Http\Message\UploadedFileInterface $credentials
             */
            $credentials = $this->request->getData('credentials');

            if ($credentials->getError() !== 0) {
                $this->Flash->error('You need to upload a client_secret*.json file');

                return $this->redirect(['action' => 'getToken']);
            }

            $credentialContents = json_decode(
                file_get_contents($credentials->getStream()->getMetadata('uri')),
                true
            );

            $table = $this->fetchTable('GmailEmailSend.GmailAuth');

            $validator = $table->getValidator('ClientSecret');

            $errors = $validator->validate($credentialContents);

            if ($errors) {
                $this->Flash->error('Credentials invalid: ' . print_r($errors, true));

                return $this->redirect(['action' => 'getToken']);
            }

            $encrypted = $this->encrypt($credentialContents);

            $this->Flash->success('Credentials Valid');

            $data = $this->request->getData();

            try {
                $entity = $table->find()
                    ->where(['email' => $data['email']])
                    ->firstOrFail();
            } catch (Throwable $e) {
                $entity = $table->newEntity($data);
            }

            $entity->credentials =  $encrypted;

            if ($table->save($entity)) {
                $this->Flash->success('Saved!');

                $client = new Client();

                $client->setApplicationName('CakePHP 5 XOAuth2 Test');

                $client->setScopes([
                    Gmail::GMAIL_SEND,
                ]);

                $client->setAuthConfig($credentialContents);

                $client->setAccessType('offline');

                $client->setPrompt('select_account consent');

                $client->setState($entity->id);

                $authUrl = $client->createAuthUrl();

                return $this->redirect($authUrl);
            } else {
                $this->Flash->error(print_r($entity->getErrors(), true));
            }
        }
    }
}
