<?php
//logout.php logs the user out and sends them back to the login page
//the Log out tab in nav.php links here

//session_start has to run before the session can be cleared
session_start();

destroy_session_and_data();

//back to the login page, loggedOut makes it show a "you logged out" message
header('Location: artLogin.php?loggedOut=yes');
exit;

//i used this function from continue2.php in the www13 sample
//it empties the session, deletes the session cookie and ends the session
function destroy_session_and_data()
{
   $_SESSION = array();
   $params = session_get_cookie_params();
   setcookie(session_name(), '', time() - 2592000, $params['path']);
   session_destroy();
}
?>
