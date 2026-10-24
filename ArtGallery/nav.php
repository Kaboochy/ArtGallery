<?php
//nav.php is the tab bar at the top of every page, each page includes it
//$page is the name of the page being shown so its tab can be highlighted
$page = basename($_SERVER['SCRIPT_NAME']);
?>
<div class="ui pointing menu">
  <a class="<?php if ($page == 'artMain.php') echo 'active '; ?>item" href="artMain.php">Artworks</a>
  <a class="<?php if ($page == 'addPainting.php') echo 'active '; ?>item" href="addPainting.php">Add Artwork</a>
  <a class="<?php if ($page == 'artistPaintings.php') echo 'active '; ?>item" href="artistPaintings.php">Artists</a>
  <a class="<?php if ($page == 'galleryPaintings.php') echo 'active '; ?>item" href="galleryPaintings.php">Galleries</a>
  <a class="<?php if ($page == 'genreList.php' || $page == 'genreDetail.php') echo 'active '; ?>item" href="genreList.php">Genres</a>

  <?php //right side shows who is logged in and the log out tab ?>
  <div class="right menu">
    <?php if (isset($_SESSION['username'])) { ?>
      <span class="item">Logged in as <?php echo htmlspecialchars($_SESSION['username']); ?></span>
    <?php } ?>
    <a class="item" href="logout.php">Log out</a>
  </div>
</div>
