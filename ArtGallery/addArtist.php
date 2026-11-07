<?php
//addArtist.php is the page for adding a new artist to the artists table
//the name boxes show the artists already saved as you type so the same artist isnt added twice

//this makes sure someone is logged in, if not it sends them to the login page
require 'checkLogin.php';

//this has the database name, user and password
require_once('dbConfig.php');

//shared artist functions (artist list, finding and adding artists)
require_once('artistHelpers.php');

//prints a <datalist> of every nationality already in the artists table
//so the nationality box is a dropdown you can also type a new one into
function outputNationalityDatalist($listId) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "select distinct Nationality from Artists where Nationality is not null and Nationality <> '' order by Nationality";
      $result = $pdo->query($sql);
      echo '<datalist id="' . htmlspecialchars($listId) . '">';
      while ($row = $result->fetch()) {
         echo '<option value="' . htmlspecialchars($row['Nationality']) . '">';
      }
      echo '</datalist>';
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//these hold what the user typed so the form can show it again if there is a mistake
$firstName = '';
$lastName = '';
$nationality = '';
$gender = '';
$yearOfBirth = '';
$yearOfDeath = '';
$errors = array();

//this runs when the ADD ARTIST button is clicked
if (isset($_POST['addArtist'])) {
   //trim takes off extra spaces, preg_replace squashes double spaces inside names
   $firstName = preg_replace('/\s+/', ' ', trim($_POST['firstName']));
   $lastName = preg_replace('/\s+/', ' ', trim($_POST['lastName']));
   $nationality = trim($_POST['nationality']);
   $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
   $yearOfBirth = trim($_POST['yearOfBirth']);
   $yearOfDeath = trim($_POST['yearOfDeath']);

   //checks everything before saving, each problem gets added to $errors
   if ($lastName == '')
      $errors[] = 'Please enter a last name (for one-name artists like Rembrandt, put it here).';
   else if (strlen($lastName) > 50)
      $errors[] = 'The last name can be at most 50 characters.';

   if (strlen($firstName) > 50)
      $errors[] = 'The first name can be at most 50 characters.';

   if (strlen($nationality) > 50)
      $errors[] = 'The nationality can be at most 50 characters.';

   if ($gender != '' && $gender != 'M' && $gender != 'F')
      $errors[] = 'Please choose a gender from the list.';

   if ($yearOfBirth != '' && (!ctype_digit($yearOfBirth) || strlen($yearOfBirth) > 4))
      $errors[] = 'Please enter the year of birth as a number, like 1853.';

   if ($yearOfDeath != '' && (!ctype_digit($yearOfDeath) || strlen($yearOfDeath) > 4))
      $errors[] = 'Please enter the year of death as a number, like 1890.';

   if ($yearOfBirth != '' && $yearOfDeath != '' && ctype_digit($yearOfBirth) && ctype_digit($yearOfDeath)
       && (int)$yearOfDeath < (int)$yearOfBirth)
      $errors[] = 'The year of death cannot be before the year of birth.';

   //stops the same artist from being added twice
   if ($lastName != '' && findArtistIdByName(artistDisplayName($firstName, $lastName)) > 0)
      $errors[] = artistDisplayName($firstName, $lastName) . ' is already in the artists list.';

   //no problems so save it and show the new artist on the artists page
   if (count($errors) == 0) {
      try {
         $newId = insertArtist(null, $firstName, $lastName, $nationality, $gender, $yearOfBirth, $yearOfDeath);
      }
      catch (PDOException $e) {
         die( $e->getMessage() );
      }
      header('Location: artistPaintings.php?id=' . $newId . '&added=yes');
      exit;
   }
}

?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Add Artist</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php //the tab bar at the top ?>
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Add Artist</h1>
         <p>Type the artist's name. Artists already in the gallery show up as you type, so you can see if they are there already.</p>
      </div>

      <?php //red box that lists every problem with what was typed ?>
      <?php if (count($errors) > 0) { ?>
         <div class="ui negative message">
            <div class="header">The artist was not added:</div>
            <ul class="list">
               <?php foreach ($errors as $error) { ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
               <?php } ?>
            </ul>
         </div>
      <?php } ?>

      <div class="ui segment">
         <form class="ui form" method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
            <?php //this box only helps you check, the name is saved from the two boxes below it ?>
            <div class="field">
               <label>Check the existing artists</label>
               <input type="text" id="artistCheck" list="artistList" autocomplete="off"
                      placeholder="Start typing a name to see if the artist is already here">
               <?php outputArtistDatalist('artistList'); ?>
            </div>
            <div class="two fields">
               <div class="field">
                  <label>First name</label>
                  <input type="text" name="firstName" placeholder="for example Frida" value="<?php echo htmlspecialchars($firstName); ?>">
               </div>
               <div class="required field">
                  <label>Last name</label>
                  <input type="text" name="lastName" placeholder="for example Kahlo" value="<?php echo htmlspecialchars($lastName); ?>">
               </div>
            </div>
            <div class="two fields">
               <div class="field">
                  <label>Nationality</label>
                  <?php //pick a nationality already used, or type a new one ?>
                  <input type="text" name="nationality" list="nationalityList" autocomplete="off"
                         placeholder="for example Mexico" value="<?php echo htmlspecialchars($nationality); ?>">
                  <?php outputNationalityDatalist('nationalityList'); ?>
               </div>
               <div class="field">
                  <label>Gender</label>
                  <select name="gender">
                     <option value="">Not given</option>
                     <option value="F" <?php if ($gender == 'F') echo 'selected'; ?>>Female</option>
                     <option value="M" <?php if ($gender == 'M') echo 'selected'; ?>>Male</option>
                  </select>
               </div>
            </div>
            <div class="two fields">
               <div class="field">
                  <label>Year of birth</label>
                  <input type="text" name="yearOfBirth" placeholder="for example 1907" value="<?php echo htmlspecialchars($yearOfBirth); ?>">
               </div>
               <div class="field">
                  <label>Year of death</label>
                  <input type="text" name="yearOfDeath" placeholder="for example 1954" value="<?php echo htmlspecialchars($yearOfDeath); ?>">
               </div>
            </div>
            <input class="ui primary button" type="submit" name="addArtist" value="ADD ARTIST">
         </form>
      </div>
   </main>
</body>
</html>
