<?php
//needs dbConfig.php included first

//descriptions have a little html (like <em>), this keeps only those simple tags
function cleanDescription($html) {
   $html = strip_tags((string)$html, '<em><i><strong><b><br><p>');
   //kept tags lose their attributes so <em onclick=...> becomes <em>
   return preg_replace('/<(em|i|strong|b|br|p)\b[^>]*>/i', '<$1>', $html);
}

//a blank link is fine too
function isValidLink($link) {
   return $link == '' || (strlen($link) <= 255 && preg_match('#^https?://\S+$#i', $link));
}

//each image file name can only be used once
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

//the city shows in small grey text
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

//exact name, not case sensitive, 0 if none
//$table and the column names come from this file only, never from user input
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
