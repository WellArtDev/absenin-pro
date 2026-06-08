<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';

try {
    $auth = Auth::requireSession();
    header('Location: /dashboard/home.php');
    exit;
} catch (\Exception $e) {
    header('Location: /dashboard/login.php');
    exit;
}
