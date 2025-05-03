<?php

return [
    'db' => [
        'host' => 'localhost',
        'user' => 'your_db_user',
        'pass' => 'your_db_password',
        'name' => 'your_db_name',
    ],
    'security' => [
        'password_hash_algo' => PASSWORD_DEFAULT,
        'password_hash_options' => [
            'cost' => 10,
        ],
    ],
];