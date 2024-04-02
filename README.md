# GmailEmailSend plugin for CakePHP

This is an alpha repo. It is not ready for use

## Installation

On the Google Cloud Console you need to:

1. Create a project
2. Enable the Gmail Api
2. Create an Oauth consent screen
3. Create some Oauth Credentials. 
4. Add a redirectUrl for the default CakePHP dev server of `http://localhost:8765/gmail/code`
4. Download the client_secrets*.json credentials file

Install CakePHP 5.x+

```sh
composer create-project --prefer-dist cakephp/app:~5.0 gmail-oauth-send
```

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages hosted on Github is:

Add the following to `composer.json`

```json
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/toggenation/gmail-email-send"
        }
    ],
```

```sh
composer require toggenation/gmail-email-send:dev-master

// load the plugin

bin/cake plugin load GmailEmailSend
```

Add the database and encryption config

```php
// config/app_local.php
// sqlite
'Datasources' => [
        'default' => [
            'url' => env('DATABASE_URL', 'sqlite://127.0.0.1/tmp/default.sqlite'),
        ],
 'Security' => [
        'salt' => env('SECURITY_SALT', 'ec3e8fa8b31a8f414fa1a704d209007a9c85406a126fe2910885826f2c6e4d2c'),
        'CLIENT_SECRET_KEY' => '__SALT__'
    ],

```

Run composer run post-install-cmd to replace `__SALT__` with key

```sh
composer run post-install-cmd

```
```php
// app_local.php after:

 'Security' => [
        'salt' => env('SECURITY_SALT', 'ec3e8ff8b31a8f414fa1a704d209007a9c85406a126fe2910885826f2c6e4d2c'),
        'CLIENT_SECRET_KEY' => '0f9b6b5b4fd473gda21ddceeb7d58722576388110f87a3e987791525a15bc41a'
    ],
```

Run the database migration to create the gmail_auth table

```sh
 bin/cake migrations migrate -p GmailEmailSend
```

Start the dev server

```sh
bin/cake server
```

Connect to `http://localhost:8765/gmail/get-token`

Enter a gmail username (user123@gmail.com) and upload the `client_secret*.json` you created in Google Cloud Console

Once you upload a valid client_secret.json you should be redirected to Googles Consent screen so you can allow Gmail API access to send email on behalf of your email account. 

The contents of the client_secret.json file will be encrypted and stored in the gmail_auth `credentials` field

When you have consented and been redirected back to http://locahost:8765/gmail/code the resulting code will be used to obtain a "access_token" and "refresh_token" which will be stored in the gmail_auth table `token` field

Example of using the mail send ability

```sh
bin/cake bake command Send
```

```php
    // paste before class declaration of src/Command/SendCommand.php
    use Cake\Chronos\Chronos;
    use Cake\Mailer\Mailer;
    use GmailEmailSend\Mailer\Transport\GmailApiTransport;
    use Cake\Utility\Text;

    // paste this into the execute method of src/Command/SendCommand.php

        $mailer = new Mailer([
            'log' => true
        ]);

         $mailer->setEmailFormat('html')
            ->setTo('james@toggen.com.au', 'James McDonald')
            ->addTo('jmcd1973@gmail.com', 'James Gmail 73')
            ->setFrom('jmcd1973@gmail.com', 'James 1973 Gmail')
            ->setSubject('Test of the Gmail Send XOAUTH2 ' . Chronos::now('Australia/Melbourne')->toAtomString())
            // use configuration in app/app_local.php (see below)
            // ->setTransport('gmailApi')
            // or 
            ->setTransport(new GmailApiTransport(['username' => 'jmcd1973@gmail.com']))
            ->viewBuilder()
            ->setTemplate('GmailEmailSend.gmail_api_template')
            ->setLayout('GmailEmailSend.gmail_api_layout')
            ->setVars([
                'one' => 'One var',
                'two' => 'Two var',
            ]);


        /**
         * @var array{headers: string, message: string}
         */
        $message = $mailer->deliver();

        $list = Text::toList(array_keys($mailer->getMessage()->getTo()));

        $io->out('Message sent to ' . $list);
```

```sh
bin/cake send

# output
Message sent to james@toggen.com.au and jmcd1973@gmail.com
```



## Configure GmailApiTransport

```php
    // app.php or app_local.php
    use GmailEmailSend\Mailer\Transport\GmailApiTransport;

    return [
        //... 
    'EmailTransport' => [
            'gmailApi' => [
                'className' => GmailApiTransport::class,
                'username' => 'jmcd1973@gmail.com'
            ]
        ],
        //...
    ]
```



