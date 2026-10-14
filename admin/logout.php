<?php
require_once __DIR__ . '/../includes/init.php';
$_SESSION = [];
session_destroy();
redirect('/hostel-meal/index.php');
