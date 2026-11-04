<?php
//paintingDetail.php shows everything about one painting
//the other pages link here with paintingDetail.php?id= and the painting id

//this makes sure someone is logged in, if not it sends them to the login page
require 'checkLogin.php';

//this has the database name, user and password
require_once('dbConfig.php');

//(int) turns anything that isnt a number into 0 so it cant break the sql
$id = 0;
if (isset($_GET['id'])) $id = (int)$_GET['id'];

$row = false;
$genres = array();

try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   //the joins get the artist and gallery info for the painting
   $sql = 'select Paintings.*, Artists.FirstName, Artists.LastName, Artists.Nationality,
                  Galleries.GalleryName, Galleries.GalleryCity, Galleries.GalleryCountry
           from Paintings
           left join Artists on Paintings.ArtistID = Artists.ArtistID
           left join Galleries on Paintings.GalleryID = Galleries.GalleryID
           where Paintings.PaintingID=:id';
   $statement = $pdo->prepare($sql);
   $statement->bindValue(':id', $id);
   $statement->execute();
   $row = $statement->fetch(PDO::FETCH_ASSOC);

   //a painting can have more than one genre so they come from PaintingGenres
   $sql = 'select Genres.GenreID, Genres.GenreName
           from PaintingGenres
           join Genres on PaintingGenres.GenreID = Genres.GenreID
           where PaintingGenres.PaintingID=:id
           order by Genres.GenreName';
   $statement = $pdo->prepare($sql);
   $statement->bindValue(':id', $id);
   $statement->execute();
   $genres = $statement->fetchAll(PDO::FETCH_ASSOC);

   $pdo = null;
}
catch (PDOException $e) {
   die( $e->getMessage() );
}

//shows one line of the details table, empty values are skipped
function outputDetail($label, $value) {
   if ($value == '' || $value == '0') return;
   echo '<tr><td class="four wide"><strong>' . $label . '</strong></td>';
   echo '<td>' . htmlspecialchars($value) . '</td></tr>';
}

?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery</title>
   <link href="semantic/semantic.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php //the tab bar at the top ?>
      <?php include 'nav.php'; ?>

      <?php if (!$row) { ?>
         <?php //the id was missing or no painting has that id (it may have been deleted) ?>
         <div class="ui negative message">That painting could not be found.</div>
         <a class="ui button" href="artMain.php">Back to Artworks</a>
      <?php } else { ?>
         <?php
         //some artists dont have a first name (like Rembrandt)
         if ($row['FirstName'] != '')
            $artist = $row['FirstName'] . ' ' . $row['LastName'];
         else
            $artist = $row['LastName'];

         //city and country are put together into one place
         $place = $row['GalleryCity'];
         if ($row['GalleryCountry'] != '') {
            if ($place != '') $place .= ', ';
            $place .= $row['GalleryCountry'];
         }

         $size = '';
         if ($row['Width'] && $row['Height'])
            $size = $row['Width'] . ' x ' . $row['Height'] . ' cm';
         ?>

         <?php //the heading with the title, artist and year ?>
         <div class="ui secondary segment">
            <h1><?php echo htmlspecialchars($row['Title']); ?></h1>
            <p>
               <a href="artistPaintings.php?id=<?php echo $row['ArtistID']; ?>"><?php echo htmlspecialchars($artist); ?></a>
               <?php if ($row['YearOfWork']) echo ', ' . $row['YearOfWork']; ?>
            </p>
         </div>

         <div class="ui segment">
            <div class="ui stackable grid">
               <?php //the picture on the left ?>
               <div class="six wide column">
                  <img class="ui fluid image" src="images/art/works/square-medium/<?php echo $row['ImageFileName']; ?>.jpg"
                     alt="<?php echo htmlspecialchars($row['Title']); ?>">
               </div>

               <?php //the description and details on the right ?>
               <div class="ten wide column">
                  <?php //the description already has html in it like <em> so it is not escaped ?>
                  <?php if ($row['Description'] != '') { ?>
                     <p><?php echo $row['Description']; ?></p>
                  <?php } else { ?>
                     <p><em>No description yet.</em></p>
                  <?php } ?>

                  <table class="ui definition table">
                     <tbody>
                        <?php
                        outputDetail('Artist', $artist);
                        outputDetail('Nationality', $row['Nationality']);
                        outputDetail('Year', $row['YearOfWork']);
                        outputDetail('Medium', $row['Medium']);
                        outputDetail('Size', $size);
                        outputDetail('Gallery', $row['GalleryName']);
                        outputDetail('Location', $place);
                        outputDetail('Accession number', $row['AccessionNumber']);
                        outputDetail('Copyright', $row['CopyrightText']);
                        ?>
                        <?php //each genre links to its page on the Genres tab ?>
                        <?php if (count($genres) > 0) { ?>
                           <tr><td><strong>Genres</strong></td><td>
                           <?php foreach ($genres as $genre) { ?>
                              <a class="ui label" href="genreDetail.php?id=<?php echo $genre['GenreID']; ?>"><?php echo htmlspecialchars($genre['GenreName']); ?></a>
                           <?php } ?>
                           </td></tr>
                        <?php } ?>
                     </tbody>
                  </table>

                  <?php //links to the museum and wikipedia, only if the painting has them ?>
                  <?php if ($row['MuseumLink'] != '') { ?>
                     <a class="ui labeled icon primary button" href="<?php echo htmlspecialchars($row['MuseumLink']); ?>">
                        <i class="external icon"></i> View at the museum
                     </a>
                  <?php } ?>
                  <?php if ($row['WikiLink'] != '') { ?>
                     <a class="ui labeled icon button" href="<?php echo htmlspecialchars($row['WikiLink']); ?>">
                        <i class="external icon"></i> Read more
                     </a>
                  <?php } ?>
               </div>
            </div>
         </div>

         <a class="ui button" href="artMain.php">Back to Artworks</a>
      <?php } ?>
   </main>

</body>
</html>
