<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 15:47
 */

$config = [
    'daemon' => [
        'address' => '127.0.0.1',
        'port' => '12345',
        'ssh_key_file' => '~/.ssh/id_rsa',
        'ssh_public_file' => '~/.ssh/id_rsa.pub',
        'ssh_pass_phrase' => null,
        'max_workers' => 2,// zero for unlimited
    ],
    'log' => [
        'dir' => __DIR__ . '/../log',
    ],
    'jsshagent' => [
        'proxy' => 'http://127.0.0.1:9999/'
    ]
];