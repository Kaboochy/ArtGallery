<?php
require 'checkAdmin.php';
require_once('dbConfig.php');
require_once('userHelpers.php');

//runs when make admin, make user or delete is clicked
//an admin cant change or delete their own account so one admin is always left
if (isset($_POST['userAction']) && isset($_POST['username'])) {
   $target = $_POST['username'];
   $done = 'none';
   if ($target != $_SESSION['username']) {
      try {
         $pdo = userDb();
         if ($_POST['userAction'] == 'makeAdmin' || $_POST['userAction'] == 'makeUser') {
            $role = $_POST['userAction'] == 'makeAdmin' ? 'admin' : 'user';
            $statement = $pdo->prepare('update Users set role=:role where username=:un');
            $statement->execute([':role' => $role, ':un' => $target]);
            $done = 'role';
         }
         else if ($_POST['userAction'] == 'delete') {
            $statement = $pdo->prepare('delete from Users where username=:un');
            $statement->execute([':un' => $target]);
            $done = 'deleted';
         }
         $pdo = null;
      }
      catch (PDOException $e) {
         die( $e->getMessage() );
      }
   }
   header('Location: admin.php?userDone=' . $done . '#users');
   exit;
}

//runs a query that returns one number
function countOf($pdo, $sql) {
   $row = $pdo->query($sql)->fetch();
   return (int)$row[0];
}

try {
   $pdo = userDb();

   $tableCounts = array(
      'Paintings'  => countOf($pdo, 'select count(*) from Paintings'),
      'Artists'    => countOf($pdo, 'select count(*) from Artists'),
      'Galleries'  => countOf($pdo, 'select count(*) from Galleries'),
      'Genres'     => countOf($pdo, 'select count(*) from Genres'),
      'Reviews'    => countOf($pdo, 'select count(*) from Reviews'),
      'Orders'     => countOf($pdo, 'select count(*) from Orders'),
      'Users'      => countOf($pdo, 'select count(*) from Users')
   );
   $adminCount = countOf($pdo, "select count(*) from Users where role='admin'");

   $facts = array(
      'Paintings with no description' => countOf($pdo, "select count(*) from Paintings where Description is null or Description = ''"),
      'Paintings with no gallery'     => countOf($pdo, 'select count(*) from Paintings where GalleryID is null or GalleryID = 0'),
      'Artists with no paintings'     => countOf($pdo, 'select count(*) from Artists where ArtistID not in (select distinct ArtistID from Paintings where ArtistID is not null)')
   );
   $yearRow = $pdo->query('select min(YearOfWork) as oldest, max(YearOfWork) as newest from Paintings where YearOfWork > 0')->fetch();

   $topArtists = $pdo->query('select Artists.ArtistID, Artists.FirstName, Artists.LastName, count(Paintings.PaintingID) as total
                              from Artists join Paintings on Paintings.ArtistID = Artists.ArtistID
                              group by Artists.ArtistID, Artists.FirstName, Artists.LastName
                              order by total desc, Artists.LastName limit 10')->fetchAll();

   $topGenres = $pdo->query('select Genres.GenreID, Genres.GenreName, count(PaintingGenres.PaintingID) as total
                             from Genres join PaintingGenres on PaintingGenres.GenreID = Genres.GenreID
                             group by Genres.GenreID, Genres.GenreName
                             order by total desc, Genres.GenreName limit 10')->fetchAll();

   $users = $pdo->query('select forename, surname, username, role, email, dateJoined from Users order by role, username')->fetchAll();
   $pdo = null;
}
catch (PDOException $e) {
   die( $e->getMessage() );
}
?>
<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Art Gallery - Admin</title>
   <link href="semantic/semantic.css" rel="stylesheet">
   <link href="css/theme.css" rel="stylesheet">
</head>
<body>
   <main class="ui container">
      <?php include 'nav.php'; ?>

      <div class="ui secondary segment">
         <h1>Admin</h1>
         <p>Welcome, <?php echo htmlspecialchars($_SESSION['forename']); ?>. Everything staff can do is here.</p>
      </div>

      <?php if (isset($_GET['userDone']) && $_GET['userDone'] == 'role') { ?>
         <div class="ui positive message">The user's role was changed. They get the new role the next time they log in.</div>
      <?php } else if (isset($_GET['userDone']) && $_GET['userDone'] == 'deleted') { ?>
         <div class="ui positive message">The account was deleted.</div>
      <?php } else if (isset($_GET['userDone'])) { ?>
         <div class="ui warning message">You cant change or delete your own account here.</div>
      <?php } ?>

      <div class="ui segment">
         <div class="admin-actions">
            <a class="ui primary button" href="addPainting.php">Add artwork</a>
            <a class="ui primary button" href="addArtist.php">Add artist</a>
            <a class="ui button" href="artMain.php">Edit or delete artworks</a>
            <a class="ui button" href="artistPaintings.php">Edit artists</a>
            <a class="ui button" href="scrape.php">Import from saved WikiArt pages</a>
         </div>
      </div>

      <div class="ui segment">
         <h2 class="section-title">Summary report</h2>
         <div class="ui stackable three column grid">
            <div class="column">
               <h3>Rows in each table</h3>
               <table class="ui definition table">
                  <tbody>
                     <?php foreach ($tableCounts as $table => $total) { ?>
                        <tr><td><?php echo $table; ?></td><td><?php echo number_format($total); ?></td></tr>
                     <?php } ?>
                     <tr><td>Admins</td><td><?php echo $adminCount; ?> of <?php echo $tableCounts['Users']; ?> users</td></tr>
                  </tbody>
               </table>
               <table class="ui definition table">
                  <tbody>
                     <?php foreach ($facts as $label => $total) { ?>
                        <tr><td><?php echo $label; ?></td><td><?php echo $total; ?></td></tr>
                     <?php } ?>
                     <tr><td>Oldest painting</td><td><?php echo $yearRow['oldest']; ?></td></tr>
                     <tr><td>Newest painting</td><td><?php echo $yearRow['newest']; ?></td></tr>
                  </tbody>
               </table>
            </div>
            <div class="column">
               <h3>Artists with the most paintings</h3>
               <table class="ui definition table">
                  <tbody>
                     <?php foreach ($topArtists as $artist) { ?>
                        <tr>
                           <td><a href="artistPaintings.php?id=<?php echo $artist['ArtistID']; ?>"><?php echo htmlspecialchars(trim($artist['FirstName'] . ' ' . $artist['LastName'])); ?></a></td>
                           <td><?php echo $artist['total']; ?></td>
                        </tr>
                     <?php } ?>
                  </tbody>
               </table>
            </div>
            <div class="column">
               <h3>Genres with the most paintings</h3>
               <table class="ui definition table">
                  <tbody>
                     <?php foreach ($topGenres as $genre) { ?>
                        <tr>
                           <td><a href="genreDetail.php?id=<?php echo $genre['GenreID']; ?>"><?php echo htmlspecialchars($genre['GenreName']); ?></a></td>
                           <td><?php echo $genre['total']; ?></td>
                        </tr>
                     <?php } ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>

      <div class="ui segment" id="users">
         <h2 class="section-title">Users</h2>
         <div class="table-scroll">
         <table class="ui unstackable table user-table">
            <thead>
               <tr><th>User ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody>
               <?php foreach ($users as $user) { ?>
                  <tr>
                     <td><?php echo htmlspecialchars($user['username']); ?></td>
                     <td><?php echo htmlspecialchars($user['forename'] . ' ' . $user['surname']); ?></td>
                     <td><?php echo htmlspecialchars((string)$user['email']); ?></td>
                     <td><?php echo $user['role'] == 'admin' ? 'Admin' : 'User'; ?></td>
                     <td><?php echo $user['dateJoined'] ? date('M j, Y', strtotime($user['dateJoined'])) : ''; ?></td>
                     <td>
                        <?php if ($user['username'] == $_SESSION['username']) { ?>
                           <em>you</em>
                        <?php } else { ?>
                           <form method="post" action="admin.php" class="inline-form">
                              <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                              <?php if ($user['role'] == 'admin') { ?>
                                 <button class="ui mini button" type="submit" name="userAction" value="makeUser">Make user</button>
                              <?php } else { ?>
                                 <button class="ui mini button" type="submit" name="userAction" value="makeAdmin">Make admin</button>
                              <?php } ?>
                              <button class="ui mini red basic button" type="submit" name="userAction" value="delete"
                                      onclick="return confirm('Delete the account <?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>?');">Delete</button>
                           </form>
                        <?php } ?>
                     </td>
                  </tr>
               <?php } ?>
            </tbody>
         </table>
         </div>
      </div>
   </main>
</body>
</html>
