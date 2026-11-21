<?php
//profile.php is the logged in person's own page (task 10)
//they can see their account, change their name, email and the short "about me" text, and change their password
//the user id and role cant be changed here (only an admin can change a role, on admin.php)
require 'checkLogin.php';
require_once('dbConfig.php');
require_once('userHelpers.php');

$me = getUser($_SESSION['username']);

//the account was deleted by an admin while they were logged in
if (!$me) {
   header('Location: logout.php');
   exit;
}

$errors = array();
$passwordErrors = array();
$message = '';

//this runs when SAVE PROFILE is clicked
if (isset($_POST['saveProfile'])) {
   $forename = trim($_POST['forename']);
   $surname = trim($_POST['surname']);
   $email = trim($_POST['email']);
   $bio = trim($_POST['bio']);

   checkUserFields($forename, $surname, $email, $errors);
   if (strlen($bio) > 1000)
      $errors[] = 'The about me text can be at most 1000 characters.';

   if (count($errors) == 0) {
      try {
         $pdo = userDb();
         $statement = $pdo->prepare('update Users set forename=:fn, surname=:sn, email=:email, bio=:bio where username=:un');
         $statement->execute([
            ':fn'    => $forename,
            ':sn'    => $surname,
            ':email' => $email == '' ? null : $email,
            ':bio'   => $bio == '' ? null : $bio,
            ':un'    => $_SESSION['username']
         ]);
         $pdo = null;
      }
      catch (PDOException $e) {
         die( $e->getMessage() );
      }
      $_SESSION['forename'] = $forename;
      $_SESSION['surname'] = $surname;
      header('Location: profile.php?saved=yes');
      exit;
   }

   //put back what they typed so they can fix it
   $me['forename'] = $forename;
   $me['surname'] = $surname;
   $me['email'] = $email;
   $me['bio'] = $bio;
}

//this runs when CHANGE PASSWORD is clicked
if (isset($_POST['changePassword'])) {
   try {
      $pdo = userDb();
      $statement = $pdo->prepare('select password from Users where username=:un');
      $statement->execute([':un' => $_SESSION['username']]);
      $row = $statement->fetch();

      //the old password has to be right first so someone using an unlocked computer cant change it
      if (!$row || !password_verify($_POST['currentPassword'], $row['password']))
         $passwordErrors[] = 'Your current password is not right.';
      checkNewPassword($_POST['newPassword'], $_POST['confirmPassword'], $passwordErrors);

      if (count($passwordErrors) == 0) {
         $statement = $pdo->prepare('update Users set password=:pw where username=:un');
         $statement->execute([
            ':pw' => password_hash($_POST['newPassword'], PASSWORD_DEFAULT),
            ':un' => $_SESSION['username']
         ]);
         $pdo = null;
         header('Location: profile.php?passwordChanged=yes');
         exit;
      }
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - My Profile</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>My Profile</h1>
         <p><?php echo htmlspecialchars($me['forename'] . ' ' . $me['surname']); ?></p>
      </div>

      <?php if (isset($_GET['welcome'])) { ?>
         <div class="ui positive message">Welcome to the gallery! Your account is ready and you are logged in.</div>
      <?php } ?>
      <?php if (isset($_GET['saved'])) { ?>
         <div class="ui positive message">Your profile was saved.</div>
      <?php } ?>
      <?php if (isset($_GET['passwordChanged'])) { ?>
         <div class="ui positive message">Your password was changed.</div>
      <?php } ?>

      <div class="ui segment">
         <div class="ui stackable two column grid">
            <?php //what is saved about the account ?>
            <div class="column">
               <h3>Account</h3>
               <table class="ui definition table">
                  <tbody>
                     <tr><td class="six wide">User ID</td><td><?php echo htmlspecialchars($me['username']); ?></td></tr>
                     <tr><td>Name</td><td><?php echo htmlspecialchars($me['forename'] . ' ' . $me['surname']); ?></td></tr>
                     <tr><td>Email</td><td><?php echo $me['email'] != '' ? htmlspecialchars($me['email']) : '<em>not given</em>'; ?></td></tr>
                     <tr><td>Account type</td><td><?php echo $me['role'] == 'admin' ? 'Admin (staff)' : 'Registered member'; ?></td></tr>
                     <tr><td>Joined</td><td><?php echo $me['dateJoined'] ? date('F j, Y', strtotime($me['dateJoined'])) : ''; ?></td></tr>
                     <tr><td>About me</td><td><?php echo $me['bio'] != '' ? nl2br(htmlspecialchars($me['bio'])) : '<em>nothing yet</em>'; ?></td></tr>
                  </tbody>
               </table>
               <?php if ($me['role'] == 'admin') { ?>
                  <a class="ui primary button" href="admin.php">Go to the admin page</a>
               <?php } ?>
            </div>

            <?php //the forms to change the profile and the password ?>
            <div class="column">
               <h3>Edit profile</h3>
               <?php if (count($errors) > 0) { ?>
                  <div class="ui negative message">
                     <ul class="list">
                        <?php foreach ($errors as $error) { ?><li><?php echo htmlspecialchars($error); ?></li><?php } ?>
                     </ul>
                  </div>
               <?php } ?>
               <form class="ui form" method="post" action="profile.php" data-check-form>
                  <div class="two fields">
                     <div class="required field">
                        <label>First name</label>
                        <input type="text" name="forename" maxlength="32" required value="<?php echo htmlspecialchars($me['forename']); ?>">
                     </div>
                     <div class="required field">
                        <label>Last name</label>
                        <input type="text" name="surname" maxlength="32" required value="<?php echo htmlspecialchars($me['surname']); ?>">
                     </div>
                  </div>
                  <div class="field">
                     <label>Email</label>
                     <input type="text" name="email" maxlength="100" data-check="email" placeholder="name@example.com"
                            value="<?php echo htmlspecialchars((string)$me['email']); ?>">
                  </div>
                  <div class="field">
                     <label>About me</label>
                     <textarea name="bio" rows="4" maxlength="1000" placeholder="for example: I love the Impressionists, especially Monet."><?php echo htmlspecialchars((string)$me['bio']); ?></textarea>
                  </div>
                  <input class="ui primary button" type="submit" name="saveProfile" value="SAVE PROFILE">
               </form>

               <h3>Change password</h3>
               <?php if (count($passwordErrors) > 0) { ?>
                  <div class="ui negative message">
                     <ul class="list">
                        <?php foreach ($passwordErrors as $error) { ?><li><?php echo htmlspecialchars($error); ?></li><?php } ?>
                     </ul>
                  </div>
               <?php } ?>
               <form class="ui form" method="post" action="profile.php" data-check-form>
                  <div class="required field">
                     <label>Current password</label>
                     <input type="password" name="currentPassword" required>
                  </div>
                  <div class="two fields">
                     <div class="required field">
                        <label>New password</label>
                        <input type="password" name="newPassword" required data-min-length="6" maxlength="72">
                     </div>
                     <div class="required field">
                        <label>New password again</label>
                        <input type="password" name="confirmPassword" required data-match="newPassword">
                     </div>
                  </div>
                  <input class="ui button" type="submit" name="changePassword" value="CHANGE PASSWORD">
               </form>
            </div>
         </div>
      </div>
   </main>
   <script src="js/formCheck.js"></script>
</body>
</html>
