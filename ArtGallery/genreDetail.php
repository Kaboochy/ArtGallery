<?php
//this makes sure someone is logged in, if not it sends them to the login page
require 'checkLogin.php';

require_once('dbConfig.php'); 

/*
 Displays a list of genres
*/

try {
   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
   $sql = 'select GenreId, GenreName, Description, Link from Genres where GenreId=:id';
   $id =  $_GET['id'];
   $statement = $pdo->prepare($sql);
   $statement->bindValue(':id', $id);
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
    <div class="ui secondary segment">
         <h1><?php echo $row['GenreName']; ?></h1>
    </div>    
    <div class="ui segment">
        <div class="ui grid">
           <div class="three wide column">
                <img src="images/art/genres/square-medium/<?php echo $row['GenreId']; ?>.jpg" >
           </div>
           <div class="thirteen wide column">
                <p><?php echo $row['Description']; ?></p>
                <p>
                <a class="ui labeled icon primary button" href="<?php echo $row['Link']; ?>">
                  <i class="external icon"></i>
                  Read more on Wikipedia about <?php echo $row['GenreName']; ?>
                </a>
                </p>
           </div> 
       </div>
    </div>
</main>


</body>
</html>