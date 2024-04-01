<?php

declare(strict_types=1);

namespace GmailEmailSend\Controller;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Log\LogTrait;
use Cake\Utility\Security;
use Exception;
use Google\Client;
use Google\Service\Gmail;

/**
 * Code Controller
 */
class AuthController extends AppController
{
    use LogTrait;
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {

        // http://127.0.0.1:8080/getToken.php?code=4/0AeaYSHDjt5X-9rj0E_3N59gPfXHha16tcOwtk7WLdKWtj8IESOViYikwqvdtQ2LpS3jG-Q&scope=https://www.googleapis.com/auth/gmail.compose%20https://www.googleapis.com/auth/gmail.addons.current.action.compose%20https://www.googleapis.com/auth/gmail.send
        $params = $this->request->getQueryParams() + [
            'code' => '- not available -',
            'scope' => '- not available -'
        ];

        $this->set(compact('params'));
    }

    public function view()
    {
        $table = $this->fetchTable('GmailEmailSend.GmailAuth');
        $entity = $table->get(1);
        // $stream = stream_get_contents($entity->credentials);
        // $encrypted = Security::encrypt(json_encode(['hi' => 'james']), Configure::read('Security.CLIENT_SECRET_KEY'));
        // dd(Security::decrypt($encrypted, Configure::read('Security.CLIENT_SECRET_KEY')));
        // dd(json_decode(Security::decrypt($stream, Configure::read('Security.CLIENT_SECRET_KEY')), true));

        // $entity->credentials = $encrypted;

        // $table->save($entity);

        // $entity = $table->get(1);

        $decrypted = json_decode(Security::decrypt(stream_get_contents($entity->credentials), Configure::read('Security.CLIENT_SECRET_KEY')), true);

        $token = json_decode(Security::decrypt(stream_get_contents($entity->token), Configure::read('Security.CLIENT_SECRET_KEY')), true);

        dd([$decrypted, $token]);
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

        $decrypted = json_decode(
            Security::decrypt(
                stream_get_contents($gmailUser->credentials),
                Configure::read('Security.CLIENT_SECRET_KEY')
            ),
            true
        );

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

        $gmailUser->token = Security::encrypt(
            json_encode($client->getAccessToken()),
            Configure::read('Security.CLIENT_SECRET_KEY')
        );

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

                $this->Flash->error("You need to upload a client_secret*.json file");

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

            $encrypted = Security::encrypt(json_encode($credentialContents), Configure::read('Security.CLIENT_SECRET_KEY'));

            $this->Flash->success('Credentials Valid');

            $data = $this->request->getData();

            try {
                $entity = $table->find()
                    ->where(['email' => $data['email']])
                    ->firstOrFail();
            } catch (\Throwable $e) {
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
