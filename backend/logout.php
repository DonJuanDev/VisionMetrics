<?php
/**
 * Logout
 */

session_start();
session_destroy();

header('Location: /backend/login.php');
exit;