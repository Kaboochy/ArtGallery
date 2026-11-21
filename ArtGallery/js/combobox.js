// combobox.js makes every <div class="combo"> (printed by combobox.php) into a type-in dropdown
// typing filters the list, the arrow keys move through it, Enter picks, Escape closes
(function () {
   function setUp(box) {
      var input = box.querySelector('input');
      var toggle = box.querySelector('.combo-toggle');
      var list = box.querySelector('.combo-list');
      var items = Array.prototype.slice.call(list.querySelectorAll('li[data-value]'));
      var newLabel = box.getAttribute('data-new-label');      // only set when new names are allowed
      var submitOnPick = box.getAttribute('data-submit-on-pick') === 'yes';
      var extraRow = null;                                    // the "add new" or "no matches" row
      var active = -1;                                        // which visible row is highlighted

      items.forEach(function (li, i) {
         li.id = list.id + '-' + i;
         li.textEl = li.querySelector('.combo-text');
         li.plain = li.getAttribute('data-value');
      });

      function visibleRows() {
         return Array.prototype.slice.call(list.children).filter(function (li) {
            return !li.hidden && !li.classList.contains('combo-empty');
         });
      }

      // puts the typed text in bold burgundy inside each matching choice
      function highlight(li, query) {
         var text = li.plain;
         li.textEl.textContent = '';
         var spot = query ? text.toLowerCase().indexOf(query) : -1;
         if (spot < 0) { li.textEl.textContent = text; return; }
         li.textEl.appendChild(document.createTextNode(text.slice(0, spot)));
         var mark = document.createElement('mark');
         mark.textContent = text.slice(spot, spot + query.length);
         li.textEl.appendChild(mark);
         li.textEl.appendChild(document.createTextNode(text.slice(spot + query.length)));
      }

      function setActive(index) {
         var rows = visibleRows();
         rows.forEach(function (li) { li.classList.remove('active'); li.setAttribute('aria-selected', 'false'); });
         active = rows.length ? Math.max(-1, Math.min(index, rows.length - 1)) : -1;
         if (active >= 0) {
            rows[active].classList.add('active');
            rows[active].setAttribute('aria-selected', 'true');
            input.setAttribute('aria-activedescendant', rows[active].id);
            // keep the highlighted row in view when the list scrolls
            var li = rows[active];
            if (li.offsetTop < list.scrollTop) list.scrollTop = li.offsetTop;
            else if (li.offsetTop + li.offsetHeight > list.scrollTop + list.clientHeight)
               list.scrollTop = li.offsetTop + li.offsetHeight - list.clientHeight;
         } else {
            input.removeAttribute('aria-activedescendant');
         }
      }

      // showAll is true when the arrow was clicked, so every choice shows
      function open(showAll) {
         var typed = input.value.replace(/\s+/g, ' ').trim();
         var query = showAll ? '' : typed.toLowerCase();
         var exact = false;
         items.forEach(function (li) {
            var match = li.plain.toLowerCase().indexOf(query) >= 0;
            li.hidden = !match;
            if (li.plain.toLowerCase() === typed.toLowerCase()) exact = true;
            highlight(li, match ? query : '');
         });

         if (extraRow) { extraRow.remove(); extraRow = null; }
         var anyShown = items.some(function (li) { return !li.hidden; });
         if (newLabel && typed && !exact && !showAll) {
            // a name that isnt in the list can be added as a new one
            extraRow = document.createElement('li');
            extraRow.className = 'combo-new';
            extraRow.id = list.id + '-new';
            extraRow.setAttribute('role', 'option');
            extraRow.setAttribute('data-new', typed);
            extraRow.textContent = newLabel + ' “' + typed + '”';
            list.appendChild(extraRow);
         } else if (!anyShown) {
            extraRow = document.createElement('li');
            extraRow.className = 'combo-empty';
            extraRow.textContent = 'No matches';
            list.appendChild(extraRow);
         }

         list.hidden = false;
         box.classList.add('open');
         input.setAttribute('aria-expanded', 'true');
         if (showAll) list.scrollTop = 0;
         setActive(query ? 0 : -1);
      }

      function close() {
         if (extraRow) { extraRow.remove(); extraRow = null; }
         list.hidden = true;
         box.classList.remove('open');
         input.setAttribute('aria-expanded', 'false');
         input.removeAttribute('aria-activedescendant');
         active = -1;
      }

      function pick(li) {
         if (!li || li.classList.contains('combo-empty')) return;
         input.value = li.hasAttribute('data-new') ? li.getAttribute('data-new') : li.plain;
         close();
         if (submitOnPick && input.form) {
            if (input.form.requestSubmit) input.form.requestSubmit(); else input.form.submit();
         }
      }

      input.addEventListener('input', function () { open(false); });
      input.addEventListener('focus', function () { if (input.value) open(false); });
      input.addEventListener('click', function () { if (list.hidden) open(!input.value); });
      input.addEventListener('blur', function () { setTimeout(close, 120); });

      input.addEventListener('keydown', function (e) {
         var rows = visibleRows();
         if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (list.hidden) open(!input.value); else setActive(active + 1);
         } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(active - 1);
         } else if (e.key === 'Enter' && !list.hidden && active >= 0) {
            // Enter picks the highlighted row instead of sending the form
            e.preventDefault();
            pick(rows[active]);
         } else if (e.key === 'Escape' && !list.hidden) {
            e.preventDefault();
            close();
         }
      });

      // mousedown instead of click so the text box doesnt lose focus first
      list.addEventListener('mousedown', function (e) {
         e.preventDefault();
         var li = e.target.closest('li');
         if (li) pick(li);
      });
      list.addEventListener('mousemove', function (e) {
         var li = e.target.closest('li');
         var index = visibleRows().indexOf(li);
         if (index >= 0 && index !== active) setActive(index);
      });

      toggle.addEventListener('mousedown', function (e) {
         e.preventDefault();
         if (list.hidden) { input.focus(); open(true); } else close();
      });
   }

   Array.prototype.forEach.call(document.querySelectorAll('.combo'), setUp);
})();
