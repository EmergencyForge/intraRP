<?php
session_start();
session_destroy();

require __DIR__ . '/../assets/config/config.php';
require __DIR__ . '/../assets/config/database.php';

header('Location: ' . BASE_PATH . 'admin/login.php');
