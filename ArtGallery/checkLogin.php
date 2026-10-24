<?php
//checkLogin.php goes at the top of every page that needs a login
//i used the top part of continue.php from the www13 sample for this

//session_start lets this page read what authenticate.php saved
session_start();

//if nobody is logged in send them to the login page and stop this page
if (!isset($_SESSION['username']))
{
   header('Location: artLogin.php');
   exit;
}
?>
