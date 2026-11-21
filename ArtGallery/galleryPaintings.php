<?php
//public page, auth.php only starts the session
require_once 'auth.php';

require_once('dbConfig.php'); 
try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    <title>Art Gallery - Galleries</title>
    <link href="semantic/semantic.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
  </head>
<body>
<main class="ui container">
   <?php include 'nav.php'; ?>
   <div class="ui secondary segment">
      <h1>Galleries</h1>
      <form method="get" action="galleryPaintings.php">
         Gallery: 
         <select name="gallery">
            <option value="0">Select a gallery</option>
            
            <?php
   
            $sql = 'select * from Galleries order by GalleryName';
            $result = $pdo->query($sql);
            while ($row = $result->fetch()) {         
               echo '<option value="' . $row['GalleryID'] . '"';
               if (isset($_GET['gallery']) && $row['GalleryID'] == $_GET['gallery']) 
                  echo ' selected ';
               echo '>';
               echo htmlspecialchars($row['GalleryName']);
               echo ' (' . htmlspecialchars($row['GalleryCity']) . ')';
               echo '</option>';
             }
   
            ?>
         </select>
         <input class="ui button" type="submit" value="Submit">
      </form>
   </div>

<div class="ui segment">
   <?php //doubling makes the cards bigger on tablets and phones ?>
   <div class="ui six doubling cards">
      <?php
      //only show cards once a gallery is picked
      if ($_SERVER["REQUEST_METHOD"] == "GET") {
         if (isset($_GET['gallery']) && (int)$_GET['gallery'] > 0) {
            //prepared so nothing from the address bar reaches the sql
            $result = $pdo->prepare('select * from Paintings where GalleryId=:gallery');
            $result->bindValue(':gallery', (int)$_GET['gallery'], PDO::PARAM_INT);
            $result->execute();
            while ($row = $result->fetch()) {
      ?>
               <a class="card" href="paintingDetail.php?id=<?php echo $row['PaintingID']; ?>">
                  <div class="image">
                     <img src="images/art/works/square-medium/<?php echo htmlspecialchars($row['ImageFileName']); ?>.jpg"
                        title="<?php echo htmlspecialchars($row['Title']); ?>"
                        alt="<?php echo htmlspecialchars($row['Title']); ?>" >
                  </div>
                  <div class="extra"><?php echo htmlspecialchars($row['Title']); ?></div>
               </a>  <!--end class=card-->
      <?php
            } //end while
         } //end if (isset
      } //end if ($_SERVER
      ?>
   </div>  <!--end class=cards-->
</div>  <!--end class=segment-->
</main>

</body>
</html>