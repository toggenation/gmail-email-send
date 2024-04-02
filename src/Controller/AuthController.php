<?php

declare(strict_types=1);

namespace GmailEmailSend\Controller;


use Cake\Core\Exception\CakeException;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use GmailEmailSend\Model\Table\GmailAuthTable;
use GmailEmailSend\Service\GmailAuth;
use GmailEmailSend\Service\Traits\DbFieldEncryptionTrait;
use GmailEmailSend\Service\Traits\ErrorFormatterTrait;
use Google\Client;
use Google\Service\Gmail;
use Symfony\Component\Uid\Ulid;

/**
 * Code Controller
 */
class AuthController extends AppController
{
    use ErrorFormatterTrait;
    use LogTrait;
    // use DbFieldEncryptionTrait;

    public GmailAuthTable $table;

    public function initialize(): void
    {
        parent::initialize();

        $this->table = $this->fetchTable('GmailEmailSend.GmailAuth');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->viewBuilder()->setLayout('GmailEmailSend.default');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $auth = $this->table->find('all');

        $authRecords = $this->paginate($auth);

        $this->set(compact('authRecords'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod('POST');

        $gmailAuth = $this->table->get($id);

        if ($this->table->delete($gmailAuth)) {
            $this->Flash->success(__('Successfully deleted {0}', $gmailAuth->email));
        } else {
            $this->Flash->error(__('Could not delete {0}', $gmailAuth->email));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function view($id = null)
    {
        $entity = $this->table->get($id);

        $client_secret = $entity->credentials;

        $access_token =  $entity->token;

        // dd(json_encode([$decrypted, $token], JSON_PRETTY_PRINT));

        $this->set(compact('client_secret', 'access_token'));
    }

    public function code(GmailAuth $auth)
    {
        // http://127.0.0.1:8080/getToken.php?code=4/0AeaYSHDjt5X-9rj0E_3N59gPfXHha16tcOwtk7WLdKWtj8IESOViYikwqvdtQ2LpS3jG-Q&scope=https://www.googleapis.com/auth/gmail.compose%20https://www.googleapis.com/auth/gmail.addons.current.action.compose%20https://www.googleapis.com/auth/gmail.send
        $params = $this->request->getQueryParams(); // code, state, scope

        /**
         * @var \GmailEmailSend\Model\Entity\GmailAuth $gmailUser
         */
        $gmailUser = $this->table->find()
            ->where(['state' => $params['state']])
            ->firstOrFail();

        $decrypted = $gmailUser->credentials;

        $client = new Client();

        $client->setApplicationName('CakePHP 5 XOAuth2 Test');

        $client->setScopes([
            Gmail::GMAIL_SEND,
        ]);

        $client->setAuthConfig($decrypted);

        $accessToken = $client->fetchAccessTokenWithAuthCode($params['code']);

        $client->setAccessToken($accessToken);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new CakeException(join(', ', $accessToken));
        }

        /**
         * @var \GmailEmailSend\Model\Entity\GmailAuth $gmailUser
         */
        $gmailUser = $this->table->find()
            ->where(['state' => $params['state']])
            ->firstOrFail();

        $gmailUser->token = $client->getAccessToken();

        $this->table->save($gmailUser);

        $this->set(compact('params'));
    }

    public function changeCredentials(GmailAuth $auth, $id = null)
    {
        $entity = $this->table->get($id);

        if ($this->request->is(['POST', 'PUT'])) {
            $data = $this->request->getData();

            /**
             * @var \Psr\Http\Message\UploadedFileInterface $credentials
             */
            $credentials = $data['credentials'];

            unset($data['credentials']);

            $error = $auth->handleUpload($credentials);

            if ($error) {
                $this->Flash->error($error);

                return $this->redirect(['action' => 'changeCredentials', $entity->id]);
            }

            $credentialContents =  $auth->getCredentialsAsJson($credentials);

            $entity = $this->table->patchEntity($entity, $data);

            $entity->credentials =
                $credentialContents;

            if ($this->table->save($entity)) {
                // $this->Flash->success('Saved!');

                $client = new Client();

                $client->setApplicationName('CakePHP 5 XOAuth2 Test');

                $client->setScopes([
                    Gmail::GMAIL_SEND,
                ]);

                $client->setAuthConfig($credentialContents);

                $client->setAccessType('offline');

                $client->setPrompt('select_account consent');

                $client->setState($entity->state);

                $authUrl = $client->createAuthUrl();

                return $this->redirect($authUrl);
            } else {
                $this->Flash->error($this->formatErrors($entity->getErrors()));
            }
        }

        $this->viewBuilder()->setTemplate('edit');

        $this->set(compact('entity'));
    }
    public function getToken(GmailAuth $auth)
    {
        $entity = $this->table->newEmptyEntity();

        if ($this->request->is('POST')) {
            $data = $this->request->getData();

            /**
             * @var \Psr\Http\Message\UploadedFileInterface $credentials
             */
            $credentials = $data['credentials'];

            unset($data['credentials']);

            $error = $auth->handleUpload($credentials);

            if ($error) {
                $this->Flash->error($error);

                return $this->redirect(['action' => 'getToken']);
            }

            $credentialContents =  $auth->getCredentialsAsJson($credentials);

            $entity = $this->table->patchEntity($entity, $data);

            $entity->credentials =
                $credentialContents;

            $entity->state = Ulid::generate();

            if ($this->table->save($entity)) {
                $client = new Client();

                $client->setApplicationName('CakePHP 5 XOAuth2 Test');

                $client->setScopes([
                    Gmail::GMAIL_SEND,
                ]);

                $client->setAuthConfig($credentialContents);

                $client->setAccessType('offline');

                $client->setPrompt('select_account consent');

                $client->setState($entity->state);

                $authUrl = $client->createAuthUrl();

                return $this->redirect($authUrl);
            } else {
                $this->Flash->error($this->formatErrors($entity->getErrors()));
            }
        }

        $this->set(compact('entity'));
    }
}
