<?php
//checkAdmin.php goes at the top of every page only staff (admins) can use (task 10)
//visitors are sent to the login page and normal users are sent back to the gallery
require_once 'auth.php';

requireAdmin();
?>
