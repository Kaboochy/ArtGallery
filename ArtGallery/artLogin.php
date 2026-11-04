<?php
//artLogin.php is the login page (part 4)
//the form sends the user id and password to authenticate.php to be checked
//there is no tab bar here because nobody is logged in yet
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Log In</title>
   <link href="semantic/semantic.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <div class="ui secondary segment">
         <h1>Art Gallery Login</h1>
      </div>

      <?php //red message when authenticate.php sends someone back with ?error=1 ?>
      <?php if (isset($_GET['error'])) { ?>
         <div class="ui negative message">Invalid user ID or password.</div>
      <?php } ?>

      <?php //green message after logout.php sends someone back with ?loggedOut=yes ?>
      <?php if (isset($_GET['loggedOut'])) { ?>
         <div class="ui positive message">You have been logged out.</div>
      <?php } ?>

      <?php //the login form, the names userId and password are what authenticate.php reads ?>
      <div class="ui segment">
         <form class="ui form" method="post" action="authenticate.php">
            <div class="field">
               <label>User ID</label>
               <input type="text" name="userId" required>
            </div>
            <div class="field">
               <label>Password</label>
               <input type="password" name="password" required>
            </div>
            <input class="ui primary button" type="submit" value="Log In">
         </form>
      </div>
   </main>
</body>
</html>
