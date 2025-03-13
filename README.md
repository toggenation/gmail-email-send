# GmailEmailSend plugin for CakePHP

This is a CakePHP Plugin that allows sending email via the Google API (Not SMTP)

This is an alpha repo.

## Installation

On the Google Cloud Console you need to:

1. Login to the [Google Cloud Console](https://console.cloud.google.com) (in this example I'm using toggen.yt@gmail.com)
2. Create a New project
4. Project Name: Gmail Email Send Project
2. Got APIs & Services => Enable APIs & Services => Enable APIsS AND SERVICES
4. Search for GMail API click on it and select the Enable button
2. Select OAuth consent Screen
5. Create an Oauth consent screen:\
        External User Type (Internal isn't available for non-paid accounts)\
        **App Name:** Toggenation Email Send\
        **User support email:** toggen.yt@gmail.com\
        **Developer contact information:** toggen.yt@gmail.com\
        **Add Or Remove Scopes:** gmail.compose (this will appear under Your Restricted Scopes)\
        **Add a Test User:** toggen.yt@gmail.com
3. Create some Oauth Credentials:\
        **Credential Type:** Oauth Client ID\
        **Application Type:** Web application\
        **Name:** leave default or change if you want\
        **Authorized redirect URIs:** `http://localhost:8765/gmail/code`\
            (point to you CakePHP dev env and add a domain .e.g https://example.com/gmail/code if you want to use it for real)
4. Download the `client_secrets*.json` credentials file

Install CakePHP 5.x+

```sh
composer create-project --prefer-dist cakephp/app:~5.0 gmail-test
```

Add Gmail PHP
Add an `extra` key to `$project_root/composer.json`

```json
{
 "extra": {
        "google/apiclient-services": [
            "Gmail"
        ]
    },
    // ... rest of composer.json content

```

Install `google/apiclient`

```sh
composer require  "google/apiclient"
```

Install this plugin
```sh
cd $project_root/plugins

git clone https://github.com/toggenation/gmail-email-send.git GmailEmailSend

```

Load the plugin
```sh

bin/cake plugin load GmailEmailSend
```

Add a database connection and the encryption config

```php
// config/app_local.php
// sqlite
'Datasources' => [
        'default' => [
            'url' => env('DATABASE_URL', 'sqlite://127.0.0.1/tmp/default.sqlite'),
        ],
 'Security' => [
        'salt' => env('SECURITY_SALT', 'ec3e8fa8b3fa8g414fa1a704d209007a9c85406a126fe2910885826f2c6e4d2c'),
        // add this CLIENT_SECRET_KEY with a __SALT___ template
        // this will be the encryption / decryption key for any encrypted DB fields
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
        'salt' => env('SECURITY_SALT', 'ec3e8fa8b3fa8g414fa1a704d209007a9c85406a126fe2910885826f2c6e4d2c'),
        'CLIENT_SECRET_KEY' => '0f9b6b5b4fd473gda21ddceeb7d58722576388110f87a3e987791525a15bc41a'
    ],
```

Run the database migration to create the gmail_auth table

```sh
 bin/cake migrations migrate -p GmailEmailSend
```
Make sure you dumpautoload

```sh
composer dumpautoload
```

Start the dev server

```sh
bin/cake server
```

Connect to `http://localhost:8765/gmail/get-token`

Enter a gmail username (toggen.yt@gmail.com) and upload the `client_secret*.json` you created in Google Cloud Console

Once you upload a valid client_secret.json you should be redirected to Googles Consent screen so you can allow Gmail API access to send email on behalf of your email account. 

The contents of the client_secret.json file will be encrypted and stored in the gmail_auth `credentials` field

When you have consented and been redirected back to http://localhost:8765/gmail/code the resulting code will be used to obtain a "access_token" and "refresh_token" which will be stored in the gmail_auth table `token` field

Example of using the mail send ability

```sh
bin/cake bake command SendEmail
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
            ->setTo('james@example.com', 'James McDonald')
            ->addTo('jm1289899@gmail.com', 'James Gmail 73')
            ->setFrom('toggen.yt@gmail.com', 'Youtube Toggen Gmail')
            ->setSubject('Test of the Gmail Send XOAUTH2 ' . Chronos::now('Australia/Melbourne')->toAtomString())
            // use configuration in app/app_local.php (see below)
            // ->setTransport('gmailApi')
            // or 
            ->setTransport(new GmailApiTransport(['username' => 'jm1289899@gmail.com']))
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
bin/cake send_email

# output
Message sent to james@example.com and jm1289899@gmail.com
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
                'username' => 'jm1289899@gmail.com'
            ]
        ],
        //...
    ]
```

## Logging Emails

```php

 // configure Mailer in email sending code
   $mailer = new Mailer([
            'log' => true
        ]);

 // config/app_local.php

 'Log' => [
        //add this
        'email' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'levels' => [],
            'scopes' => ['email'],
            'file' => 'email',
        ],

// logs/email.log for headers and email body content

```

### Install this plugin using composer (Not recommended as you will probably want to tweak the code)

Install this plugin into your CakePHP application using [composer](https://getcomposer.org).

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

// then load the plugin
```

# References 

## Sending via Gmail API via PHP

https://github.com/googleworkspace/php-samples/blob/main/gmail/quickstart/quickstart.php

## Create Custom CakePHP Email Transport

https://book.cakephp.org/5/en/core-libraries/email.html#creating-custom-transports


## Adding Custom Database Type

https://book.cakephp.org/5/en/orm/database-basics.html#adding-custom-types


## Loading Custom Configuration

https://book.cakephp.org/5/en/development/configuration.html#loading-additional-configuration-files

## Script

Database config in app_local.php

Create Plugin

bin/cake bake plugin GmailAPI

Create Migration for gmail_auth Table
bin/cake bake migration -p GmailAPI GmailAuth

bin/cake migrations status -p GmailAPI

bin/cake migrations migrate -p GmailAPI

Create Controller 
bin/cake bake controller -p GmailAPI GmailAuth

Bake Templates
bin/cake bake template -p GmailAPI GmailAuth

Forget the above and just bake all
bin/cake bake all -p GmailAPI GmailAuth

# Auth Urls
