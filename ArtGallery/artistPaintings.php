<?php
require_once 'auth.php';

require_once('dbConfig.php');

require_once('artistHelpers.php');

require_once('combobox.php');

//the find box sends a name, turn it into an id and jump to that artist
$notFound = '';
if (isset($_GET['artist']) && trim($_GET['artist']) != '') {
   $foundId = findArtistIdByName($_GET['artist']);
   if ($foundId > 0) {
      header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?id=' . $foundId);
      exit;
   }
   $notFound = trim($_GET['artist']);
}

//(int) turns anything that isnt a number into 0
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$artist = $id > 0 ? getArtist($id) : false;

function outputArtists($id) {
   try {
         $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         //no limit, it used to stop at 30 so later artists were missing
         $sql = "select ArtistID, LastName from Artists order by LastName";
         $result = $pdo->query($sql);
         while ($row = $result->fetch()) {
            echo '<a href="' . $_SERVER["SCRIPT_NAME"] . '?id=' . $row['ArtistID'] . '" class="';
            if ($id == $row['ArtistID']) echo 'active ';
            echo 'item">';
            echo htmlspecialchars($row['LastName']) . '</a>';
         }
         $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

function outputPaintings($id) {
   try {
      if ($id > 0) {
         $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

         $sql = 'select * from Paintings where ArtistId=:id order by YearOfWork, Title';
         $statement = $pdo->prepare($sql);
         $statement->bindValue(':id', $id, PDO::PARAM_INT);
         $statement->execute();

         $any = false;
         while ($row = $statement->fetch()) {
            outputSinglePainting($row);
            $any = true;
         }
         if (!$any) echo '<p><em>No paintings by this artist yet.</em></p>';
         $pdo = null;
      }
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

function outputSinglePainting($row) {
   $link = 'paintingDetail.php?id=' . $row['PaintingID'];

   echo '<div class="item">';
   echo '<a class="image" href="' . $link . '">';
   echo '<img  src="images/art/works/square-medium/' . htmlspecialchars($row['ImageFileName']) .'.jpg" alt="' . htmlspecialchars($row['Title']) . '">';
   echo '</a>';
   echo '<div class="content">';
   echo '<a class="header" href="' . $link . '"><h4>';
   echo htmlspecialchars($row['Title']);
   if ($row['YearOfWork']) echo ' <span class="year">(' . $row['YearOfWork'] . ')</span>';
   echo '</h4></a>';
   echo '<p class="description">';
   echo htmlspecialchars((string)$row['Excerpt']);
   echo '</p>';
   echo '</div>';  //end class=content
   echo '</div>';  //end class=item
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
   <?php //?v= changes when the file changes so the browser doesnt use an old copy ?>
   <link href="css/combobox.css?v=<?php echo filemtime("css/combobox.css"); ?>" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>
      <div class="ui secondary segment">
         <h1>Artists</h1>
      </div>
      <?php if (isset($_GET['added'])) { ?>
         <div class="ui positive message">The new artist was added. They have no paintings yet, use the Add Artwork tab to add one.</div>
      <?php } ?>
      <?php if (isset($_GET['updated'])) { ?>
         <div class="ui positive message">The changes to this artist were saved.</div>
      <?php } ?>

      <?php if ($notFound != '') { ?>
         <div class="ui warning message">No artist named <?php echo htmlspecialchars($notFound); ?> was found.
            <?php if (isAdmin()) { ?>You can add them on the <a href="addArtist.php">Add Artist</a> tab.<?php } ?></div>
      <?php } ?>

      <div class="ui segment">
         <form class="ui form" method="get" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
            <div class="combo-row">
               <div class="combo-row-box">
                  <?php //picking a name submits the form right away ?>
                  <?php outputComboBox('artist', '', 'Type an artist\'s name, for example Vincent Van Gogh',
                                       artistComboItems(), array('submitOnPick' => true)); ?>
               </div>
               <input class="ui primary button" type="submit" value="FIND ARTIST">
            </div>
         </form>
      </div>

      <div class="ui segment">
         <?php //stackable puts the list above the paintings on a phone ?>
         <div class="ui stackable grid">
           <div class="four wide column">
              <div class="ui link list">
                 <?php outputArtists($id); ?>
              </div>
           </div>
           <div class="twelve wide column">
              <?php if ($artist) { ?>
                 <div class="artist-info">
                    <h2><?php echo htmlspecialchars(artistDisplayName($artist['FirstName'], $artist['LastName'])); ?></h2>
                    <div class="artist-facts">
                       <?php
                       $facts = array();
                       if ($artist['Nationality'] != '') $facts[] = htmlspecialchars($artist['Nationality']);
                       if ($artist['YearOfBirth'] || $artist['YearOfDeath'])
                          $facts[] = ($artist['YearOfBirth'] ? $artist['YearOfBirth'] : '?') . ' &ndash; ' . ($artist['YearOfDeath'] ? $artist['YearOfDeath'] : '?');
                       echo implode(' &middot; ', $facts);
                       ?>
                    </div>
                    <?php if ($artist['Details'] != '') { ?>
                       <p><?php echo nl2br(htmlspecialchars($artist['Details'])); ?></p>
                    <?php } ?>
                    <?php if ($artist['ArtistLink'] != '') { ?>
                       <a class="ui labeled icon button" href="<?php echo htmlspecialchars($artist['ArtistLink']); ?>"><i class="external icon"></i> Read more</a>
                    <?php } ?>
                    <?php if (isAdmin()) { ?>
                       <a class="ui primary button" href="editArtist.php?id=<?php echo $artist['ArtistID']; ?>">Edit artist</a>
                    <?php } ?>
                 </div>
              <?php } else if ($id > 0) { ?>
                 <div class="ui negative message">That artist could not be found.</div>
              <?php } else { ?>
                 <div class="artist-facts"><em>Pick an artist from the list or type a name above to see their paintings.</em></div>
              <?php } ?>
              <div class="ui items">
                 <?php outputPaintings($artist ? $id : 0); ?>
              </div>
           </div>
         </div>
      </div>
   </main>
   <script src="js/combobox.js?v=<?php echo filemtime("js/combobox.js"); ?>"></script>

</body>
</html>
