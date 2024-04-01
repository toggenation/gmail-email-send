# GmailEmailSend plugin for CakePHP

This is an alpha repo. It is not ready for use

## Installation

On the Google Cloud Console you need to:

1. Create a project and enable the Gmail Api
2. Create an Oauth consent screen
3. Create some Oauth Credentials. To use the default CakePHP dev server add `http://localhost:8765/gmail/code` as the redirectUrl
4. Download the client_secrets*.json credentials file

Install CakePHP 5.x+

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
```


Run the database migration to create the gmail_auth table

```sh
 bin/cake migrations migrate -p GmailEmailSend
```

Connect to `http://localhost:8765/gmail/get-token`

Enter a gmail username (user123@gmail.com) and upload the `client_secret*.json` you created in Google Cloud Console

Once you upload a valid client_secret.json you should be redirected to Googles Consent screen so you can allow Gmail API access to send email on behalf of your email account. 

The contents of the client_secret.json file will be encrypted and stored in the gmail_auth credentials field

When you have consented and been redirected back to http://locahost:8765/gmail/code the resulting code will be used to obtain a "access_token" and "refresh_token" which will be stored in the gmail_auth table token field

Example of using the mail send ability

```php

        $mailer = new Mailer([
            'log' => true
        ]);

         $mailer->setEmailFormat('html')
            ->setTo('james@toggen.com.au', 'James McDonald')
            ->addTo('jmcd1973@gmail.com', 'James Gmail 73')
            ->setFrom('jmcd1973@gmail.com', 'James 1973 Gmail')
            ->setSubject('Test of the Gmail Send XOAUTH2 ' . Chronos::now('Australia/Melbourne')->toAtomString())
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
```




