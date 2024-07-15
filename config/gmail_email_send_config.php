<?php

use Cake\Log\Engine\FileLog;

$config = [
    'GmailEmailSend' => [
        'SENDER' => ['toggen.yt@gmail.com', 'Toggen Youtube'],
        'TO' => ['toggen.yt@gmail.com', 'Toggen Youtube Gmail Account'],
        'emailLog' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'email',
            'scopes' => ['email']
        ],
    ],
];

return $config;
