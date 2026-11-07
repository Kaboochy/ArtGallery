<?php
//combobox.php prints a type-in dropdown that matches the site theme
//it replaces the plain browser dropdown (<datalist>), which looks different in every browser
//the page also needs css/combobox.css in its head and js/combobox.js at the bottom of the body
//without javascript it still works as a normal text box

//$name        the form field name, the typed or picked text is what gets sent
//$value       what the box shows when the page loads (for putting back what the user typed)
//$placeholder the grey hint text
//$items       a list of choices, each one is array('value' => 'text', 'note' => 'small grey text on the right')
//$options     'newLabel' => text for the "add new" row (leave out to only allow choices from the list)
//             'submitOnPick' => true sends the form as soon as a choice is picked
//             'id' => the id for the text box (made from $name if left out)
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
      . ' autocomplete="off" role="combobox" aria-autocomplete="list"'
      . ' aria-expanded="false" aria-controls="' . htmlspecialchars($listId) . '">';

   //the little arrow on the right opens the full list
   echo '<button type="button" class="combo-toggle" tabindex="-1" aria-label="Show all choices"></button>';

   //every choice, the javascript hides the ones that dont match what is typed
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
