<?php
session_start();
require_once 'twig/vendor/autoload.php';

$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$task = '';

if (isset($_GET['task'])) {
      $urlTask = explode("/", $_GET['task']);
      $task = end($urlTask);
}

// nicht angemeldet und nicht im login-prozess
if (!isset($_SESSION['angemeldet']) || !$_SESSION['angemeldet'] || !isset($_SESSION['login_process']) || !$_SESSION['login_process'])  {
      if ($task != 'login' && $task != 'register'){
            header('Location: http://' . $hostname . ($path == '/' ? '' : $path) . '/login');
            exit;
      }
}

