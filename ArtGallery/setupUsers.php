<?php
//setupUsers.php makes the users table and adds the users (part 4)
//i used setupusers.php from the www13 sample for this
//open it once in the browser, a blank page means it worked

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

//makes the table only if it isnt there yet so running this again never deletes accounts
$query = "CREATE TABLE IF NOT EXISTS Users (
  forename VARCHAR(32) NOT NULL,
  surname  VARCHAR(32) NOT NULL,
  username VARCHAR(32) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
)";

$result = $pdo->query($query);

//my own account
$forename = 'Ryan';
$surname  = 'ONeil';
$username = 'ryano';
$password = 'ryano';
$hash     = password_hash($password, PASSWORD_DEFAULT);

add_user($pdo, $forename, $surname, $username, $hash);

//the 4 users task 4C part 4 asks for
//first1 / last1 / user1 / pass1 through first4 / last4 / user4 / pass4
for ($n = 1; $n <= 4; $n++)
{
  $forename = "first$n";
  $surname  = "last$n";
  $username = "user$n";
  $password = "pass$n";
  $hash     = password_hash($password, PASSWORD_DEFAULT);

  add_user($pdo, $forename, $surname, $username, $hash);
}

//adds one user to the table
//password_hash above scrambles the password so the real one is never saved
function add_user($pdo, $fn, $sn, $un, $pw)
{
  //IGNORE skips a user who is already there instead of failing
  $stmt = $pdo->prepare('INSERT IGNORE INTO Users VALUES(:fn,:sn,:un,:pw)');
  $stmt->execute([
    ':fn' => $fn,
    ':sn' => $sn,
    ':un' => $un,
    ':pw' => $pw
  ]);
}
?>
