<?php
//addPainting.php is the page for adding a new painting (part 3 bonus)
//it has a form and an ADD button that saves the painting into the paintings table

//this makes sure someone is logged in, if not it sends them to the login page
require 'checkLogin.php';

//this has the database name, user and password
require_once('dbConfig.php');

//shared artist functions (the type-in artist dropdown, finding and adding artists)
require_once('artistHelpers.php');

//the folder where all the painting pictures are kept
$imageFolder = 'images/art/works/square-medium/';

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

//saves the new painting into the paintings table
//every other column (cost, price, shape and so on) uses the database default
//if $artistId is 0 the typed artist name is new, so the artist is added first
//both inserts are in one transaction so a failed painting never leaves a stray artist behind
function addPainting($title, $artistId, $newArtistName, $year, $medium, $imageName) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $pdo->beginTransaction();

      if ($artistId == 0) {
         list($firstName, $lastName) = splitArtistName($newArtistName);
         $artistId = insertArtist($pdo, $firstName, $lastName);
      }

      $sql = 'insert into Paintings (ArtistID, ImageFileName, Title, YearOfWork, Medium)
              values (:artist, :image, :title, :year, :medium)';
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':artist', $artistId);
      $statement->bindValue(':image', $imageName);
      $statement->bindValue(':title', $title);
      $statement->bindValue(':year', $year);
      $statement->bindValue(':medium', $medium);
      $statement->execute();

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

   if (!ctype_digit($year) || strlen($year) > 4)
      $errors[] = 'Please enter the year as a number, like 1889.';

   //the image name can only have letters, numbers, - and _ so nobody can sneak in a path
   if ($imageName == '')
      $errors[] = 'Please enter the image file name.';
   else if (!preg_match('/^[A-Za-z0-9_-]+$/', $imageName))
      $errors[] = 'The image file name can only have letters, numbers, - and _ (leave off the .jpg).';
   else if (!file_exists($imageFolder . $imageName . '.jpg'))
      $errors[] = 'There is no picture named ' . $imageName . '.jpg in the images folder.';
   else if (imageNameTaken($imageName))
      $errors[] = 'Another painting already uses the image ' . $imageName . '.jpg.';

   //if medium is left blank use the same default the database uses
   if ($medium == '') $medium = 'Oil on canvas';

   //no problems so save it and go back to the main page where it shows up first
   if (count($errors) == 0) {
      addPainting($title, $artistId, $artistName, $year, $medium, $imageName);
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
</head>
<body>
   <main class="ui container">
      <?php //the tab bar at the top ?>
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Add Artwork</h1>
         <p>Fill in the painting details and click ADD. The picture has to already be in the images folder.</p>
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
         <form class="ui form" method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
            <div class="field">
               <label>Title</label>
               <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
            </div>
            <div class="field">
               <label>Artist</label>
               <?php //list="artistList" turns this box into a dropdown you can type in ?>
               <input type="text" name="artistName" list="artistList" autocomplete="off"
                      placeholder="Start typing, for example Vincent Van Gogh"
                      value="<?php echo htmlspecialchars($artistName); ?>">
               <?php outputArtistDatalist('artistList'); ?>
               <small>Pick an artist from the list, or type a new name to add that artist.
               For more details (nationality, years) use the <a href="addArtist.php">Add Artist</a> tab first.</small>
            </div>
            <div class="field">
               <label>Year</label>
               <input type="text" name="year" placeholder="for example 1889" value="<?php echo htmlspecialchars($year); ?>">
            </div>
            <div class="field">
               <label>Medium</label>
               <input type="text" name="medium" placeholder="Oil on canvas" value="<?php echo htmlspecialchars($medium); ?>">
            </div>
            <div class="field">
               <label>Image file name (without .jpg)</label>
               <input type="text" name="imageName" placeholder="for example 019070" value="<?php echo htmlspecialchars($imageName); ?>">
            </div>
            <input class="ui primary button" type="submit" name="add" value="ADD">
         </form>
      </div>
   </main>
</body>
</html>
