<?php
//paintingHelpers.php has the painting functions shared by addPainting.php, editPainting.php and scrape.php
//the page that includes this must already include dbConfig.php

//descriptions in the database have a little html in them (like <em> for titles)
//this keeps only those simple tags and their text, so a description can never add a script or a link to the page
function cleanDescription($html) {
   $html = strip_tags((string)$html, '<em><i><strong><b><br><p>');
   //the kept tags lose any attributes, so <em onclick="..."> becomes <em>
   return preg_replace('/<(em|i|strong|b|br|p)\b[^>]*>/i', '<$1>', $html);
}

//true for a link that starts with http:// or https:// (a blank link is fine too)
function isValidLink($link) {
   return $link == '' || (strlen($link) <= 255 && preg_match('#^https?://\S+$#i', $link));
}

//checks if another painting already uses this image file name
//the paintings table only allows each image file name once
function imageNameTaken($imageName) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'select count(*) as total from Paintings where ImageFileName=:image';
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':image', $imageName);
      $statement->execute();
      $row = $statement->fetch();
      $pdo = null;
      return $row['total'] > 0;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//gets every genre as choices for the genre dropdown
function genreComboItems() {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $result = $pdo->query('select GenreName from Genres order by GenreName');
      $items = array();
      while ($row = $result->fetch()) {
         $items[] = array('value' => $row['GenreName']);
      }
      $pdo = null;
      return $items;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//gets every gallery as choices for the gallery dropdown, the city shows in small grey text
function galleryComboItems() {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $result = $pdo->query('select GalleryName, GalleryCity from Galleries order by GalleryName');
      $items = array();
      while ($row = $result->fetch()) {
         $items[] = array('value' => $row['GalleryName'], 'note' => $row['GalleryCity']);
      }
      $pdo = null;
      return $items;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//finds the id of the genre or gallery with this exact name (not case sensitive), 0 if there is none
//$table and $idColumn and $nameColumn come from this file only, never from what the user typed
function findIdByName($table, $idColumn, $nameColumn, $name) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "select $idColumn as id from $table where lower(trim($nameColumn)) = lower(:name) order by $idColumn limit 1";
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':name', $name);
      $statement->execute();
      $row = $statement->fetch();
      $pdo = null;
      return $row ? (int)$row['id'] : 0;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}
?>
