<?php
//addPainting.php is the page for adding a new painting (part 3 bonus)
//it has a form and an ADD button that saves the painting into the paintings table

//this makes sure someone is logged in, if not it sends them to the login page
require 'checkLogin.php';

//this has the database name, user and password
require_once('dbConfig.php');

//the folder where all the painting pictures are kept
$imageFolder = 'images/art/works/square-medium/';

//shows every artist as a choice in the artist dropdown
//the one in $selectedId stays picked if the form has to be shown again
function outputArtistOptions($selectedId) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'select ArtistID, FirstName, LastName from Artists order by LastName, FirstName';
      $result = $pdo->query($sql);
      while ($row = $result->fetch()) {
         //some artists dont have a first name (like Rembrandt)
         if ($row['FirstName'] != '')
            $name = $row['LastName'] . ', ' . $row['FirstName'];
         else
            $name = $row['LastName'];

         echo '<option value="' . $row['ArtistID'] . '"';
         if ($row['ArtistID'] == $selectedId) echo ' selected ';
         echo '>' . htmlspecialchars($name) . '</option>';
      }
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
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

//saves the new painting into the paintings table
//every other column (cost, price, shape and so on) uses the database default
function addPainting($title, $artistId, $year, $medium, $imageName) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'insert into Paintings (ArtistID, ImageFileName, Title, YearOfWork, Medium)
              values (:artist, :image, :title, :year, :medium)';
      $statement = $pdo->prepare($sql);
      $statement->bindValue(':artist', $artistId);
      $statement->bindValue(':image', $imageName);
      $statement->bindValue(':title', $title);
      $statement->bindValue(':year', $year);
      $statement->bindValue(':medium', $medium);
      $statement->execute();
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//these hold what the user typed so the form can show it again if there is a mistake
$title = '';
$artistId = '';
$year = '';
$medium = '';
$imageName = '';
$errors = array();

//this runs when the ADD button is clicked
if (isset($_POST['add'])) {
   //trim takes off extra spaces at the start and end
   $title = trim($_POST['title']);
   $artistId = trim($_POST['artistId']);
   $year = trim($_POST['year']);
   $medium = trim($_POST['medium']);
   $imageName = trim($_POST['imageName']);

   //checks everything before saving, each problem gets added to $errors
   if ($title == '')
      $errors[] = 'Please enter a title.';
   else if (strlen($title) > 255)
      $errors[] = 'The title can be at most 255 characters.';

   if ($artistId == '' || !ctype_digit($artistId))
      $errors[] = 'Please choose an artist.';

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
      addPainting($title, $artistId, $year, $medium, $imageName);
      header('Location: artMain.php?added=yes');
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
               <select name="artistId">
                  <option value="">Choose an artist</option>
                  <?php outputArtistOptions($artistId); ?>
               </select>
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
