<?php
require_once 'classes/Auth.php';
require_once 'classes/Database.php';

$db = new Database();
$auth = new Auth($db);

$auth->logout();

header('Location: index.php');
exit();
?> 