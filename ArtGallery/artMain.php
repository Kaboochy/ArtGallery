<?php
//artMain.php is the main page of the art gallery (part 3)
//it shows every painting as a card and each card has a delete button

//this makes sure someone is logged in, if not it sends them to the login page
require 'checkLogin.php';

//this has the database name, user and password
require_once('dbConfig.php');

//deletes a painting and everything in other tables that points to it
//the transaction makes all the deletes happen together or none at all
function deletePainting($id) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      //related tables go first and the painting itself goes last
      $sqlList = array(
         'delete from PaintingGenres where PaintingID=:id',
         'delete from PaintingSubjects where PaintingID=:id',
         'delete from Reviews where PaintingID=:id',
         'delete from OrderDetails where PaintingID=:id',
         'delete from Paintings where PaintingID=:id'
      );

      $pdo->beginTransaction();
      foreach ($sqlList as $sql) {
         $statement = $pdo->prepare($sql);
         $statement->bindValue(':id', $id);
         $statement->execute();
      }
      $pdo->commit();
      $pdo = null;
   }
   catch (PDOException $e) {
      //if something went wrong undo any deletes that already happened
      if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
      die( $e->getMessage() );
   }
}

//shows how many paintings are in the collection
function outputPaintingCount() {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'select count(*) as total from Paintings';
      $result = $pdo->query($sql);
      $row = $result->fetch();
      echo $row['total'];
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//only title and year are allowed, anything else turns into newest
//this way nothing typed in the address bar can get into the sql
function cleanSortChoice($value) {
   if ($value == 'title' || $value == 'year')
      return $value;
   return 'newest';
}

//shows the choices in the sort dropdown and marks the one being used
function outputSortOptions($sort) {
   $choices = array(
      'newest' => 'Newest first',
      'title'  => 'Title (A to Z)',
      'year'   => 'Year (oldest first)'
   );
   foreach ($choices as $value => $label) {
      echo '<option value="' . $value . '"';
      if ($value == $sort) echo ' selected ';
      echo '>' . $label . '</option>';
   }
}

//shows every painting in the order picked in the sort dropdown
function outputPaintings($sort) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      //newest paintings have the highest ids
      if ($sort == 'title')
         $orderBy = 'Paintings.Title';
      else if ($sort == 'year')
         $orderBy = 'Paintings.YearOfWork, Paintings.Title';
      else
         $orderBy = 'Paintings.PaintingID desc';

      //the join gets the artist name from the artists table
      $sql = 'select Paintings.PaintingID, Paintings.Title, Paintings.ImageFileName,
                     Paintings.YearOfWork, Artists.FirstName, Artists.LastName
              from Paintings
              left join Artists on Paintings.ArtistID = Artists.ArtistID
              order by ' . $orderBy;
      $result = $pdo->query($sql);
      while ($row = $result->fetch()) {
         outputSinglePainting($row, $sort);
      }
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//shows one painting card with its picture, title, artist, year and delete button
//the sort is passed along so the page keeps the same order after a delete
function outputSinglePainting($row, $sort) {
   //some artists dont have a first name (like Rembrandt)
   if ($row['FirstName'] != '')
      $artist = $row['FirstName'] . ' ' . $row['LastName'];
   else
      $artist = $row['LastName'];

   //the picture and title link to the painting's detail page
   $link = 'paintingDetail.php?id=' . $row['PaintingID'];

   echo '<div class="card">';
   echo '<a class="image" href="' . $link . '">';
   echo '<img src="images/art/works/square-medium/' . $row['ImageFileName'] . '.jpg"';
   echo ' alt="' . htmlspecialchars($row['Title']) . '">';
   echo '</a>';
   echo '<div class="content">';
   echo '<a class="header" href="' . $link . '">' . htmlspecialchars($row['Title']) . '</a>';
   echo '<div class="meta">' . htmlspecialchars($artist) . '</div>';
   echo '<div class="description">' . $row['YearOfWork'] . '</div>';
   echo '</div>';  // end class=content

   //the delete form, it asks to confirm before it sends
   echo '<div class="extra content">';
   echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '"';
   echo ' onsubmit="return confirm(\'Delete this painting and everything related to it?\');">';
   echo '<input type="hidden" name="delete" value="yes">';
   echo '<input type="hidden" name="paintingId" value="' . $row['PaintingID'] . '">';
   echo '<input type="hidden" name="sort" value="' . $sort . '">';
   echo '<input class="ui red basic fluid button" type="submit" value="Delete">';
   echo '</form>';
   echo '</div>';  // end class=extra content
   echo '</div>';  // end class=card
}

//this runs when a delete button is clicked, then it reloads the page
//reloading stops a refresh from sending the delete again
if (isset($_POST['delete']) && isset($_POST['paintingId'])) {
   deletePainting($_POST['paintingId']);

   $sort = 'newest';
   if (isset($_POST['sort'])) $sort = cleanSortChoice($_POST['sort']);

   header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?sort=' . $sort . '&deleted=yes');
   exit;
}

//the sort order picked in the dropdown (newest first if nothing was picked)
$sort = 'newest';
if (isset($_GET['sort'])) $sort = cleanSortChoice($_GET['sort']);

?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery</title>
   <link href="semantic/semantic.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php //the tab bar at the top ?>
      <?php include 'nav.php'; ?>

      <?php //the heading, painting count and sort dropdown ?>
      <div class="ui secondary segment">
         <h1>Artworks</h1>
         <p><?php outputPaintingCount(); ?> paintings in the collection</p>
         <form method="get" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
            Sort by:
            <select name="sort">
               <?php outputSortOptions($sort); ?>
            </select>
            <input class="ui button" type="submit" value="Sort">
         </form>
      </div>

      <?php //green messages after a delete or an add ?>
      <?php if (isset($_GET['deleted'])) { ?>
         <div class="ui positive message">The painting and everything related to it were deleted.</div>
      <?php } ?>
      <?php if (isset($_GET['added'])) { ?>
         <div class="ui positive message">The new painting was added. It is the first card below.</div>
      <?php } ?>

      <?php //all the painting cards ?>
      <div class="ui segment">
         <div class="ui four doubling cards">
            <?php outputPaintings($sort); ?>
         </div>
      </div>
   </main>

</body>
</html>
