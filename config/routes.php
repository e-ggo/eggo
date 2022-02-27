<?php
return [
    //homepage
    //  array(['GET', 'POST'], '/hello/{id:\d+}', 'TestSingleton@test'),
//    array(['GET', 'POST', 'PUT'], '/xxx', function () {
//        echo 'About us';
//    }),
    array(['GET', 'POST', 'PUT'], '/a', [\App\controllers\Index::class, 'index']),
    array(['GET', 'POST'], '/hello/{id:\d+}', 'App\controllers\demo\Test@test'),
    array(['GET', 'POST'], '/', 'App\controllers\Index@index'),
    //create
    array(['GET', 'POST'], '/index', 'App\\controllers\\Index@test'),
    //Api
    array(['GET', 'POST'], '/api', 'App\\controllers\\Api@index'),
    array(['GET', 'POST'], '/api/test', 'App\\controllers\\Api@test'),
];