<?php

$db = array_merge(
    require __DIR__ . '/db.php',
    require __DIR__ . '/_db-local.php',
//    require __DIR__ . '/_db-local-laptop.php',
);

return [
    'name' => 'MedQuery',
    'db' => $db,
    'github_repo' => 'https://github.com/Jcarrasco96/medquery',
    'medicaid' => [
        'username' => '',
        'password' => '',
    ],
    'jwt_secret' => '',
];