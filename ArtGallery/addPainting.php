<?php
//addPainting.php is the page for adding a new painting (part 3 bonus)
//it has a form and an ADD button that saves the painting into the paintings table

//only admins (staff) can add paintings (task 10)
require 'checkAdmin.php';

//this has the database name, user and password
require_once('dbConfig.php');

//shared artist functions (the type-in artist dropdown, finding and adding artists)
require_once('artistHelpers.php');

//the themed type-in dropdown used for artist, genre and gallery
require_once('combobox.php');

//saving an uploaded picture into the images folder (task 6)
require_once('uploadHelpers.php');

//shared painting functions (genre and gallery dropdowns, finding ids, checking image names)
require_once('paintingHelpers.php');

//the folder where all the painting pictures are kept
$imageFolder = 'images/art/works/square-medium/';

//saves the new painting into the paintings table
//every other column (cost, price, shape and so on) uses the database default
//if $artistId is 0 the typed artist name is new, so the artist is added first
//$galleryId and $genreId are 0 when they were left blank
//the genre goes in the PaintingGenres table because a painting can have more than one
//all the inserts are in one transaction so a failed painting never leaves a stray artist or genre row behind
function addPainting($title, $artistId, $newArtistName, $year, $medium, $imageName, $galleryId, $genreId) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $pdo->beginTransaction();

      if ($artistId == 0) {
         list($firstName, $lastName) = splitArtistName($newArtistName);
         $artistId = insertArtist($pdo, $firstName, $lastName);
      }

      $sql = 'insert into Paintings (ArtistID, GalleryID, ImageFileName, Title, YearOfWork, Medium)
              values (:artist, :gallery, :image, :title, :year, :medium)';
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':artist', $artistId);
      //no gallery picked is saved as null
      if ($galleryId > 0)
         $statement->bindValue(':gallery', $galleryId, PDO::PARAM_INT);
      else
         $statement->bindValue(':gallery', null, PDO::PARAM_NULL);
      $statement->bindValue(':image', $imageName);
      $statement->bindValue(':title', $title);
      $statement->bindValue(':year', $year);
      $statement->bindValue(':medium', $medium);
      $statement->execute();

      if ($genreId > 0) {
         $paintingId = $pdo->lastInsertId();
         $statement = $pdo->prepare('insert into PaintingGenres (PaintingID, GenreID) values (:painting, :genre)');
         $statement->bindValue(':painting', $paintingId, PDO::PARAM_INT);
         $statement->bindValue(':genre', $genreId, PDO::PARAM_INT);
         $statement->execute();
      }

      $pdo->commit();
      $pdo = null;
   }
   catch (PDOException $e) {
      if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
      die( $e->getMessage() );
   }
}

//these hold what the user typed so the form can show it again if there is a mistake
$title = '';
$artistName = '';
$year = '';
$medium = '';
$imageName = '';
$genreName = '';
$galleryName = '';
$errors = array();

//this runs when the ADD button is clicked
if (isset($_POST['add'])) {
   //trim takes off extra spaces at the start and end
   $title = trim($_POST['title']);
   //squash extra spaces in the typed artist name
   $artistName = preg_replace('/\s+/', ' ', trim($_POST['artistName']));
   $year = trim($_POST['year']);
   $medium = trim($_POST['medium']);
   $imageName = trim($_POST['imageName']);
   $genreName = preg_replace('/\s+/', ' ', trim($_POST['genreName']));
   $galleryName = preg_replace('/\s+/', ' ', trim($_POST['galleryName']));

   //checks everything before saving, each problem gets added to $errors
   if ($title == '')
      $errors[] = 'Please enter a title.';
   else if (strlen($title) > 255)
      $errors[] = 'The title can be at most 255 characters.';

   //the artist can be picked from the dropdown or typed in
   //a name that is not in the list becomes a new artist when the painting is saved
   $artistId = 0;
   if ($artistName == '')
      $errors[] = 'Please choose or type an artist.';
   else {
      $artistId = findArtistIdByName($artistName);
      if ($artistId == 0) {
         list($newFirst, $newLast) = splitArtistName($artistName);
         if (strlen($newFirst) > 50 || strlen($newLast) > 50)
            $errors[] = 'A new artist\'s first and last name can each be at most 50 characters.';
      }
   }

   //the year has to be a number and cant be in the future (task 7b)
   if (!ctype_digit($year) || strlen($year) > 4)
      $errors[] = 'Please enter the year as a number, like 1889.';
   else if ((int)$year > (int)date('Y'))
      $errors[] = 'The year cannot be after ' . date('Y') . '.';

   //a picture can be uploaded (task 6) or it can already be in the images folder
   $uploading = uploadWasSent('imageFile');
   if ($uploading) {
      $uploadError = checkUpload('imageFile');
      if ($uploadError != '') $errors[] = $uploadError;
      //no image name typed, so use the uploaded file's own name
      if ($imageName == '') $imageName = imageNameFromUpload('imageFile');
   }

   //the image name can only have letters, numbers, - and _ so nobody can sneak in a path
   if ($imageName == '')
      $errors[] = 'Please enter the image file name, or upload a picture.';
   else if (strlen($imageName) > 50 || !preg_match('/^[A-Za-z0-9_-]+$/', $imageName))
      $errors[] = 'The image file name can only have letters, numbers, - and _ (leave off the .jpg), at most 50.';
   else if (imageNameTaken($imageName))
      $errors[] = 'Another painting already uses the image ' . $imageName . '.jpg.';
   else if ($uploading && file_exists($imageFolder . $imageName . '.jpg'))
      //never write over a picture that is already there
      $errors[] = 'There is already a picture named ' . $imageName . '.jpg in the images folder. Please type a different image file name.';
   else if (!$uploading && !file_exists($imageFolder . $imageName . '.jpg'))
      $errors[] = 'There is no picture named ' . $imageName . '.jpg in the images folder. Upload one with the box below.';

   //genre and gallery can be left blank, but if something is typed it has to be in the list
   $genreId = 0;
   if ($genreName != '') {
      $genreId = findIdByName('Genres', 'GenreID', 'GenreName', $genreName);
      if ($genreId == 0)
         $errors[] = 'There is no genre named ' . $genreName . '. Please pick one from the list.';
   }

   $galleryId = 0;
   if ($galleryName != '') {
      $galleryId = findIdByName('Galleries', 'GalleryID', 'GalleryName', $galleryName);
      if ($galleryId == 0)
         $errors[] = 'There is no gallery named ' . $galleryName . '. Please pick one from the list.';
   }

   //if medium is left blank use the same default the database uses
   if ($medium == '') $medium = 'Oil on canvas';

   //no problems so save it and go back to the main page where it shows up first
   //the picture is saved last, only when everything else is fine
   if (count($errors) == 0 && $uploading && !saveUpload('imageFile', $imageFolder, $imageName))
      $errors[] = 'The picture could not be saved into the images folder.';

   if (count($errors) == 0) {
      addPainting($title, $artistId, $artistName, $year, $medium, $imageName, $galleryId, $genreId);
      //tell the main page if a new artist was made too
      header('Location: artMain.php?added=yes' . ($artistId == 0 ? '&newArtist=yes' : ''));
      exit;
   }
}

?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Add Artwork</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
   <?php //the ?v= number changes when the file changes, so browsers dont keep an old copy ?>
   <link href="css/combobox.css?v=<?php echo filemtime("css/combobox.css"); ?>" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php //the tab bar at the top ?>
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Add Artwork</h1>
         <p>Fill in the painting details and click ADD. Upload a .jpg picture, or type the name of one already in the images folder.</p>
      </div>

      <?php //red box that lists every problem with what was typed ?>
      <?php if (count($errors) > 0) { ?>
         <div class="ui negative message">
            <div class="header">The painting was not added:</div>
            <ul class="list">
               <?php foreach ($errors as $error) { ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
               <?php } ?>
            </ul>
         </div>
      <?php } ?>

      <?php //the add form, the values put back what the user typed if there was a mistake ?>
      <div class="ui segment">
         <?php //enctype multipart is needed so the form can send a file (task 6), data-check-form turns on js/formCheck.js (task 7a) ?>
         <form class="ui form" method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" enctype="multipart/form-data" data-check-form>
            <div class="required field">
               <label>Title</label>
               <input type="text" name="title" maxlength="255" required placeholder="for example The Starry Night" value="<?php echo htmlspecialchars($title); ?>">
            </div>
            <div class="required field">
               <label>Artist</label>
               <?php //a type-in dropdown, a name that isnt in the list gets an "Add new artist" row ?>
               <?php outputComboBox('artistName', $artistName, 'Start typing, for example Vincent Van Gogh',
                                    artistComboItems(), array('newLabel' => 'Add new artist', 'required' => true)); ?>
               <small>Pick an artist from the list, or type a new name to add that artist.
               For more details (nationality, years) use the <a href="addArtist.php">Add Artist</a> tab first.</small>
            </div>
            <div class="required field">
               <label>Year</label>
               <input type="text" name="year" inputmode="numeric" maxlength="4" required data-check="year" placeholder="for example 1889" value="<?php echo htmlspecialchars($year); ?>">
            </div>
            <div class="field">
               <label>Medium</label>
               <input type="text" name="medium" maxlength="255" placeholder="Oil on canvas" value="<?php echo htmlspecialchars($medium); ?>">
            </div>
            <div class="field">
               <label>Genre</label>
               <?php outputComboBox('genreName', $genreName, 'Optional, for example Baroque', genreComboItems()); ?>
            </div>
            <div class="field">
               <label>Gallery</label>
               <?php outputComboBox('galleryName', $galleryName, 'Optional, for example National Gallery', galleryComboItems()); ?>
            </div>
            <div class="field">
               <label>Upload a picture (.jpg, at most 2 MB)</label>
               <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_UPLOAD_BYTES; ?>">
               <input type="file" name="imageFile" accept=".jpg,.jpeg,image/jpeg" data-check="jpg">
               <small>Leave this empty if the picture is already in the images folder.</small>
            </div>
            <div class="field">
               <label>Image file name (without .jpg)</label>
               <input type="text" name="imageName" maxlength="50" data-check="imagename" placeholder="for example 019070" value="<?php echo htmlspecialchars($imageName); ?>">
               <small>Letters, numbers, - and _ only. If you upload a picture and leave this empty, the uploaded file's name is used.</small>
            </div>
            <input class="ui primary button" type="submit" name="add" value="ADD">
         </form>
      </div>
   </main>
   <script src="js/combobox.js?v=<?php echo filemtime("js/combobox.js"); ?>"></script>
   <script src="js/formCheck.js?v=<?php echo filemtime("js/formCheck.js"); ?>"></script>
</body>
</html>
