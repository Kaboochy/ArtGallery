<?php
//public page, auth.php only starts the session
require_once 'auth.php';

require_once('dbConfig.php'); 

try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
   $sql = 'select GenreId, GenreName, Description, Link from Genres where GenreId=:id';
   //(int) turns anything that isnt a number into 0 so no genre is found
   $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
   $statement = $pdo->prepare($sql);
   $statement->bindValue(':id', $id, PDO::PARAM_INT);
   $statement->execute();   
   
   $row = $statement->fetch(PDO::FETCH_ASSOC);
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
    <title>Art Gallery - Genre Details</title>
    <link href="semantic/semantic.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
  </head>
<body>
<main class="ui container">
  <?php include 'nav.php'; ?>
  <?php if (!$row) { ?>
    <div class="ui secondary segment"><h1>Genres</h1></div>
    <div class="ui negative message">That genre could not be found.</div>
    <a class="ui button" href="genreList.php">Back to Genres</a>
  <?php } else { ?>
    <div class="ui secondary segment">
         <h1><?php echo htmlspecialchars($row['GenreName']); ?></h1>
    </div>    
    <div class="ui segment">
        <?php //stackable puts the picture above the text on a phone ?>
        <div class="ui stackable grid">
           <div class="three wide column">
                <img src="images/art/genres/square-medium/<?php echo $row['GenreId']; ?>.jpg" alt="<?php echo htmlspecialchars($row['GenreName']); ?>">
           </div>
           <div class="thirteen wide column">
                <p><?php echo $row['Description']; ?></p>
                <p>
                <a class="ui labeled icon primary button" href="<?php echo htmlspecialchars($row['Link']); ?>">
                  <i class="external icon"></i>
                  Read more on Wikipedia about <?php echo htmlspecialchars($row['GenreName']); ?>
                </a>
                </p>
           </div> 
       </div>
    </div>
  <?php } ?>
</main>

</body>
</html>