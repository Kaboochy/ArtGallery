<?php
//editArtist.php changes the information of one artist (task 1)
//the Edit artist button on the artists page links here with editArtist.php?id=
//the ArtistID is the primary key so it is only shown, it can never be changed
//the fields that can be changed: first and last name, nationality, gender, birth and death years, biography and link

//only admins (staff) can change artists (task 10)
require 'checkAdmin.php';
require_once('dbConfig.php');
require_once('artistHelpers.php');
require_once('combobox.php');
require_once('paintingHelpers.php');

//(int) turns anything that isnt a number into 0 so it cant break the sql
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (isset($_POST['artistId'])) $id = (int)$_POST['artistId'];

$artist = getArtist($id);
$errors = array();

if ($artist) {
   //the form starts with what is saved now, a 0 year means the year isnt known so the box starts empty
   $firstName = (string)$artist['FirstName'];
   $lastName = (string)$artist['LastName'];
   $nationality = (string)$artist['Nationality'];
   $gender = (string)$artist['Gender'];
   $yearOfBirth = $artist['YearOfBirth'] ? (string)$artist['YearOfBirth'] : '';
   $yearOfDeath = $artist['YearOfDeath'] ? (string)$artist['YearOfDeath'] : '';
   $details = (string)$artist['Details'];
   $artistLink = (string)$artist['ArtistLink'];

   //this runs when SAVE CHANGES is clicked
   if (isset($_POST['save'])) {
      $firstName = preg_replace('/\s+/', ' ', trim($_POST['firstName']));
      $lastName = preg_replace('/\s+/', ' ', trim($_POST['lastName']));
      $nationality = trim($_POST['nationality']);
      $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
      $yearOfBirth = trim($_POST['yearOfBirth']);
      $yearOfDeath = trim($_POST['yearOfDeath']);
      $details = trim($_POST['details']);
      $artistLink = trim($_POST['artistLink']);

      //server side checks (task 7b), the same ones as the Add Artist page
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
      else if ($yearOfBirth != '' && (int)$yearOfBirth > (int)date('Y'))
         $errors[] = 'The year of birth cannot be after ' . date('Y') . '.';

      if ($yearOfDeath != '' && (!ctype_digit($yearOfDeath) || strlen($yearOfDeath) > 4))
         $errors[] = 'Please enter the year of death as a number, like 1890.';
      else if ($yearOfDeath != '' && (int)$yearOfDeath > (int)date('Y'))
         $errors[] = 'The year of death cannot be after ' . date('Y') . '.';

      //birth has to come before death
      if (ctype_digit($yearOfBirth) && ctype_digit($yearOfDeath) && (int)$yearOfDeath < (int)$yearOfBirth)
         $errors[] = 'The year of death cannot be before the year of birth.';

      if (strlen($details) > 10000)
         $errors[] = 'The biography can be at most 10000 characters.';
      if (!isValidLink($artistLink))
         $errors[] = 'The link has to start with http:// or https://';

      //the new name cant be the same as a different artist's name
      if ($lastName != '') {
         $sameName = findArtistIdByName(artistDisplayName($firstName, $lastName));
         if ($sameName > 0 && $sameName != $id)
            $errors[] = artistDisplayName($firstName, $lastName) . ' is already another artist in the list.';
      }

      //no problems so save with one prepared update, the where uses the id so only this artist changes
      if (count($errors) == 0) {
         try {
            $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = 'update Artists
                    set FirstName=:first, LastName=:last, Nationality=:nationality, Gender=:gender,
                        YearOfBirth=:born, YearOfDeath=:died, Details=:details, ArtistLink=:link
                    where ArtistID=:id';
            $statement = $pdo->prepare($sql);
            //blank values are saved as null, a blank year is saved as 0 like the rest of the table
            $statement->bindValue(':first', $firstName == '' ? null : $firstName);
            $statement->bindValue(':last', $lastName);
            $statement->bindValue(':nationality', $nationality == '' ? null : $nationality);
            $statement->bindValue(':gender', $gender == '' ? null : $gender);
            $statement->bindValue(':born', $yearOfBirth == '' ? 0 : (int)$yearOfBirth, PDO::PARAM_INT);
            $statement->bindValue(':died', $yearOfDeath == '' ? 0 : (int)$yearOfDeath, PDO::PARAM_INT);
            $statement->bindValue(':details', $details == '' ? null : $details);
            $statement->bindValue(':link', $artistLink == '' ? null : $artistLink);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            $pdo = null;
         }
         catch (PDOException $e) {
            die( $e->getMessage() );
         }
         header('Location: artistPaintings.php?id=' . $id . '&updated=yes');
         exit;
      }
   }
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Edit Artist</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
   <link href="css/combobox.css?v=<?php echo filemtime("css/combobox.css"); ?>" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <?php if (!$artist) { ?>
         <div class="ui secondary segment"><h1>Edit Artist</h1></div>
         <div class="ui negative message">That artist could not be found.</div>
         <a class="ui button" href="artistPaintings.php">Back to Artists</a>
      <?php } else { ?>
         <div class="ui secondary segment">
            <h1>Edit Artist</h1>
            <p>Change the details and click SAVE CHANGES. The artist ID cannot be changed.</p>
         </div>

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
            <form class="ui form" method="post" action="editArtist.php?id=<?php echo $id; ?>" data-check-form>
               <input type="hidden" name="artistId" value="<?php echo $id; ?>">
               <div class="field">
                  <label>Artist ID</label>
                  <input type="text" value="<?php echo $id; ?>" readonly class="readonly">
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
                     <?php outputComboBox('nationality', $nationality, 'for example Mexico', nationalityComboItems(), array('newLabel' => 'Use new nationality')); ?>
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
                     <small>Has to be the same as or after the year of birth.</small>
                  </div>
               </div>
               <div class="field">
                  <label>Biography</label>
                  <textarea name="details" rows="6" maxlength="10000" placeholder="A short biography of the artist."><?php echo htmlspecialchars($details); ?></textarea>
               </div>
               <div class="field">
                  <label>Link to more information</label>
                  <input type="text" name="artistLink" maxlength="255" data-check="url" placeholder="https://en.wikipedia.org/wiki/..." value="<?php echo htmlspecialchars($artistLink); ?>">
               </div>
               <input class="ui primary button" type="submit" name="save" value="SAVE CHANGES">
               <a class="ui button" href="artistPaintings.php?id=<?php echo $id; ?>">Cancel</a>
            </form>
         </div>
      <?php } ?>
   </main>
   <script src="js/combobox.js?v=<?php echo filemtime("js/combobox.js"); ?>"></script>
   <script src="js/formCheck.js?v=<?php echo filemtime("js/formCheck.js"); ?>"></script>
</body>
</html>
