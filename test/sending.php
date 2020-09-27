<?php

use GuzzleHttp\Client;
use Kuvardin\SmsCenter\Api;

require 'vendor/autoload.php';

$client = new Client();
$api = new Api($client, $argv[1], $argv[2]);
$api->sendMessageBySms('test', $argv[3]);
