<?php
//artMain.php is the main page of the art gallery (part 3)
//it shows the paintings as cards, a page at a time
//task 2: sort by date added, title, artist or year, ascending or descending, and a summary of each table
//task 3: 20 paintings per page with page links under the cards
//task 4: a search box that suggests titles or artists while you type (js/search.js asks suggest.php)
//task 10: everyone can look, only admins see the edit and delete buttons

//starts the session so the page knows who is looking (anyone can open this page)
require_once 'auth.php';

//this has the database name, user and password
require_once('dbConfig.php');

//how many paintings show on one page
define('PER_PAGE', 20);

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

//the summary report (task 2): how many rows each main table has
function getTableCounts() {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $counts = array();
      foreach (array('Paintings', 'Artists', 'Galleries', 'Genres') as $table) {
         $row = $pdo->query('select count(*) as total from ' . $table)->fetch();
         $counts[$table] = (int)$row['total'];
      }
      $pdo = null;
      return $counts;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//only the sort keys in this list are allowed, anything else turns into "added"
//this way nothing typed in the address bar can get into the sql
//each key is the order by used for it, the extra columns break ties so the order never jumps around
$sortKeys = array(
   'added'  => array('label' => 'Date added', 'sql' => 'Paintings.PaintingID'),
   'title'  => array('label' => 'Title',      'sql' => 'Paintings.Title'),
   'artist' => array('label' => 'Artist',     'sql' => 'Artists.LastName, Artists.FirstName'),
   'year'   => array('label' => 'Year',       'sql' => 'Paintings.YearOfWork')
);

function cleanSortChoice($value, $sortKeys) {
   return isset($sortKeys[$value]) ? $value : 'added';
}

//asc or desc only, newest first is the normal order for "date added"
function cleanDirection($value, $sort) {
   if ($value == 'asc' || $value == 'desc') return $value;
   return $sort == 'added' ? 'desc' : 'asc';
}

//the search can look in the title or in the artist's name (task 4)
function cleanSearchField($value) {
   return $value == 'artist' ? 'artist' : 'title';
}

//builds the where part for the search, the words are only ever sent as a bound value
//the search matches the start of a title or of the artist's last (or full) name
function searchWhere($q, $field) {
   if ($q == '') return '';
   if ($field == 'artist')
      return " where (Artists.LastName like :q1 or concat_ws(' ', Artists.FirstName, Artists.LastName) like :q2)";
   return ' where Paintings.Title like :q1';
}

//% and _ mean "anything" in like, so they are escaped to mean themselves, then % is added to match the start
function likePrefix($q) {
   return addcslashes($q, '\\%_') . '%';
}

function bindSearch($statement, $q, $field) {
   if ($q == '') return;
   $statement->bindValue(':q1', likePrefix($q));
   if ($field == 'artist') $statement->bindValue(':q2', likePrefix($q));
}

//how many paintings match the search (all of them when there is no search)
function countPaintings($q, $field) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = 'select count(*) as total from Paintings
              left join Artists on Paintings.ArtistID = Artists.ArtistID' . searchWhere($q, $field);
      $statement = $pdo->prepare($sql);
      bindSearch($statement, $q, $field);
      $statement->execute();
      $row = $statement->fetch();
      $pdo = null;
      return (int)$row['total'];
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//shows one page of paintings in the order picked in the sort options
function outputPaintings($sortKeys, $state, $offset) {
   try {
      $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      //every column of the sort key gets the direction, then the title and id break ties
      $columns = explode(',', $sortKeys[$state['sort']]['sql']);
      $orderBy = array();
      foreach ($columns as $column) $orderBy[] = trim($column) . ' ' . $state['dir'];
      $orderBy[] = 'Paintings.Title';
      $orderBy[] = 'Paintings.PaintingID';

      //the join gets the artist name from the artists table
      $sql = 'select Paintings.PaintingID, Paintings.Title, Paintings.ImageFileName,
                     Paintings.YearOfWork, Artists.FirstName, Artists.LastName
              from Paintings
              left join Artists on Paintings.ArtistID = Artists.ArtistID'
              . searchWhere($state['q'], $state['field']) .
             ' order by ' . implode(', ', $orderBy) .
             ' limit :offset, :perPage';
      $statement = $pdo->prepare($sql);
      bindSearch($statement, $state['q'], $state['field']);
      $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
      $statement->bindValue(':perPage', PER_PAGE, PDO::PARAM_INT);
      $statement->execute();
      while ($row = $statement->fetch()) {
         outputSinglePainting($row, $state);
      }
      $pdo = null;
   }
   catch (PDOException $e) {
      die( $e->getMessage() );
   }
}

//makes a link back to this page that keeps the sort, search and page, with some of them changed
function pageLink($state, $changes = array()) {
   $params = array_merge($state, $changes);
   //leave out the values that are the same as normal so the links stay short
   if ($params['q'] == '') { unset($params['q']); unset($params['field']); }
   if ($params['page'] == 1) unset($params['page']);
   return 'artMain.php?' . http_build_query($params);
}

//shows one painting card with its picture, title, artist and year
//admins also get edit and delete buttons, the page state is passed along so the page stays the same after a delete
function outputSinglePainting($row, $state) {
   //some artists dont have a first name (like Rembrandt)
   if ($row['FirstName'] != '')
      $artist = $row['FirstName'] . ' ' . $row['LastName'];
   else
      $artist = $row['LastName'];

   //the picture and title link to the painting's detail page
   $link = 'paintingDetail.php?id=' . $row['PaintingID'];

   echo '<div class="card">';
   echo '<a class="image" href="' . $link . '">';
   echo '<img src="images/art/works/square-medium/' . htmlspecialchars($row['ImageFileName']) . '.jpg"';
   echo ' alt="' . htmlspecialchars($row['Title']) . '">';
   echo '</a>';
   echo '<div class="content">';
   echo '<a class="header" href="' . $link . '">' . htmlspecialchars($row['Title']) . '</a>';
   echo '<div class="meta">' . htmlspecialchars($artist) . '</div>';
   echo '<div class="description">' . $row['YearOfWork'] . '</div>';
   echo '</div>';  // end class=content

   if (isAdmin()) {
      //the edit link and the delete form, delete asks to confirm before it sends
      echo '<div class="extra content admin-buttons">';
      echo '<a class="ui basic fluid button" href="editPainting.php?id=' . $row['PaintingID'] . '">Edit</a>';
      echo '<form method="post" action="artMain.php"';
      echo ' onsubmit="return confirm(\'Delete this painting and everything related to it?\');">';
      echo '<input type="hidden" name="delete" value="yes">';
      echo '<input type="hidden" name="paintingId" value="' . $row['PaintingID'] . '">';
      echo '<input type="hidden" name="back" value="' . htmlspecialchars(pageLink($state)) . '">';
      echo '<input class="ui red basic fluid button" type="submit" value="Delete">';
      echo '</form>';
      echo '</div>';  // end class=extra content
   }
   echo '</div>';  // end class=card
}

//prints the page links: first, previous, the pages near this one, next and last
function outputPagination($state, $totalPages) {
   if ($totalPages <= 1) return;
   $page = $state['page'];
   echo '<nav class="ui pagination menu" aria-label="Pages">';
   if ($page > 1) {
      echo '<a class="item" href="' . htmlspecialchars(pageLink($state, array('page' => 1))) . '" aria-label="First page">&laquo;</a>';
      echo '<a class="item" href="' . htmlspecialchars(pageLink($state, array('page' => $page - 1))) . '" aria-label="Previous page">&lsaquo; Prev</a>';
   }
   //up to 2 pages on each side of this one
   $from = max(1, $page - 2);
   $to = min($totalPages, $page + 2);
   for ($n = $from; $n <= $to; $n++) {
      if ($n == $page)
         echo '<span class="active item" aria-current="page">' . $n . '</span>';
      else
         echo '<a class="item" href="' . htmlspecialchars(pageLink($state, array('page' => $n))) . '">' . $n . '</a>';
   }
   if ($page < $totalPages) {
      echo '<a class="item" href="' . htmlspecialchars(pageLink($state, array('page' => $page + 1))) . '" aria-label="Next page">Next &rsaquo;</a>';
      echo '<a class="item" href="' . htmlspecialchars(pageLink($state, array('page' => $totalPages))) . '" aria-label="Last page">&raquo;</a>';
   }
   echo '</nav>';
}

//this runs when a delete button is clicked, then it reloads the page
//reloading stops a refresh from sending the delete again
if (isset($_POST['delete']) && isset($_POST['paintingId'])) {
   //only admins can delete, anyone else is sent away
   if (!isAdmin()) {
      header('Location: artMain.php?notAllowed=yes');
      exit;
   }
   deletePainting((int)$_POST['paintingId']);

   //go back to the same page, only links to this page are allowed
   $back = 'artMain.php';
   if (isset($_POST['back']) && strpos($_POST['back'], 'artMain.php?') === 0) $back = $_POST['back'];
   header('Location: ' . $back . (strpos($back, '?') === false ? '?' : '&') . 'deleted=yes');
   exit;
}

//what is being shown: the sort, the direction, the search and the page
$state = array();
$state['sort'] = cleanSortChoice(isset($_GET['sort']) ? $_GET['sort'] : '', $sortKeys);
$state['dir'] = cleanDirection(isset($_GET['dir']) ? $_GET['dir'] : '', $state['sort']);
$state['q'] = isset($_GET['q']) ? substr(trim($_GET['q']), 0, 100) : '';
$state['field'] = cleanSearchField(isset($_GET['field']) ? $_GET['field'] : '');

$total = countPaintings($state['q'], $state['field']);
$totalPages = max(1, (int)ceil($total / PER_PAGE));
$state['page'] = isset($_GET['page']) ? (int)$_GET['page'] : 1;
//a page number that is too big or too small is moved back into range
$state['page'] = min(max(1, $state['page']), $totalPages);
$offset = ($state['page'] - 1) * PER_PAGE;

$counts = getTableCounts();
//the sort panel starts open when something other than the normal order is used
$sortChanged = !($state['sort'] == 'added' && $state['dir'] == 'desc');
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
   <link href="css/effects.css" rel="stylesheet">
   <link href="css/search.css?v=<?php echo filemtime("css/search.css"); ?>" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php //the tab bar at the top ?>
      <?php include 'nav.php'; ?>

      <?php //the heading, the summary of each table and the search and sort forms ?>
      <div class="ui secondary segment">
         <h1>Artworks</h1>
         <?php //summary report (task 2), each number links to its own page ?>
         <p class="summary">
            <?php echo $counts['Paintings']; ?> paintings &middot;
            <a href="artistPaintings.php"><?php echo $counts['Artists']; ?> artists</a> &middot;
            <a href="galleryPaintings.php"><?php echo $counts['Galleries']; ?> galleries</a> &middot;
            <a href="genreList.php"><?php echo $counts['Genres']; ?> genres</a>
         </p>
         <?php if (isAdmin()) { ?>
            <a class="ui primary button" href="addPainting.php">Add artwork</a>
         <?php } ?>

         <?php //the search box (task 4), js/search.js shows suggestions under it while typing ?>
         <form id="searchForm" class="search-form" method="get" action="artMain.php" role="search">
            <label for="searchField" class="sr-only">Search in</label>
            <select name="field" id="searchField">
               <option value="title" <?php if ($state['field'] == 'title') echo 'selected'; ?>>Title</option>
               <option value="artist" <?php if ($state['field'] == 'artist') echo 'selected'; ?>>Artist</option>
            </select>
            <div class="suggest-box">
               <label for="searchBox" class="sr-only">Search words</label>
               <input type="text" name="q" id="searchBox" autocomplete="off" maxlength="100"
                      placeholder="Start typing a title, like Star..." value="<?php echo htmlspecialchars($state['q']); ?>"
                      role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestList">
               <ul class="suggest-list" id="suggestList" role="listbox" hidden></ul>
            </div>
            <input type="hidden" name="sort" value="<?php echo $state['sort']; ?>">
            <input type="hidden" name="dir" value="<?php echo $state['dir']; ?>">
            <input class="ui primary button" type="submit" value="Search">
         </form>

         <?php //the sort options (task 2), js/artEffects.js puts them in a panel that slides open ?>
         <form id="sortForm" method="get" action="artMain.php" data-start-open="<?php echo $sortChanged ? 'yes' : 'no'; ?>">
            <?php if ($state['q'] != '') { ?>
               <input type="hidden" name="q" value="<?php echo htmlspecialchars($state['q']); ?>">
               <input type="hidden" name="field" value="<?php echo $state['field']; ?>">
            <?php } ?>
            <label for="sortKey">Sort by:</label>
            <select name="sort" id="sortKey">
               <?php foreach ($sortKeys as $key => $info) { ?>
                  <option value="<?php echo $key; ?>" <?php if ($key == $state['sort']) echo 'selected'; ?>><?php echo $info['label']; ?></option>
               <?php } ?>
            </select>
            <?php //the two buttons pick the direction, the one being used is filled in ?>
            <button class="ui <?php if ($state['dir'] == 'asc') echo 'primary '; ?>button" type="submit" name="dir" value="asc">Ascending &#9650;</button>
            <button class="ui <?php if ($state['dir'] == 'desc') echo 'primary '; ?>button" type="submit" name="dir" value="desc">Descending &#9660;</button>
         </form>
      </div>

      <?php //messages after a delete, an add or a search ?>
      <?php if (isset($_GET['deleted'])) { ?>
         <div class="ui positive message">The painting and everything related to it were deleted.</div>
      <?php } ?>
      <?php if (isset($_GET['added'])) { ?>
         <div class="ui positive message">The new painting was added. It is the first card below.<?php if (isset($_GET['newArtist'])) echo ' Its artist was new, so the artist was added too.'; ?></div>
      <?php } ?>
      <?php if (isset($_GET['notAllowed'])) { ?>
         <div class="ui warning message">Only staff (admins) can do that. <?php if (!isLoggedIn()) echo '<a href="artLogin.php">Log in</a> with a staff account.'; ?></div>
      <?php } ?>
      <?php if ($state['q'] != '') { ?>
         <div class="ui message">
            <?php echo $total; ?> painting<?php echo $total == 1 ? '' : 's'; ?> with <?php echo $state['field'] == 'artist' ? 'an artist' : 'a title'; ?>
            starting with &ldquo;<?php echo htmlspecialchars($state['q']); ?>&rdquo;.
            <a href="<?php echo htmlspecialchars(pageLink($state, array('q' => '', 'page' => 1))); ?>">Show all paintings</a>
         </div>
      <?php } ?>

      <?php //which paintings are showing, then the cards and the page links (task 3) ?>
      <div class="ui segment">
         <?php if ($total > 0) { ?>
            <p class="page-status">Showing <?php echo $offset + 1; ?>&ndash;<?php echo min($offset + PER_PAGE, $total); ?> of <?php echo $total; ?>
               &middot; page <?php echo $state['page']; ?> of <?php echo $totalPages; ?></p>
         <?php } ?>
         <div class="ui four doubling cards">
            <?php outputPaintings($sortKeys, $state, $offset); ?>
         </div>
         <?php outputPagination($state, $totalPages); ?>
      </div>
   </main>

   <script src="js/jquery-3.1.1.min.js"></script>
   <script src="js/artEffects.js?v=<?php echo filemtime("js/artEffects.js"); ?>"></script>
   <script src="js/search.js?v=<?php echo filemtime("js/search.js"); ?>"></script>
</body>
</html>
