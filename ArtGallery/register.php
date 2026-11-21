<?php
//new accounts are always users, only an admin can make an admin
require_once 'auth.php';
require_once('dbConfig.php');
require_once('userHelpers.php');

//someone logged in doesnt need a second account
if (isLoggedIn()) {
   header('Location: profile.php');
   exit;
}

//kept so the form can show what was typed if there is an error
$forename = '';
$surname = '';
$username = '';
$email = '';
$errors = array();

if (isset($_POST['register'])) {
   $forename = trim($_POST['forename']);
   $surname = trim($_POST['surname']);
   $username = trim($_POST['username']);
   $email = trim($_POST['email']);
   $password = $_POST['password'];
   $confirm = $_POST['confirm'];

   checkUserFields($forename, $surname, $email, $errors);
   checkUsername($username, $errors);
   checkNewPassword($password, $confirm, $errors);

   //no errors so save the account and log them straight in
   if (count($errors) == 0) {
      try {
         insertUser($forename, $surname, $username, $password, $email, 'user');
      }
      catch (PDOException $e) {
         die( $e->getMessage() );
      }
      session_regenerate_id();
      $_SESSION['forename'] = $forename;
      $_SESSION['surname']  = $surname;
      $_SESSION['username'] = $username;
      $_SESSION['role']     = 'user';
      header('Location: profile.php?welcome=yes');
      exit;
   }
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Register</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Register</h1>
         <p>Make a free account to get your own profile page.</p>
      </div>

      <?php if (count($errors) > 0) { ?>
         <div class="ui negative message">
            <div class="header">The account was not made:</div>
            <ul class="list">
               <?php foreach ($errors as $error) { ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
               <?php } ?>
            </ul>
         </div>
      <?php } ?>

      <div class="ui segment">
         <form class="ui form" method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" data-check-form>
            <div class="two fields">
               <div class="required field">
                  <label>First name</label>
                  <input type="text" name="forename" maxlength="32" required placeholder="for example Frida"
                         value="<?php echo htmlspecialchars($forename); ?>">
               </div>
               <div class="required field">
                  <label>Last name</label>
                  <input type="text" name="surname" maxlength="32" required placeholder="for example Kahlo"
                         value="<?php echo htmlspecialchars($surname); ?>">
               </div>
            </div>
            <div class="required field">
               <label>User ID</label>
               <input type="text" name="username" maxlength="32" required data-check="username" placeholder="for example frida_k"
                      value="<?php echo htmlspecialchars($username); ?>">
               <small>3 to 32 letters, numbers or _ with no spaces.</small>
            </div>
            <div class="field">
               <label>Email (optional)</label>
               <input type="text" name="email" maxlength="100" data-check="email" placeholder="name@example.com"
                      value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="two fields">
               <div class="required field">
                  <label>Password</label>
                  <input type="password" name="password" required data-min-length="6" maxlength="72">
                  <small>At least 6 characters.</small>
               </div>
               <div class="required field">
                  <label>Password again</label>
                  <input type="password" name="confirm" required data-match="password">
               </div>
            </div>
            <input class="ui primary button" type="submit" name="register" value="REGISTER">
            <p class="form-note">Already have an account? <a href="artLogin.php">Log in</a>.</p>
         </form>
      </div>
   </main>
   <script src="js/formCheck.js"></script>
</body>
</html>
