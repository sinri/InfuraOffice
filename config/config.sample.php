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
    ],
    'log' => [
        'dir' => __DIR__ . '/../log',
    ],
];