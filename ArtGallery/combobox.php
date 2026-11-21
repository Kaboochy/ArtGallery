<?php
//replaces <datalist>, which looks different in every browser
//needs css/combobox.css and js/combobox.js on the page
//without javascript it still works as a normal text box

//$items: array('value' => text, 'note' => small grey text)
//$options: newLabel allows new names, submitOnPick sends the form on pick, id sets the box id, required
function outputComboBox($name, $value, $placeholder, $items, $options = array()) {
   $id = isset($options['id']) ? $options['id'] : 'combo-' . $name;
   $listId = $id . '-list';

   echo '<div class="combo"';
   if (isset($options['newLabel'])) echo ' data-new-label="' . htmlspecialchars($options['newLabel']) . '"';
   if (!empty($options['submitOnPick'])) echo ' data-submit-on-pick="yes"';
   echo '>';

   echo '<input type="text" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '"'
      . ' value="' . htmlspecialchars($value) . '"'
      . ' placeholder="' . htmlspecialchars($placeholder) . '"'
      . (!empty($options['required']) ? ' required' : '')
      . ' autocomplete="off" role="combobox" aria-autocomplete="list"'
      . ' aria-expanded="false" aria-controls="' . htmlspecialchars($listId) . '">';

   //the arrow opens the full list
   echo '<button type="button" class="combo-toggle" tabindex="-1" aria-label="Show all choices"></button>';

   //js/combobox.js hides the choices that dont match
   echo '<ul class="combo-list" id="' . htmlspecialchars($listId) . '" role="listbox" hidden>';
   foreach ($items as $item) {
      echo '<li role="option" data-value="' . htmlspecialchars($item['value']) . '">';
      echo '<span class="combo-text">' . htmlspecialchars($item['value']) . '</span>';
      if (!empty($item['note']))
         echo '<span class="combo-note">' . htmlspecialchars($item['note']) . '</span>';
      echo '</li>';
   }
   echo '</ul>';
   echo '</div>';
}
?>
