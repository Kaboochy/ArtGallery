<?php
//userHelpers.php has the user account functions shared by register.php, profile.php and admin.php (task 10)
//the page that includes this must already include dbConfig.php

//opens a connection to the database
function userDb() {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   return $pdo;
}

//gets one user's row (everything but the password), or false if there is no such user
function getUser($username) {
   try {
      $pdo = userDb();
      $statement = $pdo->prepare('select forename, surname, username, role, email, bio, dateJoined from Users where username=:un');
      $statement->execute([':un' => $username]);
      $row = $statement->fetch(PDO::FETCH_ASSOC);
      $pdo = null;
      return $row;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//true if someone already has this user id (not case sensitive, so Bob and bob cant both exist)
function usernameTaken($username) {
   try {
      $pdo = userDb();
      $statement = $pdo->prepare('select count(*) as total from Users where lower(username)=lower(:un)');
      $statement->execute([':un' => $username]);
      $row = $statement->fetch();
      $pdo = null;
      return $row['total'] > 0;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//server side checks for the name and email boxes (task 7b), each problem is added to $errors
function checkUserFields($forename, $surname, $email, &$errors) {
   if ($forename == '')
      $errors[] = 'Please enter your first name.';
   else if (strlen($forename) > 32)
      $errors[] = 'The first name can be at most 32 characters.';

   if ($surname == '')
      $errors[] = 'Please enter your last name.';
   else if (strlen($surname) > 32)
      $errors[] = 'The last name can be at most 32 characters.';

   //email is optional, but if it is filled in it has to look like an email
   if ($email != '' && (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)))
      $errors[] = 'Please enter an email like name@example.com, or leave it blank.';
}

//server side checks for a new user id
function checkUsername($username, &$errors) {
   if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username))
      $errors[] = 'The user ID must be 3 to 32 letters, numbers or _ (no spaces).';
   else if (usernameTaken($username))
      $errors[] = 'The user ID ' . $username . ' is already taken. Please pick another one.';
}

//server side checks for a new password and the box where it is typed again
function checkNewPassword($password, $confirm, &$errors) {
   if (strlen($password) < 6)
      $errors[] = 'The password must be at least 6 characters.';
   else if (strlen($password) > 72)
      $errors[] = 'The password can be at most 72 characters.';
   else if ($password !== $confirm)
      $errors[] = 'The two passwords do not match.';
}

//saves a new account, the password is hashed the same way setupUsers.php does it
function insertUser($forename, $surname, $username, $password, $email, $role = 'user') {
   $pdo = userDb();
   $statement = $pdo->prepare('insert into Users (forename, surname, username, password, role, email)
                               values (:fn, :sn, :un, :pw, :role, :email)');
   $statement->execute([
      ':fn'    => $forename,
      ':sn'    => $surname,
      ':un'    => $username,
      ':pw'    => password_hash($password, PASSWORD_DEFAULT),
      ':role'  => $role,
      ':email' => $email == '' ? null : $email
   ]);
   $pdo = null;
}
?>
