<?php

//the session has to be started before it can be cleared
session_start();

destroy_session_and_data();

//loggedOut makes the login page show a message
header('Location: artLogin.php?loggedOut=yes');
exit;

//from continue2.php in www13
//empties the session, deletes its cookie and ends it
function destroy_session_and_data()
{
   $_SESSION = array();
   $params = session_get_cookie_params();
   setcookie(session_name(), '', time() - 2592000, $params['path']);
   session_destroy();
}
?>
