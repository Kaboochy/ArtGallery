<?php
//suggest.php answers the search box on the artworks page while the user types (task 4, ad-hoc query)
//js/search.js calls it with fetch, like suggest.php?field=title&q=star or suggest.php?field=artist&q=mon
//it sends back JSON (not a web page): a list of up to 8 suggestions, each with a label, a small note, a picture and a link
//q is matched against the start of a painting title, or the start of an artist's last name (or full name)
//anyone can use it, the same as the gallery pages

require_once('dbConfig.php');

header('Content-Type: application/json; charset=utf-8');

$field = (isset($_GET['field']) && $_GET['field'] == 'artist') ? 'artist' : 'title';
$q = isset($_GET['q']) ? substr(trim($_GET['q']), 0, 100) : '';

//nothing typed means nothing to suggest
if ($q == '') {
   echo '[]';
   exit;
}

//% and _ mean "anything" in like, so they are escaped to mean themselves, then % is added to match the start
$prefix = addcslashes($q, '\\%_') . '%';
$suggestions = array();

try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   if ($field == 'artist') {
      //artists whose last name (or full name) starts with what was typed, with how many paintings each has
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
      //paintings whose title starts with what was typed, with the artist and year as the note
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
   //the search box just shows no suggestions if the database has a problem
   http_response_code(500);
   echo '[]';
   exit;
}

//the javascript puts the text in with textContent, so titles with < or & are shown as text and never run
//JSON_INVALID_UTF8_SUBSTITUTE stops one badly saved character from breaking the whole answer
echo json_encode($suggestions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
?>
