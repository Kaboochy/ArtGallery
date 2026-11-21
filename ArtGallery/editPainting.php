<?php
//editPainting.php changes the information of one painting (task 1)
//the Edit buttons on the artworks page and the painting detail page link here with editPainting.php?id=
//the PaintingID is the primary key so it is only shown, it can never be changed
//the fields that can be changed: title, artist, year, medium, gallery, description, wikipedia link, museum link and the picture

//only admins (staff) can change paintings (task 10)
require 'checkAdmin.php';
require_once('dbConfig.php');
require_once('artistHelpers.php');
require_once('combobox.php');
require_once('paintingHelpers.php');
require_once('uploadHelpers.php');

$imageFolder = 'images/art/works/square-medium/';

//(int) turns anything that isnt a number into 0 so it cant break the sql
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (isset($_POST['paintingId'])) $id = (int)$_POST['paintingId'];

//gets the painting with its artist and gallery names, or false if there is no painting with this id
function getPaintingForEdit($id) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'select Paintings.PaintingID, Paintings.Title, Paintings.YearOfWork, Paintings.Medium, Paintings.Description,
                     Paintings.WikiLink, Paintings.MuseumLink, Paintings.ImageFileName,
                     Artists.FirstName, Artists.LastName, Galleries.GalleryName
              from Paintings
              left join Artists on Paintings.ArtistID = Artists.ArtistID
              left join Galleries on Paintings.GalleryID = Galleries.GalleryID
              where Paintings.PaintingID=:id';
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':id', $id, PDO::PARAM_INT);
      $statement->execute();
      $row = $statement->fetch(PDO::FETCH_ASSOC);
      $pdo = null;
      return $row;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//saves the changed fields with one prepared update, the where uses the id so only this painting changes
function updatePainting($id, $title, $artistId, $year, $medium, $galleryId, $description, $wikiLink, $museumLink) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'update Paintings
              set Title=:title, ArtistID=:artist, YearOfWork=:year, Medium=:medium, GalleryID=:gallery,
                  Description=:description, WikiLink=:wiki, MuseumLink=:museum
              where PaintingID=:id';
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':title', $title);
      $statement->bindValue(':artist', $artistId, PDO::PARAM_INT);
      $statement->bindValue(':year', (int)$year, PDO::PARAM_INT);
      $statement->bindValue(':medium', $medium);
      //no gallery picked is saved as null
      if ($galleryId > 0)
         $statement->bindValue(':gallery', $galleryId, PDO::PARAM_INT);
      else
         $statement->bindValue(':gallery', null, PDO::PARAM_NULL);
      //blank text is saved as null
      $statement->bindValue(':description', $description == '' ? null : $description);
      $statement->bindValue(':wiki', $wikiLink == '' ? null : $wikiLink);
      $statement->bindValue(':museum', $museumLink == '' ? null : $museumLink);
      $statement->bindValue(':id', $id, PDO::PARAM_INT);
      $statement->execute();
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

$painting = getPaintingForEdit($id);
$errors = array();

if ($painting) {
   //the form starts with what is saved now
   $title = $painting['Title'];
   $artistName = artistDisplayName($painting['FirstName'], $painting['LastName']);
   $year = $painting['YearOfWork'] ? (string)$painting['YearOfWork'] : '';
   $medium = (string)$painting['Medium'];
   $galleryName = (string)$painting['GalleryName'];
   $description = (string)$painting['Description'];
   $wikiLink = (string)$painting['WikiLink'];
   $museumLink = (string)$painting['MuseumLink'];

   //this runs when SAVE CHANGES is clicked
   if (isset($_POST['save'])) {
      $title = trim($_POST['title']);
      $artistName = preg_replace('/\s+/', ' ', trim($_POST['artistName']));
      $year = trim($_POST['year']);
      $medium = trim($_POST['medium']);
      $galleryName = preg_replace('/\s+/', ' ', trim($_POST['galleryName']));
      $description = trim($_POST['description']);
      $wikiLink = trim($_POST['wikiLink']);
      $museumLink = trim($_POST['museumLink']);

      //server side checks (task 7b), each problem gets added to $errors
      if ($title == '')
         $errors[] = 'Please enter a title.';
      else if (strlen($title) > 255)
         $errors[] = 'The title can be at most 255 characters.';

      //the artist has to be one already in the artists table (new artists are added on the Add Artist tab)
      $artistId = findArtistIdByName($artistName);
      if ($artistName == '')
         $errors[] = 'Please choose an artist.';
      else if ($artistId == 0)
         $errors[] = 'There is no artist named ' . $artistName . '. Pick one from the list or add them on the Add Artist tab first.';

      if (!ctype_digit($year) || strlen($year) > 4)
         $errors[] = 'Please enter the year as a number, like 1889.';
      else if ((int)$year > (int)date('Y'))
         $errors[] = 'The year cannot be after ' . date('Y') . '.';

      if (strlen($medium) > 255)
         $errors[] = 'The medium can be at most 255 characters.';
      if ($medium == '') $medium = 'Oil on canvas';

      $galleryId = 0;
      if ($galleryName != '') {
         $galleryId = findIdByName('Galleries', 'GalleryID', 'GalleryName', $galleryName);
         if ($galleryId == 0)
            $errors[] = 'There is no gallery named ' . $galleryName . '. Please pick one from the list.';
      }

      if (strlen($description) > 10000)
         $errors[] = 'The description can be at most 10000 characters.';

      if (!isValidLink($wikiLink))
         $errors[] = 'The Wikipedia link has to start with http:// or https://';
      if (!isValidLink($museumLink))
         $errors[] = 'The museum link has to start with http:// or https://';

      //a new picture replaces the old file but keeps the same image file name (task 6)
      $uploading = uploadWasSent('imageFile');
      if ($uploading) {
         $uploadError = checkUpload('imageFile');
         if ($uploadError != '') $errors[] = $uploadError;
      }

      if (count($errors) == 0) {
         if ($uploading && !saveUpload('imageFile', $imageFolder, $painting['ImageFileName'])) {
            $errors[] = 'The new picture could not be saved into the images folder.';
         }
         else {
            updatePainting($id, $title, $artistId, $year, $medium, $galleryId, cleanDescription($description), $wikiLink, $museumLink);
            header('Location: paintingDetail.php?id=' . $id . '&updated=yes');
            exit;
         }
      }
   }
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Edit Artwork</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
   <link href="css/combobox.css?v=<?php echo filemtime("css/combobox.css"); ?>" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <?php if (!$painting) { ?>
         <div class="ui secondary segment"><h1>Edit Artwork</h1></div>
         <div class="ui negative message">That painting could not be found. It may have been deleted.</div>
         <a class="ui button" href="artMain.php">Back to Artworks</a>
      <?php } else { ?>
         <div class="ui secondary segment">
            <h1>Edit Artwork</h1>
            <p>Change the details and click SAVE CHANGES. The painting ID cannot be changed.</p>
         </div>

         <?php //red box that lists every problem the server found ?>
         <?php if (count($errors) > 0) { ?>
            <div class="ui negative message">
               <div class="header">The changes were not saved:</div>
               <ul class="list">
                  <?php foreach ($errors as $error) { ?>
                     <li><?php echo htmlspecialchars($error); ?></li>
                  <?php } ?>
               </ul>
            </div>
         <?php } ?>

         <div class="ui segment">
            <form class="ui form" method="post" action="editPainting.php?id=<?php echo $id; ?>" enctype="multipart/form-data" data-check-form>
               <input type="hidden" name="paintingId" value="<?php echo $id; ?>">

               <?php //the current picture, so the admin can see which painting this is ?>
               <div class="edit-preview">
                  <img src="<?php echo $imageFolder . htmlspecialchars($painting['ImageFileName']); ?>.jpg?v=<?php echo @filemtime($imageFolder . $painting['ImageFileName'] . '.jpg'); ?>"
                       alt="<?php echo htmlspecialchars($painting['Title']); ?>">
               </div>

               <div class="two fields">
                  <div class="field">
                     <label>Painting ID</label>
                     <input type="text" value="<?php echo $id; ?>" readonly class="readonly">
                  </div>
                  <div class="field">
                     <label>Image file name</label>
                     <input type="text" value="<?php echo htmlspecialchars($painting['ImageFileName']); ?>.jpg" readonly class="readonly">
                  </div>
               </div>
               <div class="required field">
                  <label>Title</label>
                  <input type="text" name="title" maxlength="255" required placeholder="for example The Starry Night" value="<?php echo htmlspecialchars($title); ?>">
               </div>
               <div class="required field">
                  <label>Artist</label>
                  <?php outputComboBox('artistName', $artistName, 'Start typing, for example Vincent Van Gogh', artistComboItems(), array('required' => true)); ?>
               </div>
               <div class="two fields">
                  <div class="required field">
                     <label>Year</label>
                     <input type="text" name="year" inputmode="numeric" maxlength="4" required data-check="year" placeholder="for example 1889" value="<?php echo htmlspecialchars($year); ?>">
                  </div>
                  <div class="field">
                     <label>Medium</label>
                     <input type="text" name="medium" maxlength="255" placeholder="Oil on canvas" value="<?php echo htmlspecialchars($medium); ?>">
                  </div>
               </div>
               <div class="field">
                  <label>Gallery</label>
                  <?php outputComboBox('galleryName', $galleryName, 'Optional, for example National Gallery', galleryComboItems()); ?>
               </div>
               <div class="field">
                  <label>Description</label>
                  <textarea name="description" rows="7" maxlength="10000" placeholder="A few sentences about the painting. <em>Titles</em> can be put in em tags."><?php echo htmlspecialchars($description); ?></textarea>
               </div>
               <div class="field">
                  <label>Wikipedia link</label>
                  <input type="text" name="wikiLink" maxlength="255" data-check="url" placeholder="https://en.wikipedia.org/wiki/..." value="<?php echo htmlspecialchars($wikiLink); ?>">
               </div>
               <div class="field">
                  <label>Museum link</label>
                  <input type="text" name="museumLink" maxlength="255" data-check="url" placeholder="https://www.museum.org/..." value="<?php echo htmlspecialchars($museumLink); ?>">
               </div>
               <div class="field">
                  <label>Replace the picture (.jpg, at most 2 MB)</label>
                  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_UPLOAD_BYTES; ?>">
                  <input type="file" name="imageFile" accept=".jpg,.jpeg,image/jpeg" data-check="jpg">
                  <small>Leave this empty to keep the picture shown above.</small>
               </div>
               <input class="ui primary button" type="submit" name="save" value="SAVE CHANGES">
               <a class="ui button" href="paintingDetail.php?id=<?php echo $id; ?>">Cancel</a>
            </form>
         </div>
      <?php } ?>
   </main>
   <script src="js/combobox.js?v=<?php echo filemtime("js/combobox.js"); ?>"></script>
   <script src="js/formCheck.js?v=<?php echo filemtime("js/formCheck.js"); ?>"></script>
</body>
</html>
