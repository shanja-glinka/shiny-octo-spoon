<?php

return [
    '/api/login' => [
        'POST' => 'app\controllers\ApiProfile@login'
    ],
    '/api/profile' => [
        'GET' => 'app\controllers\ApiProfile@info',
        'POST' => 'app\controllers\ApiProfile@create',
        'PUT' => 'app\controllers\ApiProfile@update'
    ],



    '/' => [
        'GET' => 'app\controllers\Main@login',
    ],
    '/login' => [
        'GET' => 'app\controllers\Main@login',
    ],
    '/logout' => [
        'GET' => 'app\controllers\Main@logout',
    ],
    '/signup' => [
        'GET' => 'app\controllers\Main@signup',
    ],
    '/profile' => [
        'GET' => 'app\controllers\Main@profile',
    ],
    '/rick' => [
        'GET' => 'app\controllers\Main@hiMark',
    ],
];
