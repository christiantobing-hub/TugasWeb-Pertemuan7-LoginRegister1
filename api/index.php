<?php
require_once __DIR__ . '/functions.php';

if (isLoggedIn()) {
    redirectTo('dashboard.php');
} else {
    redirectTo('login.php');
}
