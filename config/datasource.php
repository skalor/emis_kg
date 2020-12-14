<?php
return [
	'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => '',
            'port' => '3306',
            'username' => '',
            'password' => '',
            'database' => 'openemis_core',
            'encoding' => 'utf8',
            'timezone' => '+6:00',//'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
            //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
        ]
    ]
];
