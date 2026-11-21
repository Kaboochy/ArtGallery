<?php
//needs dbConfig.php included first

//artists with no first name (like rembrandt) show only the last name
function artistDisplayName($firstName, $lastName) {
   if (trim((string)$firstName) != '')
      return trim($firstName) . ' ' . trim($lastName);
   return trim($lastName);
}

function getAllArtists() {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'select ArtistID, FirstName, LastName from Artists order by LastName, FirstName';
      $result = $pdo->query($sql);
      $artists = array();
      while ($row = $result->fetch()) {
         $artists[] = array(
            'ArtistID' => $row['ArtistID'],
            'Name' => artistDisplayName($row['FirstName'], $row['LastName'])
         );
      }
      $pdo = null;
      return $artists;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

function artistComboItems() {
   $items = array();
   foreach (getAllArtists() as $artist) {
      $items[] = array('value' => $artist['Name']);
   }
   return $items;
}

//not case sensitive, returns 0 if nobody has that name
function findArtistIdByName($name) {
   //squash spaces so extra spaces still match
   $name = preg_replace('/\s+/', ' ', trim($name));
   if ($name == '') return 0;
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      //concat_ws skips a null first name, nullif turns a blank one into null
      $sql = "select ArtistID from Artists
              where lower(concat_ws(' ', nullif(trim(FirstName), ''), trim(LastName))) = lower(:name)
              order by ArtistID limit 1";
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':name', $name);
      $statement->execute();
      $row = $statement->fetch();
      $pdo = null;
      return $row ? (int)$row['ArtistID'] : 0;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//the last word is the last name, the rest is the first name
//"frida kahlo" -> frida / kahlo, "rembrandt" -> blank / rembrandt
function splitArtistName($name) {
   $name = preg_replace('/\s+/', ' ', trim($name));
   $spot = strrpos($name, ' ');
   if ($spot === false)
      return array('', $name);
   return array(substr($name, 0, $spot), substr($name, $spot + 1));
}

//pass an open $pdo to join a bigger transaction, or null for a new connection
function insertArtist($pdo, $firstName, $lastName, $nationality = null, $gender = null, $yearOfBirth = null, $yearOfDeath = null) {
   $ownConnection = ($pdo === null);
   if ($ownConnection) {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   }
   $sql = 'insert into Artists (FirstName, LastName, Nationality, Gender, YearOfBirth, YearOfDeath)
           values (:first, :last, :nationality, :gender, :born, :died)';
   $statement = $pdo->prepare($sql);
   //blank values are saved as null
   $statement->bindValue(':first', $firstName == '' ? null : $firstName);
   $statement->bindValue(':last', $lastName);
   $statement->bindValue(':nationality', $nationality == '' ? null : $nationality);
   $statement->bindValue(':gender', $gender == '' ? null : $gender);
   //0 means the year is not known
   $statement->bindValue(':born', $yearOfBirth == '' ? 0 : (int)$yearOfBirth, PDO::PARAM_INT);
   $statement->bindValue(':died', $yearOfDeath == '' ? 0 : (int)$yearOfDeath, PDO::PARAM_INT);
   $statement->execute();
   $newId = (int)$pdo->lastInsertId();
   if ($ownConnection) $pdo = null;
   return $newId;
}

//a new nationality can still be typed in
function nationalityComboItems() {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "select distinct Nationality from Artists where Nationality is not null and Nationality <> '' order by Nationality";
      $result = $pdo->query($sql);
      $items = array();
      while ($row = $result->fetch()) {
         $items[] = array('value' => $row['Nationality']);
      }
      $pdo = null;
      return $items;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

function getArtist($id) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $statement = $pdo->prepare('select * from Artists where ArtistID=:id');
      $statement->bindValue(':id', (int)$id, PDO::PARAM_INT);
      $statement->execute();
      $row = $statement->fetch(PDO::FETCH_ASSOC);
      $pdo = null;
      return $row;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}
?>
