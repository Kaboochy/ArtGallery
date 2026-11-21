<?php
//auth.php is included at the top of every page (task 10, user management)
//it starts the session and has small functions that tell the page who is looking at it
//there are three kinds of people:
//   visitor - not logged in, can look around the gallery (artworks, artists, galleries, genres)
//   user    - registered and logged in, can also see and change their own profile
//   admin   - staff, can also add, edit and delete things and gets the admin page

//only start the session once, some pages include this file more than one way
if (session_status() == PHP_SESSION_NONE) session_start();

//true when someone is logged in (a user or an admin)
function isLoggedIn() {
   return isset($_SESSION['username']);
}

//true only when an admin is logged in
//people who logged in before roles existed have no role saved, so they count as normal users
function isAdmin() {
   return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

//sends visitors to the login page, used by pages only logged in people can open
function requireLogin() {
   if (!isLoggedIn()) {
      header('Location: artLogin.php?needLogin=yes');
      exit;
   }
}

//sends everyone who is not an admin away, used by the add, edit, delete and admin pages
function requireAdmin() {
   requireLogin();
   if (!isAdmin()) {
      header('Location: artMain.php?notAllowed=yes');
      exit;
   }
}
?>
