<?php
require_once 'vendor/autoload.php';

$google_client = new Google_Client();
$google_client->setClientId('your_client_id');
$google_client->setClientSecret('your_client_secret');
$google_client->setRedirectUri('http://localhost/callback.php');
$google_client->addScope('email');
$google_client->addScope('profile'); 