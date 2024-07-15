<?php
declare(strict_types=1);

namespace GmailEmailSend\Controller;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use GmailEmailSend\Mailer\TestMailer;
use GmailEmailSend\Model\Table\GmailAuthTable;
use GmailEmailSend\Service\GmailAuth;
use GmailEmailSend\Service\Traits\ErrorFormatterTrait;
use Google\Client;
use Google\Service\Gmail;

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
        // http://localhost:8765/gmail/code?state=c8b7c214-49aa-4cd2-b5bd-0c3e32f25613&code=4/0ATx3LY60P7k5kV54zhnwR_gxVcesOIWI5oVeL2G3A19kliur_NN-HFCMeWvAlBi27j-U9g&scope=https://www.googleapis.com/auth/gmail.send
        // params are
        // state c8b7c214-49aa-4cd2-b5bd-0c3e32f25613
        // code 4/0ATx3LY60P7k5kV54zhnwR_gxVcesOIWI5oVeL2G3A19kliur_NN-HFCMeWvAlBi27j-U9g
        // scope https://www.googleapis.com/auth/gmail.send

        $params = $this->request->getQueryParams(); // code, state, scope

        /**
         * @var \GmailEmailSend\Model\Entity\GmailAuth $gmailUser
         */
        $gmailUser = $this->table->find()
            ->where(['state' => $params['state']])
            ->firstOrFail();

        $client = new Client();

        $client->setApplicationName(Configure::read('GmailEmailSend.applicationName'));

        $client->setScopes([
            Gmail::GMAIL_SEND,
        ]);

        $client->setAuthConfig($gmailUser->credentials);

        $accessToken = $client->fetchAccessTokenWithAuthCode($params['code']);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new CakeException(join(', ', $accessToken));
        }

        $client->setAccessToken($accessToken);

        $gmailUser->token = $client->getAccessToken();

        if ($this->table->save($gmailUser)) {
            $this->Flash->success('Gmail API auth token saved!');
        }

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

            $credentialContents =  $auth->getJsonCredentialsAsArray($credentials);

            $data['credentials'] =  $credentialContents;

            $entity = $this->table->patchEntity($entity, $data);

            if ($this->table->save($entity)) {
                $this->Flash->success('Credentials updated!');
                $authUrl = $auth->authUrl($credentialContents, $entity->state);

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

            $notNewUser = $this->table->find()
                ->where(['email' => $data['email']])
                ->first();

            if ($notNewUser) {
                $this->Flash->error('User already exists. Redirecting to change credentials');

                return $this->redirect(['action' => 'changeCredentials', $notNewUser->id]);
            }

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

            $credentialContents =  $auth->getJsonCredentialsAsArray($credentials);

            $data['credentials'] = $credentialContents;

            $data['state'] = Text::uuid();

            $entity = $this->table->patchEntity($entity, $data);

            if ($this->table->save($entity)) {
                $authUrl = $auth->authUrl($credentialContents, $entity->state);

                return $this->redirect($authUrl);
            } else {
                $this->Flash->error($this->formatErrors($entity->getErrors()));
            }
        }

        $this->set(compact('entity'));
    }

    public function test($id = null)
    {
        if ($this->getRequest()->is('POST')) {
            $data = $this->getRequest()->getData();

            $validator = new Validator();

            $validator->email('to');

            if ($validator->validate($data)) {
                $this->Flash->error('Invalid email please try again');

                return $this->redirect(['action' => 'test', $id]);
            }

            $to = $data['to'];

            $mailer = new TestMailer([
                'log' => true,
            ]);

            $from = $this->table->find()
                ->where(['id' => $id])
                ->firstOrFail();

            $mailer->send('sendTest', ['to' => $to, 'from' => [$from->email, $from->description]]);

            $this->Flash->success(__('Message sent to {0} from {1}', $to, $from->email));

            return $this->redirect(['action' => 'index']);
        }
    }
}
