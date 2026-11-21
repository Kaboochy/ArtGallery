<?php
//only admins can add artists
require 'checkAdmin.php';

require_once('dbConfig.php');

require_once('artistHelpers.php');

require_once('combobox.php');

//kept so the form can show what was typed if there is an error
$firstName = '';
$lastName = '';
$nationality = '';
$gender = '';
$yearOfBirth = '';
$yearOfDeath = '';
$errors = array();

//runs when add artist is clicked
if (isset($_POST['addArtist'])) {
   //squash double spaces inside names
   $firstName = preg_replace('/\s+/', ' ', trim($_POST['firstName']));
   $lastName = preg_replace('/\s+/', ' ', trim($_POST['lastName']));
   $nationality = trim($_POST['nationality']);
   $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
   $yearOfBirth = trim($_POST['yearOfBirth']);
   $yearOfDeath = trim($_POST['yearOfDeath']);

   //each problem is added to $errors
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

   //years in the future are not allowed
   if (ctype_digit($yearOfBirth) && (int)$yearOfBirth > (int)date('Y'))
      $errors[] = 'The year of birth cannot be after ' . date('Y') . '.';
   if (ctype_digit($yearOfDeath) && (int)$yearOfDeath > (int)date('Y'))
      $errors[] = 'The year of death cannot be after ' . date('Y') . '.';

   //stop the same artist being added twice
   if ($lastName != '' && findArtistIdByName(artistDisplayName($firstName, $lastName)) > 0)
      $errors[] = artistDisplayName($firstName, $lastName) . ' is already in the artists list.';

   //no errors so save and show the new artist
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
   <?php //?v= changes when the file changes so the browser doesnt use an old copy ?>
   <link href="css/combobox.css?v=<?php echo filemtime("css/combobox.css"); ?>" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Add Artist</h1>
         <p>Type the artist's name. Artists already in the gallery show up as you type, so you can see if they are there already.</p>
      </div>

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
         <?php //data-check-form turns on js/formCheck.js ?>
         <form class="ui form" method="post" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" data-check-form>
            <?php //only for checking, the name is saved from the two boxes below ?>
            <div class="field">
               <label>Check the existing artists</label>
               <?php outputComboBox('artistCheck', '', 'Start typing a name to see if the artist is already here',
                                    artistComboItems(), array('id' => 'artistCheck')); ?>
            </div>
            <div class="two fields">
               <div class="field">
                  <label>First name</label>
                  <input type="text" name="firstName" maxlength="50" placeholder="for example Frida" value="<?php echo htmlspecialchars($firstName); ?>">
               </div>
               <div class="required field">
                  <label>Last name</label>
                  <input type="text" name="lastName" maxlength="50" required placeholder="for example Kahlo" value="<?php echo htmlspecialchars($lastName); ?>">
               </div>
            </div>
            <div class="two fields">
               <div class="field">
                  <label>Nationality</label>
                  <?php outputComboBox('nationality', $nationality, 'for example Mexico',
                                       nationalityComboItems(), array('newLabel' => 'Use new nationality')); ?>
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
                  <input type="text" name="yearOfBirth" inputmode="numeric" maxlength="4" data-check="year" placeholder="for example 1907" value="<?php echo htmlspecialchars($yearOfBirth); ?>">
               </div>
               <div class="field">
                  <label>Year of death</label>
                  <input type="text" name="yearOfDeath" inputmode="numeric" maxlength="4" data-check="year" data-after="yearOfBirth" placeholder="for example 1954" value="<?php echo htmlspecialchars($yearOfDeath); ?>">
               </div>
            </div>
            <input class="ui primary button" type="submit" name="addArtist" value="ADD ARTIST">
         </form>
      </div>
   </main>
   <script src="js/combobox.js?v=<?php echo filemtime("js/combobox.js"); ?>"></script>
   <script src="js/formCheck.js?v=<?php echo filemtime("js/formCheck.js"); ?>"></script>
</body>
</html>
