<?php
//setupUsers.php makes the users table and adds the starting accounts (part 4, task 10)
//i used setupusers.php from the www13 sample for this
//open it once in the browser, it is safe to open again (it never deletes accounts)

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
//role is 'admin' for staff or 'user' for people who registered themselves (task 10)
$query = "CREATE TABLE IF NOT EXISTS Users (
  forename   VARCHAR(32)  NOT NULL,
  surname    VARCHAR(32)  NOT NULL,
  username   VARCHAR(32)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       VARCHAR(10)  NOT NULL DEFAULT 'user',
  email      VARCHAR(100),
  bio        TEXT,
  dateJoined DATETIME DEFAULT CURRENT_TIMESTAMP
)";

$result = $pdo->query($query);

//a users table made by the old version of this file only has the first 4 columns
//this adds the new ones without touching the accounts already in it
$newColumns = array(
  'role'       => "VARCHAR(10) NOT NULL DEFAULT 'user'",
  'email'      => 'VARCHAR(100)',
  'bio'        => 'TEXT',
  'dateJoined' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
);
foreach ($newColumns as $column => $type)
{
  $check = $pdo->query("SHOW COLUMNS FROM Users LIKE '$column'");
  if (!$check->rowCount())
    $pdo->query("ALTER TABLE Users ADD COLUMN $column $type");
}

//my own account, staff so it is an admin
add_user($pdo, 'Ryan', 'ONeil', 'ryano', 'ryano', 'admin');

//the 4 users task 4C part 4 asks for, they were the staff accounts so they stay admins
//first1 / last1 / user1 / pass1 through first4 / last4 / user4 / pass4
for ($n = 1; $n <= 4; $n++)
{
  add_user($pdo, "first$n", "last$n", "user$n", "pass$n", 'admin');
}

//a registered client (not staff) for showing what a normal user can and cant do
add_user($pdo, 'Claire', 'Client', 'client1', 'client1', 'user');

//adds one user to the table
//password_hash scrambles the password so the real one is never saved
function add_user($pdo, $fn, $sn, $un, $pw, $role)
{
  $hash = password_hash($pw, PASSWORD_DEFAULT);

  //IGNORE skips a user who is already there instead of failing
  $stmt = $pdo->prepare('INSERT IGNORE INTO Users (forename, surname, username, password, role)
                         VALUES(:fn,:sn,:un,:pw,:role)');
  $stmt->execute([
    ':fn'   => $fn,
    ':sn'   => $sn,
    ':un'   => $un,
    ':pw'   => $hash,
    ':role' => $role
  ]);

  //accounts made by the old version of this file got the default role, this gives them the right one
  $stmt = $pdo->prepare("UPDATE Users SET role=:role WHERE username=:un AND role='user'");
  $stmt->execute([':role' => $role, ':un' => $un]);
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <title>Art Gallery - Set Up Users</title>
</head>
<body>
   <p>The Users table is ready. Admins: ryano, user1 to user4. Registered user: client1.</p>
   <p><a href="artLogin.php">Go to the login page</a></p>
</body>
</html>
