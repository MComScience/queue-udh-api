<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('DB_DSN'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'tablePrefix' => getenv('DB_TABLE_PREFIX'),
    'charset' => getenv('DB_CHARSET', 'utf8'), 
    /* 'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=queue-udoncity;port=3307',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8', */

    // Schema cache options (for production environment)
    /*'enableSchemaCache' => YII_ENV_PROD,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',*/
];
