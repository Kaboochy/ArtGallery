<?php
//authenticate.php checks the user id and password from the login form (part 4)
//i used authenticate2.php from the www13 sample and changed it to use a form instead of the popup

//this has the database name, user and password
require_once('dbConfig.php');

//connects to the database
try
{
  $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (\PDOException $e)
{
  throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

//this runs when the login form was filled in
if (isset($_POST['userId']) &&
    isset($_POST['password']))
{
  //looks up the user in the users table
  $stmt = $pdo->prepare('SELECT * FROM Users WHERE username=:un');
  $stmt->execute([':un' => $_POST['userId']]);

  //no user with that id so go back to the login page with an error
  if (!$stmt->rowCount())
  {
    header('Location: artLogin.php?error=1');
    exit;
  }

  //there is a user so get their info
  $row = $stmt->fetch();
  $fn  = $row['forename'];
  $sn  = $row['surname'];
  $un  = $row['username'];
  $pw  = $row['password'];

  //password_verify checks the typed password against the hashed one in the table
  if (password_verify($_POST['password'], $pw))
  {
    //password is right so start a session and remember who is logged in
    //session_regenerate_id gives them a new session id (from authenticate2-regenerate.php)
    session_start();
    session_regenerate_id();

    $_SESSION['forename'] = $fn;
    $_SESSION['surname']  = $sn;
    $_SESSION['username'] = $un;

    //logged in so go to the main page
    header('Location: artMain.php');
    exit;
  }
  else
  {
    //wrong password so go back to the login page with an error
    header('Location: artLogin.php?error=1');
    exit;
  }
}
else
{
  //someone opened this page without using the form so send them to the login page
  header('Location: artLogin.php');
  exit;
}
?>
