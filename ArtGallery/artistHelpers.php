<?php
//artistHelpers.php has the artist functions shared by addPainting.php, addArtist.php and artistPaintings.php
//the page that includes this must already include dbConfig.php

//builds the name people see, like "Vincent Van Gogh"
//some artists dont have a first name (like Rembrandt) so only the last name shows
function artistDisplayName($firstName, $lastName) {
   if (trim((string)$firstName) != '')
      return trim($firstName) . ' ' . trim($lastName);
   return trim($lastName);
}

//gets every artist as a list of arrays with ArtistID and Name, sorted by last name
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

//gets every artist as choices for the type-in dropdown (see combobox.php)
function artistComboItems() {
   $items = array();
   foreach (getAllArtists() as $artist) {
      $items[] = array('value' => $artist['Name']);
   }
   return $items;
}

//finds the artist whose full name matches what was typed (not case sensitive)
//returns the ArtistID, or 0 if there is no artist with that name
function findArtistIdByName($name) {
   //squash extra spaces so "Vincent   Van Gogh" still matches
   $name = preg_replace('/\s+/', ' ', trim($name));
   if ($name == '') return 0;
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      //concat_ws skips the first name when it is null, nullif turns a blank first name into null
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

//splits a typed full name into first and last name
//the last word is the last name and everything before it is the first name
//"Frida Kahlo" -> Frida / Kahlo, "Rembrandt" -> blank / Rembrandt
function splitArtistName($name) {
   $name = preg_replace('/\s+/', ' ', trim($name));
   $spot = strrpos($name, ' ');
   if ($spot === false)
      return array('', $name);
   return array(substr($name, 0, $spot), substr($name, $spot + 1));
}

//adds a new artist and returns the new ArtistID
//pass in an open $pdo when this is part of a bigger transaction, or null to open a new connection
function insertArtist($pdo, $firstName, $lastName, $nationality = null, $gender = null, $yearOfBirth = null, $yearOfDeath = null) {
   $ownConnection = ($pdo === null);
   if ($ownConnection) {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   }
   $sql = 'insert into Artists (FirstName, LastName, Nationality, Gender, YearOfBirth, YearOfDeath)
           values (:first, :last, :nationality, :gender, :born, :died)';
   $statement = $pdo->prepare($sql);
   //blank values are saved as null instead of an empty string
   $statement->bindValue(':first', $firstName == '' ? null : $firstName);
   $statement->bindValue(':last', $lastName);
   $statement->bindValue(':nationality', $nationality == '' ? null : $nationality);
   $statement->bindValue(':gender', $gender == '' ? null : $gender);
   //0 is what the database uses when the year is not known
   $statement->bindValue(':born', $yearOfBirth == '' ? 0 : (int)$yearOfBirth, PDO::PARAM_INT);
   $statement->bindValue(':died', $yearOfDeath == '' ? 0 : (int)$yearOfDeath, PDO::PARAM_INT);
   $statement->execute();
   $newId = (int)$pdo->lastInsertId();
   if ($ownConnection) $pdo = null;
   return $newId;
}
?>
