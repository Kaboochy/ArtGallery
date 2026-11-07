<?php
//this makes sure someone is logged in, if not it sends them to the login page
require 'checkLogin.php';

require_once('dbConfig.php'); 

//shared artist functions (the type-in artist dropdown and finding an artist by name)
require_once('artistHelpers.php');

//the themed type-in dropdown
require_once('combobox.php');

//the find box sends the typed name, turn it into the artist id and show that artist
$notFound = '';
if (isset($_GET['artist']) && trim($_GET['artist']) != '') {
   $foundId = findArtistIdByName($_GET['artist']);
   if ($foundId > 0) {
      header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?id=' . $foundId);
      exit;
   }
   $notFound = trim($_GET['artist']);
}


/*
 Displays the list of artist links on the left-side of page
*/
function outputArtists() {
   try {
         $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $sql = "select * from Artists order by LastName limit 0,30";
         $result = $pdo->query($sql);
         while ($row = $result->fetch()) {
            echo '<a href="' . $_SERVER["SCRIPT_NAME"] . '?id=' . $row['ArtistID'] . '" class="';
            if (isset($_GET['id']) && $_GET['id'] == $row['ArtistID']) echo 'active ';
            echo 'item">';
            echo $row['LastName'] . '</a>';
         }
         $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

/*
 Displays the list of paintings for the artist id specified in the id query string
*/
function outputPaintings() {
   try {
      if (isset($_GET['id']) && $_GET['id'] > 0) {   
         $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         
         $sql = 'select * from Paintings where ArtistId=:id';
         $id =  $_GET['id'];
         $statement = $pdo->prepare($sql);
         $statement->bindValue(':id', $id);
         $statement->execute();

         while ($row = $statement->fetch()) {
            outputSinglePainting($row);         
         }
         $pdo = null;
      }
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

/*
 Displays a single painting
*/
function outputSinglePainting($row) {
   //the picture and title link to the painting's detail page
   $link = 'paintingDetail.php?id=' . $row['PaintingID'];

   echo '<div class="item">';
   echo '<a class="image" href="' . $link . '">';
   echo '<img  src="images/art/works/square-medium/' . $row['ImageFileName'] .'.jpg">';
   echo '</a>';
   echo '<div class="content">';
   echo '<a class="header" href="' . $link . '"><h4>';
   echo $row['Title'];
   echo '</h4></a>';
   echo '<p class="description">';
   echo $row['Excerpt'];
   echo '</p>';
   echo '</div>';  // end class=content
   echo '</div>';  // end class=item
}

?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Artists</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
   <?php //the ?v= number changes when the file changes, so browsers dont keep an old copy ?>
   <link href="css/combobox.css?v=<?php echo filemtime("css/combobox.css"); ?>" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>
      <div class="ui secondary segment">
         <h1>Artists</h1>
      </div>
      <?php //green box after a new artist is added on the Add Artist tab ?>
      <?php if (isset($_GET['added'])) { ?>
         <div class="ui positive message">The new artist was added. They have no paintings yet, use the Add Artwork tab to add one.</div>
      <?php } ?>

      <?php if ($notFound != '') { ?>
         <div class="ui warning message">No artist named <?php echo htmlspecialchars($notFound); ?> was found.
            You can add them on the <a href="addArtist.php">Add Artist</a> tab.</div>
      <?php } ?>

      <?php //type an artist's name (or pick it from the dropdown) to jump to their paintings ?>
      <div class="ui segment">
         <form class="ui form" method="get" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
            <?php //the box takes up the row and the button sits at its right end ?>
            <div class="combo-row">
               <div class="combo-row-box">
                  <?php //picking a name from the list goes straight to that artist ?>
                  <?php outputComboBox('artist', '', 'Type an artist\'s name, for example Vincent Van Gogh',
                                       artistComboItems(), array('submitOnPick' => true)); ?>
               </div>
               <input class="ui primary button" type="submit" value="FIND ARTIST">
            </div>
         </form>
      </div>

      <div class="ui segment">
         <div class="ui grid">
           <div class="four wide column">
              <div class="ui link list">
                 <?php outputArtists(); ?>
              </div>
           </div>
           <div class="twelve wide column">
              <div class="ui items">
                 <?php outputPaintings(); ?> 
              </div>
           </div>
         </div>
      </div>
   </main>
   <script src="js/combobox.js?v=<?php echo filemtime("js/combobox.js"); ?>"></script>

</body>
</html>