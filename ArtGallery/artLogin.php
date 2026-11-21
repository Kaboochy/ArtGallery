<?php
//artLogin.php is the login page (part 4, task 10)
//the form sends the user id and password to authenticate.php to be checked
//visitors dont need to log in to look around, so there are links to the gallery and to register
require_once 'auth.php';

//someone already logged in doesnt need this page
if (isLoggedIn()) {
   header('Location: ' . (isAdmin() ? 'admin.php' : 'artMain.php'));
   exit;
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Log In</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Art Gallery Login</h1>
         <p>Staff and registered members log in here. Visitors can <a href="artMain.php">browse the gallery</a> without an account.</p>
      </div>

      <?php //red message when authenticate.php sends someone back with ?error=1 ?>
      <?php if (isset($_GET['error'])) { ?>
         <div class="ui negative message">Invalid user ID or password.</div>
      <?php } ?>

      <?php //a page that needs a login sent the visitor here ?>
      <?php if (isset($_GET['needLogin'])) { ?>
         <div class="ui warning message">Please log in to open that page.</div>
      <?php } ?>

      <?php //green message after logout.php sends someone back with ?loggedOut=yes ?>
      <?php if (isset($_GET['loggedOut'])) { ?>
         <div class="ui positive message">You have been logged out.</div>
      <?php } ?>

      <?php //the login form, the names userId and password are what authenticate.php reads ?>
      <div class="ui segment">
         <form class="ui form" method="post" action="authenticate.php" data-check-form>
            <div class="field">
               <label>User ID</label>
               <input type="text" name="userId" placeholder="for example user1" required>
            </div>
            <div class="field">
               <label>Password</label>
               <input type="password" name="password" required>
            </div>
            <input class="ui primary button" type="submit" value="Log In">
            <p class="form-note">No account yet? <a href="register.php">Register here</a>.</p>
         </form>
      </div>
   </main>
   <script src="js/formCheck.js"></script>
</body>
</html>
