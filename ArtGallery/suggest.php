<?php
//answers js/search.js with json, up to 8 matches

require_once('dbConfig.php');

header('Content-Type: application/json; charset=utf-8');

$field = (isset($_GET['field']) && $_GET['field'] == 'artist') ? 'artist' : 'title';
$q = isset($_GET['q']) ? substr(trim($_GET['q']), 0, 100) : '';

if ($q == '') {
   echo '[]';
   exit;
}

//% and _ are wildcards in like so they are escaped, then % matches the start
$prefix = addcslashes($q, '\\%_') . '%';
$suggestions = array();

try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   if ($field == 'artist') {
      $sql = "select Artists.ArtistID, Artists.FirstName, Artists.LastName, Artists.YearOfBirth, Artists.YearOfDeath,
                     count(Paintings.PaintingID) as total
              from Artists
              left join Paintings on Paintings.ArtistID = Artists.ArtistID
              where Artists.LastName like :p1 or concat_ws(' ', Artists.FirstName, Artists.LastName) like :p2
              group by Artists.ArtistID, Artists.FirstName, Artists.LastName, Artists.YearOfBirth, Artists.YearOfDeath
              order by Artists.LastName, Artists.FirstName
              limit 8";
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':p1', $prefix);
      $statement->bindValue(':p2', $prefix);
      $statement->execute();
      while ($row = $statement->fetch()) {
         $name = trim($row['FirstName'] . ' ' . $row['LastName']);
         $years = ($row['YearOfBirth'] && $row['YearOfDeath']) ? $row['YearOfBirth'] . '–' . $row['YearOfDeath'] . ', ' : '';
         $suggestions[] = array(
            'label' => $name,
            'note'  => $years . $row['total'] . ' painting' . ($row['total'] == 1 ? '' : 's'),
            'image' => '',
            'url'   => 'artistPaintings.php?id=' . $row['ArtistID']
         );
      }
   }
   else {
      $sql = 'select Paintings.PaintingID, Paintings.Title, Paintings.YearOfWork, Paintings.ImageFileName,
                     Artists.FirstName, Artists.LastName
              from Paintings
              left join Artists on Paintings.ArtistID = Artists.ArtistID
              where Paintings.Title like :p1
              order by Paintings.Title
              limit 8';
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':p1', $prefix);
      $statement->execute();
      while ($row = $statement->fetch()) {
         $artist = trim($row['FirstName'] . ' ' . $row['LastName']);
         $suggestions[] = array(
            'label' => $row['Title'],
            'note'  => $artist . ($row['YearOfWork'] ? ', ' . $row['YearOfWork'] : ''),
            'image' => 'images/art/works/square-medium/' . $row['ImageFileName'] . '.jpg',
            'url'   => 'paintingDetail.php?id=' . $row['PaintingID']
         );
      }
   }
   $pdo = null;
}
catch (PDOException $e) {
   //the search box just shows nothing if the database fails
   http_response_code(500);
   echo '[]';
   exit;
}

//js puts the text in with textContent so < and & never run
//JSON_INVALID_UTF8_SUBSTITUTE stops one bad character breaking the whole answer
echo json_encode($suggestions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
?>
