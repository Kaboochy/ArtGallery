<?php
//based on authenticate2.php from www13, changed to use a form instead of the popup

require_once('dbConfig.php');

try
{
  $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (\PDOException $e)
{
  throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if (isset($_POST['userId']) &&
    isset($_POST['password']))
{
  $stmt = $pdo->prepare('SELECT * FROM Users WHERE username=:un');
  $stmt->execute([':un' => trim($_POST['userId'])]);

  //no user with that id
  if (!$stmt->rowCount())
  {
    header('Location: artLogin.php?error=1');
    exit;
  }

  $row = $stmt->fetch();
  $fn  = $row['forename'];
  $sn  = $row['surname'];
  $un  = $row['username'];
  $pw  = $row['password'];
  //tables made before roles have no role column, so default to user
  $role = isset($row['role']) ? $row['role'] : 'user';

  //password_verify checks the typed password against the hash
  if (password_verify($_POST['password'], $pw))
  {
    //new session id on login (from authenticate2-regenerate.php)
    session_start();
    session_regenerate_id();

    $_SESSION['forename'] = $fn;
    $_SESSION['surname']  = $sn;
    $_SESSION['username'] = $un;
    $_SESSION['role']     = $role;

    //admins go to the admin page, everyone else to the gallery
    if ($role == 'admin')
      header('Location: admin.php');
    else
      header('Location: artMain.php');
    exit;
  }
  else
  {
    //wrong password
    header('Location: artLogin.php?error=1');
    exit;
  }
}
else
{
  //opened without the form
  header('Location: artLogin.php');
  exit;
}
?>
