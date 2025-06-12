<?php
session_start();
session_destroy();

header('Location: " . BASE_PATH . "admin/login.php');
