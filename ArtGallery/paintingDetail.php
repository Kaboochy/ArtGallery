<?php
//anyone can view, admins also get an edit button
require_once 'auth.php';

require_once('dbConfig.php');

require_once('paintingHelpers.php');

//(int) turns anything that isnt a number into 0
$id = 0;
if (isset($_GET['id'])) $id = (int)$_GET['id'];

$row = false;
$genres = array();

try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   //the joins add the artist and gallery info
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

   //a painting can have several genres so they come from PaintingGenres
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

//empty values are skipped
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
   <link href="css/theme.css" rel="stylesheet">
   <link href="css/effects.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <?php if ($row && isset($_GET['updated'])) { ?>
         <div class="ui positive message">The changes to this painting were saved.</div>
      <?php } ?>

      <?php if (!$row) { ?>
         <?php //missing id or the painting was deleted ?>
         <div class="ui negative message">That painting could not be found.</div>
         <a class="ui button" href="artMain.php">Back to Artworks</a>
      <?php } else { ?>
         <?php
         //some artists have no first name (like rembrandt)
         if ($row['FirstName'] != '')
            $artist = $row['FirstName'] . ' ' . $row['LastName'];
         else
            $artist = $row['LastName'];

         //city and country are joined into one place
         $place = $row['GalleryCity'];
         if ($row['GalleryCountry'] != '') {
            if ($place != '') $place .= ', ';
            $place .= $row['GalleryCountry'];
         }

         $size = '';
         if ($row['Width'] && $row['Height'])
            $size = $row['Width'] . ' x ' . $row['Height'] . ' cm';
         ?>

         <div class="ui secondary segment">
            <h1><?php echo htmlspecialchars($row['Title']); ?></h1>
            <p>
               <a href="artistPaintings.php?id=<?php echo $row['ArtistID']; ?>"><?php echo htmlspecialchars($artist); ?></a>
               <?php if ($row['YearOfWork']) echo ', ' . $row['YearOfWork']; ?>
            </p>
         </div>

         <div class="ui segment">
            <div class="ui stackable grid">
               <div class="six wide column">
                  <?php //?v= changes when a new picture is uploaded so the browser shows it ?>
                  <img class="ui fluid image" src="images/art/works/square-medium/<?php echo htmlspecialchars($row['ImageFileName']); ?>.jpg?v=<?php echo @filemtime('images/art/works/square-medium/' . $row['ImageFileName'] . '.jpg'); ?>"
                     alt="<?php echo htmlspecialchars($row['Title']); ?>">
               </div>

               <div class="ten wide column">
                  <?php //cleanDescription keeps only safe tags like <em> ?>
                  <?php if ($row['Description'] != '') { ?>
                     <p><?php echo cleanDescription($row['Description']); ?></p>
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
                        <?php if (count($genres) > 0) { ?>
                           <tr><td><strong>Genres</strong></td><td>
                           <?php foreach ($genres as $genre) { ?>
                              <a class="ui label" href="genreDetail.php?id=<?php echo $genre['GenreID']; ?>"><?php echo htmlspecialchars($genre['GenreName']); ?></a>
                           <?php } ?>
                           </td></tr>
                        <?php } ?>
                     </tbody>
                  </table>

                  <?php //only shown if the painting has them ?>
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
         <?php if (isAdmin()) { ?>
            <a class="ui primary button" href="editPainting.php?id=<?php echo $row['PaintingID']; ?>">Edit this painting</a>
         <?php } ?>
      <?php } ?>
   </main>

   <script src="js/jquery-3.1.1.min.js"></script>
   <script src="js/artEffects.js"></script>
</body>
</html>
