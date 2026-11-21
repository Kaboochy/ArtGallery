<?php
//only reads the saved copies in scrape/pages, never the real site
require 'checkAdmin.php';
require_once('dbConfig.php');
require_once('artistHelpers.php');
require_once('paintingHelpers.php');
require_once('scrapeHelpers.php');

$imageFolder = 'images/art/works/square-medium/';

$scraped = scrapeFolder();
$groups = groupByArtist($scraped);

try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $insertSql = buildInsertSql($groups, $pdo);
   $pdo = null;
}
catch (PDOException $e) {
   die( $e->getMessage() );
}

//download sends the sql as a file instead of the page
if (isset($_GET['download'])) {
   header('Content-Type: text/plain; charset=utf-8');
   header('Content-Disposition: attachment; filename="scrapedArtwork.sql"');
   echo $insertSql;
   exit;
}

//everything goes in one transaction
//returns lines saying what happened to each artist and painting
function importScraped($groups, $imageFolder) {
   $report = array();
   $copied = array();
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $pdo->beginTransaction();

      foreach ($groups as $group) {
         $artist = $group['artist'];
         list($first, $last) = splitArtistName($artist['name']);

         //use the artist already in the table or add them
         $artistId = findArtistIdByName($artist['name']);
         if ($artistId == 0) {
            $artistId = insertArtist($pdo, $first, $last, $artist['nationality'], null, $artist['yearOfBirth'], $artist['yearOfDeath']);
            $report[] = 'Added artist ' . $artist['name'] . ' (ArtistID ' . $artistId . ').';
         }
         else {
            $report[] = $artist['name'] . ' is already in the artists table (ArtistID ' . $artistId . '), so the paintings were added to that artist.';
         }
         //biography and link are only filled in if the artist has none
         $statement = $pdo->prepare("update Artists set Details = coalesce(nullif(Details, ''), :details),
                                     ArtistLink = coalesce(nullif(ArtistLink, ''), :link) where ArtistID = :id");
         $statement->bindValue(':details', $artist['details'] == '' ? null : $artist['details']);
         $statement->bindValue(':link', $artist['link'] == '' ? null : $artist['link']);
         $statement->bindValue(':id', $artistId, PDO::PARAM_INT);
         $statement->execute();

         foreach ($group['paintings'] as $painting) {
            $imageName = scrapedImageName($painting);

            //skip paintings imported before or that the artist already has
            $statement = $pdo->prepare('select count(*) from Paintings where ImageFileName = :image or (Title = :title and ArtistID = :artist)');
            $statement->execute(array(':image' => $imageName, ':title' => $painting['title'], ':artist' => $artistId));
            if ($statement->fetchColumn() > 0) {
               $report[] = '&nbsp;&nbsp;Skipped ' . $painting['title'] . ', it is already in the paintings table.';
               continue;
            }
            if ($painting['image'] == '') {
               $report[] = '&nbsp;&nbsp;Skipped ' . $painting['title'] . ', there is no saved picture for it.';
               continue;
            }

            if (!copy($painting['image'], $imageFolder . $imageName . '.jpg'))
               throw new Exception('Could not copy the picture for ' . $painting['title']);
            $copied[] = $imageFolder . $imageName . '.jpg';

            //gallery is matched by the first part of the location
            $locationParts = explode(',', $painting['location']);
            $galleryId = $painting['location'] != '' ? findIdByName('Galleries', 'GalleryID', 'GalleryName', trim($locationParts[0])) : 0;

            $statement = $pdo->prepare('insert into Paintings (ArtistID, GalleryID, ImageFileName, Title, YearOfWork, Medium, Width, Height, Description, Excerpt, WikiLink)
                                        values (:artist, :gallery, :image, :title, :year, :medium, :width, :height, :description, :excerpt, :wiki)');
            $statement->bindValue(':artist', $artistId, PDO::PARAM_INT);
            $statement->bindValue(':gallery', $galleryId > 0 ? $galleryId : null, $galleryId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $statement->bindValue(':image', $imageName);
            $statement->bindValue(':title', $painting['title']);
            $statement->bindValue(':year', (int)$painting['year'], PDO::PARAM_INT);
            $statement->bindValue(':medium', $painting['medium'] != '' ? $painting['medium'] : 'Oil on canvas');
            $statement->bindValue(':width', $painting['width'] ? $painting['width'] : null);
            $statement->bindValue(':height', $painting['height'] ? $painting['height'] : null);
            //the detail page shows descriptions as html so < and & are escaped
            $statement->bindValue(':description', $painting['description'] == '' ? null : htmlspecialchars($painting['description'], ENT_NOQUOTES));
            $statement->bindValue(':excerpt', $painting['excerpt'] == '' ? null : $painting['excerpt']);
            $statement->bindValue(':wiki', $painting['wikiLink'] == '' ? null : $painting['wikiLink']);
            $statement->execute();
            $paintingId = $pdo->lastInsertId();

            //the wikiart style (like impressionism) matches one of our genres
            $styleParts = explode(',', $painting['style']);
            $genreId = $painting['style'] != '' ? findIdByName('Genres', 'GenreID', 'GenreName', trim($styleParts[0])) : 0;
            if ($genreId > 0) {
               $statement = $pdo->prepare('insert into PaintingGenres (PaintingID, GenreID) values (:painting, :genre)');
               $statement->execute(array(':painting' => $paintingId, ':genre' => $genreId));
            }
            $report[] = '&nbsp;&nbsp;Added <a href="paintingDetail.php?id=' . $paintingId . '">' . htmlspecialchars($painting['title']) . '</a> (PaintingID ' . $paintingId
                      . ', image ' . $imageName . '.jpg' . ($genreId > 0 ? ', genre ' . htmlspecialchars(trim($styleParts[0])) : '') . ($galleryId > 0 ? ', gallery found' : '') . ').';
         }
      }
      $pdo->commit();
      $pdo = null;
   }
   catch (Exception $e) {
      //undo everything, including the pictures already copied
      if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
      foreach ($copied as $file) @unlink($file);
      $report[] = 'Nothing was imported because of an error: ' . htmlspecialchars($e->getMessage());
   }
   return $report;
}

$report = array();
if (isset($_POST['import'])) {
   $report = importScraped($groups, $imageFolder);
}

//a dash when the page didnt have the value
function cell($value) {
   return ($value === '' || $value === 0 || $value === null) ? '<span class="muted">&ndash;</span>' : htmlspecialchars((string)$value);
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Import from WikiArt</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Import from WikiArt</h1>
         <p>Data scraped from the <?php echo count($scraped['artists']) + count($scraped['paintings']); ?> WikiArt pages saved in
            <code><?php echo SCRAPE_FOLDER; ?></code>. The real web site is never opened.</p>
      </div>

      <?php if (count($report) > 0) { ?>
         <div class="ui positive message">
            <div class="header">Import finished</div>
            <?php foreach ($report as $line) { ?><div><?php echo $line; ?></div><?php } ?>
         </div>
      <?php } ?>

      <?php if (count($scraped['skipped']) > 0) { ?>
         <div class="ui warning message">These saved pages were not an artist or a painting page and were skipped:
            <?php echo htmlspecialchars(implode(', ', $scraped['skipped'])); ?></div>
      <?php } ?>

      <div class="ui segment">
         <?php foreach ($groups as $group) { $artist = $group['artist']; ?>
            <div class="scrape-artist">
               <h2 class="section-title"><?php echo htmlspecialchars($artist['name']); ?></h2>
               <table class="ui definition table">
                  <tbody>
                     <tr><td class="four wide">Saved page</td><td><?php echo cell($artist['file']); ?></td></tr>
                     <tr><td>Born</td><td><?php echo cell($artist['born']); ?> (year <?php echo cell($artist['yearOfBirth']); ?>)</td></tr>
                     <tr><td>Died</td><td><?php echo cell($artist['died']); ?> (year <?php echo cell($artist['yearOfDeath']); ?>)</td></tr>
                     <tr><td>Nationality</td><td><?php echo cell($artist['nationality']); ?></td></tr>
                     <tr><td>Art movement</td><td><?php echo cell($artist['movement']); ?></td></tr>
                     <tr><td>Description</td><td><?php echo cell(mb_strimwidth($artist['details'], 0, 400, '...', 'UTF-8')); ?></td></tr>
                     <tr><td>Link</td><td><?php echo cell($artist['link']); ?></td></tr>
                  </tbody>
               </table>

               <div class="table-scroll">
               <table class="ui unstackable table user-table">
                  <thead>
                     <tr><th></th><th>Title</th><th>Year</th><th>Style</th><th>Medium</th><th>Size (cm)</th><th>Location</th><th>Image name</th></tr>
                  </thead>
                  <tbody>
                     <?php foreach ($group['paintings'] as $painting) { ?>
                        <tr>
                           <td><?php if ($painting['image'] != '') { ?><img class="scrape-thumb" src="<?php echo htmlspecialchars($painting['image']); ?>" alt=""><?php } ?></td>
                           <td><em><?php echo cell($painting['title']); ?></em></td>
                           <td><?php echo cell($painting['year']); ?></td>
                           <td><?php echo cell($painting['style']); ?></td>
                           <td><?php echo cell($painting['medium']); ?></td>
                           <td><?php echo $painting['width'] ? $painting['height'] . ' x ' . $painting['width'] : cell(''); ?></td>
                           <td><?php echo cell($painting['location']); ?></td>
                           <td><?php echo cell(scrapedImageName($painting)); ?></td>
                        </tr>
                     <?php } ?>
                  </tbody>
               </table>
               </div>
            </div>
         <?php } ?>
      </div>

      <div class="ui segment">
         <h2 class="section-title">SQL insert statements</h2>
         <textarea class="scrape-sql" readonly><?php echo htmlspecialchars($insertSql); ?></textarea>
         <div class="admin-actions" style="margin-top: 1.25rem;">
            <a class="ui button" href="scrape.php?download=sql">Download the .sql file</a>
            <form method="post" action="scrape.php" onsubmit="return confirm('Add these artists and paintings to the database?');">
               <input class="ui primary button" type="submit" name="import" value="Import into the database">
            </form>
         </div>
      </div>
   </main>
</body>
</html>
