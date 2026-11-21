<?php
//nav.php is the tab bar at the top of every page, each page includes it
//$page is the name of the page being shown so its tab can be highlighted
//the tabs change with who is looking (task 10): visitors and users see the gallery tabs,
//admins also see the add tabs and the admin page
require_once 'auth.php';
$page = basename($_SERVER['SCRIPT_NAME']);

//prints one tab, $pages are the pages that light up this tab
function navTab($href, $label, $pages, $current) {
   echo '<a class="' . (in_array($current, $pages) ? 'active ' : '') . 'item" href="' . $href . '">' . $label . '</a>';
}
?>
<div class="ui pointing menu">
  <?php
  navTab('artMain.php', 'Artworks', array('artMain.php', 'paintingDetail.php', 'editPainting.php'), $page);
  navTab('artistPaintings.php', 'Artists', array('artistPaintings.php', 'editArtist.php'), $page);
  navTab('galleryPaintings.php', 'Galleries', array('galleryPaintings.php'), $page);
  navTab('genreList.php', 'Genres', array('genreList.php', 'genreDetail.php'), $page);

  //only staff can add things
  if (isAdmin()) {
     navTab('addPainting.php', 'Add Artwork', array('addPainting.php'), $page);
     navTab('addArtist.php', 'Add Artist', array('addArtist.php'), $page);
     navTab('admin.php', 'Admin', array('admin.php', 'scrape.php'), $page);
  }
  ?>

  <?php //right side shows who is logged in, or the log in and register tabs for visitors ?>
  <div class="right menu">
    <?php if (isLoggedIn()) { ?>
      <span class="item">Logged in as <?php echo htmlspecialchars($_SESSION['username']); ?><?php if (isAdmin()) echo ' (admin)'; ?></span>
      <?php navTab('profile.php', 'My Profile', array('profile.php'), $page); ?>
      <a class="item" href="logout.php">Log out</a>
    <?php } else { ?>
      <?php navTab('artLogin.php', 'Log in', array('artLogin.php'), $page); ?>
      <?php navTab('register.php', 'Register', array('register.php'), $page); ?>
    <?php } ?>
  </div>
</div>
