<?php
//$page lights up the matching tab
//admins also get the add tabs and the admin page
require_once 'auth.php';
$page = basename($_SERVER['SCRIPT_NAME']);

//$pages are the pages that light up this tab
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

  if (isAdmin()) {
     navTab('addPainting.php', 'Add Artwork', array('addPainting.php'), $page);
     navTab('addArtist.php', 'Add Artist', array('addArtist.php'), $page);
     navTab('admin.php', 'Admin', array('admin.php', 'scrape.php'), $page);
  }
  ?>

  <?php //visitors get log in and register instead ?>
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
