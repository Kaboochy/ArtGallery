<?php
//based on setupusers.php from www13
//safe to open again, it never deletes accounts

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

//if not exists so running again never deletes accounts
//role is admin for staff or user for registered people
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

//tables from the old version only have the first 4 columns, so add the rest
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

add_user($pdo, 'Ryan', 'ONeil', 'ryano', 'ryano', 'admin');

//the 4 task 4c users were staff accounts so they stay admins
for ($n = 1; $n <= 4; $n++)
{
  add_user($pdo, "first$n", "last$n", "user$n", "pass$n", 'admin');
}

//a normal user for showing what non-staff can do
add_user($pdo, 'Claire', 'Client', 'client1', 'client1', 'user');

//password_hash so the real password is never saved
function add_user($pdo, $fn, $sn, $un, $pw, $role)
{
  $hash = password_hash($pw, PASSWORD_DEFAULT);

  //IGNORE skips a user who already exists
  $stmt = $pdo->prepare('INSERT IGNORE INTO Users (forename, surname, username, password, role)
                         VALUES(:fn,:sn,:un,:pw,:role)');
  $stmt->execute([
    ':fn'   => $fn,
    ':sn'   => $sn,
    ':un'   => $un,
    ':pw'   => $hash,
    ':role' => $role
  ]);

  //accounts from the old version got the default role, this fixes it
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
