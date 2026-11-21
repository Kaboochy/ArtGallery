<?php
//uploadHelpers.php saves a painting picture sent from the browser into the images folder (task 6)
//it is based on the Upload.php example from the www slides and follows its security tips:
//only .jpg files, the file cant be empty, there is a size limit, and the file name is made by us (not the user)

//the biggest picture we take, 2 MB
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);

//true when a file was picked in the upload box called $field
function uploadWasSent($field) {
   return isset($_FILES[$field]) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE;
}

//makes an image name from the uploaded file's own name, for when the image name box was left blank
//anything that isnt a letter, number, - or _ turns into -, so "Starry Night.jpg" becomes "Starry-Night"
function imageNameFromUpload($field) {
   $base = pathinfo($_FILES[$field]['name'], PATHINFO_FILENAME);
   $base = preg_replace('/[^A-Za-z0-9_-]+/', '-', $base);
   return substr(trim($base, '-'), 0, 50);
}

//checks the uploaded file, returns '' when it is fine or a message saying what is wrong
function checkUpload($field) {
   $file = $_FILES[$field];

   if ($file['error'] == UPLOAD_ERR_INI_SIZE || $file['error'] == UPLOAD_ERR_FORM_SIZE)
      return 'The picture is too big. The limit is 2 MB.';
   if ($file['error'] != UPLOAD_ERR_OK)
      return 'The picture did not upload (error ' . $file['error'] . '). Please try again.';
   if ($file['size'] == 0)
      return 'The picture file is empty.';
   if ($file['size'] > MAX_UPLOAD_BYTES)
      return 'The picture is too big. The limit is 2 MB.';

   //the name has to end in .jpg or .jpeg
   $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
   if ($extension != 'jpg' && $extension != 'jpeg')
      return 'Only .jpg pictures can be uploaded.';

   //a renamed file (like a .exe called picture.jpg) is caught by looking inside the file
   $finfo = new finfo(FILEINFO_MIME_TYPE);
   if ($finfo->file($file['tmp_name']) != 'image/jpeg' || !getimagesize($file['tmp_name']))
      return 'That file is not a real .jpg picture.';

   return '';
}

//moves the checked file into the images folder as $imageName.jpg
//move_uploaded_file only works on files that really came from the upload form
function saveUpload($field, $imageFolder, $imageName) {
   return move_uploaded_file($_FILES[$field]['tmp_name'], $imageFolder . $imageName . '.jpg');
}
?>
