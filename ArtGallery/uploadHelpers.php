<?php
//follows the security tips from the Upload.php example in the www slides

define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);

function uploadWasSent($field) {
   return isset($_FILES[$field]) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE;
}

//used when the image name box was left blank
//anything but letters, numbers, - and _ becomes -
function imageNameFromUpload($field) {
   $base = pathinfo($_FILES[$field]['name'], PATHINFO_FILENAME);
   $base = preg_replace('/[^A-Za-z0-9_-]+/', '-', $base);
   return substr(trim($base, '-'), 0, 50);
}

//returns '' when fine or a message saying what is wrong
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

   $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
   if ($extension != 'jpg' && $extension != 'jpeg')
      return 'Only .jpg pictures can be uploaded.';

   //look inside the file so a renamed .exe cant pass as a .jpg
   $finfo = new finfo(FILEINFO_MIME_TYPE);
   if ($finfo->file($file['tmp_name']) != 'image/jpeg' || !getimagesize($file['tmp_name']))
      return 'That file is not a real .jpg picture.';

   return '';
}

//move_uploaded_file only works on files that came from the upload form
function saveUpload($field, $imageFolder, $imageName) {
   return move_uploaded_file($_FILES[$field]['tmp_name'], $imageFolder . $imageName . '.jpg');
}
?>
