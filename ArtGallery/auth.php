<?php
//roles: visitor (not logged in), user (registered), admin (staff)

//only start the session once, some pages include this more than one way
if (session_status() == PHP_SESSION_NONE) session_start();

function isLoggedIn() {
   return isset($_SESSION['username']);
}

//accounts from before roles existed have no role, so they count as users
function isAdmin() {
   return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function requireLogin() {
   if (!isLoggedIn()) {
      header('Location: artLogin.php?needLogin=yes');
      exit;
   }
}

function requireAdmin() {
   requireLogin();
   if (!isAdmin()) {
      header('Location: artMain.php?notAllowed=yes');
      exit;
   }
}
?>
