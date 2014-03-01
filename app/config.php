<?php
// Config that gets passed into main Bullet/App instance
return array(
    'env' => getenv('BULLET_ENV') ? getenv('BULLET_ENV') : 'development',
    'template' => array(
        'path' => __DIR__ . '/templates/',
        'path_layouts' => __DIR__ . '/templates/layout/',
        'auto_layout' => 'application'
    ),
    'users' => [
        // 3-letter words we don't want as a users' tagcode
        'tagcode_blacklist' => ['ass', 'azz', 'bum', 'cum', 'die', 'gay', 'fag', 'poo', 'sex', 'tit', 'vag', 'wtf']
    ],
    'sms' => [
        'tag_success_messages' => ['Tagged!', 'Got it!', 'Booyeah!', 'You Rock!']
    ]
);
