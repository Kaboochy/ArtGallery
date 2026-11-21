<?php
//checkLogin.php goes at the top of every page that needs someone to be logged in (any role)
//i used the top part of continue.php from the www13 sample for this
//the session and the role checks now live in auth.php (task 10)
require_once 'auth.php';

//if nobody is logged in send them to the login page and stop this page
requireLogin();
?>
