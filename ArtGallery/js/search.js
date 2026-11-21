//fetch returns a promise with the json from suggest.php
//without javascript the search button still filters on artMain.php
(function () {
   var form = document.getElementById('searchForm');
   if (!form) return;

   var input = document.getElementById('searchBox');
   var fieldSelect = document.getElementById('searchField');
   var list = document.getElementById('suggestList');
   var timer = null;        //waits after the last key so every key doesnt send a request
   var lastAsked = 0;       //answers to older requests are ignored
   var active = -1;

   function updatePlaceholder() {
      input.placeholder = fieldSelect.value === 'artist'
         ? 'Start typing an artist\'s last name, like Mon...'
         : 'Start typing a title, like Star...';
   }

   function rows() {
      return Array.prototype.slice.call(list.querySelectorAll('li[data-url]'));
   }

   function setActive(index) {
      var all = rows();
      all.forEach(function (li) { li.classList.remove('active'); li.setAttribute('aria-selected', 'false'); });
      active = all.length ? Math.max(-1, Math.min(index, all.length - 1)) : -1;
      if (active >= 0) {
         all[active].classList.add('active');
         all[active].setAttribute('aria-selected', 'true');
         input.setAttribute('aria-activedescendant', all[active].id);
      } else {
         input.removeAttribute('aria-activedescendant');
      }
   }

   function close() {
      list.hidden = true;
      list.innerHTML = '';
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
      active = -1;
   }

   //textContent keeps any < or & as plain text
   function addLabel(parent, text, typed) {
      var start = text.toLowerCase().indexOf(typed.toLowerCase());
      if (start < 0 || typed === '') { parent.textContent = text; return; }
      parent.appendChild(document.createTextNode(text.slice(0, start)));
      var mark = document.createElement('mark');
      mark.textContent = text.slice(start, start + typed.length);
      parent.appendChild(mark);
      parent.appendChild(document.createTextNode(text.slice(start + typed.length)));
   }

   function show(suggestions, typed) {
      list.innerHTML = '';
      if (suggestions.length === 0) {
         var empty = document.createElement('li');
         empty.className = 'suggest-empty';
         empty.textContent = 'No ' + (fieldSelect.value === 'artist' ? 'artists' : 'titles') + ' start with "' + typed + '"';
         list.appendChild(empty);
      }
      suggestions.forEach(function (s, i) {
         var li = document.createElement('li');
         li.id = 'suggest-' + i;
         li.setAttribute('role', 'option');
         li.setAttribute('data-url', s.url);
         if (s.image) {
            var img = document.createElement('img');
            img.src = s.image;
            img.alt = '';
            li.appendChild(img);
         }
         var text = document.createElement('span');
         text.className = 'suggest-text';
         var label = document.createElement('span');
         label.className = 'suggest-label';
         addLabel(label, s.label, typed);
         var note = document.createElement('span');
         note.className = 'suggest-note';
         note.textContent = s.note;
         text.appendChild(label);
         text.appendChild(note);
         li.appendChild(text);
         list.appendChild(li);
      });
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      setActive(-1);
   }

   function ask() {
      var typed = input.value.trim();
      if (typed === '') { close(); return; }
      var number = ++lastAsked;
      var url = 'suggest.php?field=' + encodeURIComponent(fieldSelect.value) + '&q=' + encodeURIComponent(typed);
      fetch(url)
         .then(function (response) {
            if (!response.ok) throw new Error('suggest.php answered ' + response.status);
            return response.json();
         })
         .then(function (suggestions) {
            //a newer request was sent so this answer is old
            if (number !== lastAsked) return;
            show(suggestions, typed);
         })
         .catch(function () {
            //no list if something failed, the search button still works
            if (number === lastAsked) close();
         });
   }

   input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(ask, 200);
   });

   input.addEventListener('focus', function () {
      if (input.value.trim() !== '') ask();
   });

   //short wait so a click on a suggestion happens before the list closes
   input.addEventListener('blur', function () { setTimeout(close, 150); });

   input.addEventListener('keydown', function (e) {
      if (list.hidden) return;
      if (e.key === 'ArrowDown') {
         e.preventDefault();
         setActive(active + 1);
      } else if (e.key === 'ArrowUp') {
         e.preventDefault();
         setActive(active - 1);
      } else if (e.key === 'Enter' && active >= 0) {
         //enter opens the highlighted suggestion instead of searching
         e.preventDefault();
         window.location.href = rows()[active].getAttribute('data-url');
      } else if (e.key === 'Escape') {
         e.preventDefault();
         close();
      }
   });

   //mousedown fires before the box loses focus and closes the list
   list.addEventListener('mousedown', function (e) {
      var li = e.target.closest('li[data-url]');
      if (!li) return;
      e.preventDefault();
      window.location.href = li.getAttribute('data-url');
   });

   list.addEventListener('mousemove', function (e) {
      var li = e.target.closest('li[data-url]');
      var index = rows().indexOf(li);
      if (index >= 0 && index !== active) setActive(index);
   });

   //switching title and artist searches again with the same letters
   fieldSelect.addEventListener('change', function () {
      updatePlaceholder();
      if (input.value.trim() !== '') { input.focus(); ask(); }
   });

   updatePlaceholder();
})();
