// formCheck.js checks what the user typed before the form is sent (task 7a, client side error handling)
// it works on every <form data-check-form>, the rules are written on each input:
//   required             the box cant be empty
//   data-check="year"    a whole number of up to 4 digits, not after this year
//   data-check="email"   looks like name@example.com (blank is fine unless required)
//   data-check="url"     starts with http:// or https:// (blank is fine unless required)
//   data-check="username" 3 to 32 letters, numbers or _
//   data-check="imagename" letters, numbers, - and _ only (no .jpg)
//   data-check="jpg"     a file box: the picked file has to be a .jpg of at most 2 MB
//   data-min-length="6"  at least that many characters
//   data-match="name"    has to be the same as the box with that name (typing a password twice)
//   data-after="name"    a year that cant be before the year in the box with that name (death after birth)
// the server checks everything again (task 7b), this just tells the user sooner
// each message shows in small red text right under its box and goes away once the box is fixed
(function () {
   var thisYear = new Date().getFullYear();
   var maxUploadBytes = 2 * 1024 * 1024;

   // the label text of the box, used to start each message ("Year of birth: ...")
   function labelOf(input) {
      var field = input.closest('.field');
      var label = field ? field.querySelector('label') : null;
      return label ? label.textContent.replace(/\s+/g, ' ').trim() : input.name;
   }

   // finds another box in the same form by its name
   function other(form, name) {
      return form.querySelector('[name="' + name + '"]');
   }

   // gives back the problem with one box, or '' if it is fine
   function problemWith(input, form) {
      var value = input.type === 'file' ? '' : input.value.trim();
      var rule = input.getAttribute('data-check');

      if (input.hasAttribute('required') && value === '' && input.type !== 'file')
         return 'Please fill this in.';

      if (rule === 'jpg' && input.files && input.files.length) {
         var file = input.files[0];
         if (!/\.jpe?g$/i.test(file.name)) return 'Only .jpg pictures can be uploaded.';
         if (file.size === 0) return 'That file is empty.';
         if (file.size > maxUploadBytes) return 'The picture is too big. The limit is 2 MB.';
      }

      if (value === '') return '';

      if (rule === 'year') {
         if (!/^\d{1,4}$/.test(value)) return 'Enter the year as a number, like 1889.';
         if (parseInt(value, 10) > thisYear) return 'The year cannot be after ' + thisYear + '.';
      }
      if (rule === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value))
         return 'Enter an email like name@example.com.';
      if (rule === 'url' && !/^https?:\/\/\S+$/i.test(value))
         return 'The link has to start with http:// or https://';
      if (rule === 'username' && !/^[A-Za-z0-9_]{3,32}$/.test(value))
         return 'Use 3 to 32 letters, numbers or _ with no spaces.';
      if (rule === 'imagename' && !/^[A-Za-z0-9_-]+$/.test(value))
         return 'Only letters, numbers, - and _ (leave off the .jpg).';

      var minLength = input.getAttribute('data-min-length');
      if (minLength && value.length < parseInt(minLength, 10))
         return 'Use at least ' + minLength + ' characters.';

      var match = input.getAttribute('data-match');
      if (match && other(form, match) && value !== other(form, match).value.trim())
         return 'This does not match the box above.';

      // the year of death cant be before the year of birth
      var after = input.getAttribute('data-after');
      if (after && other(form, after)) {
         var first = other(form, after).value.trim();
         if (/^\d{1,4}$/.test(first) && /^\d{1,4}$/.test(value) && parseInt(value, 10) < parseInt(first, 10))
            return 'This cannot be before the ' + labelOf(other(form, after)).toLowerCase() + ' (' + first + ').';
      }
      return '';
   }

   // shows or clears the red message under one box, semantic ui turns the box red with the "error" class
   function showProblem(input, message) {
      var field = input.closest('.field') || input.parentNode;
      var note = field.querySelector('.field-error');
      if (message) {
         if (!note) {
            note = document.createElement('div');
            note.className = 'field-error';
            note.setAttribute('role', 'alert');
            field.appendChild(note);
         }
         note.textContent = message;
         field.classList.add('error');
         input.setAttribute('aria-invalid', 'true');
      } else {
         if (note) note.remove();
         field.classList.remove('error');
         input.removeAttribute('aria-invalid');
      }
   }

   function checkOne(input, form) {
      var message = problemWith(input, form);
      showProblem(input, message);
      return message === '';
   }

   function boxesIn(form) {
      return Array.prototype.slice.call(form.querySelectorAll('input[name], textarea[name], select[name]'))
         .filter(function (input) { return input.type !== 'hidden' && input.type !== 'submit' && !input.readOnly; });
   }

   function setUp(form) {
      // our own messages replace the browser's popups (without javascript the browser's checks still work)
      form.setAttribute('novalidate', 'novalidate');

      form.addEventListener('submit', function (e) {
         var firstBad = null;
         boxesIn(form).forEach(function (input) {
            if (!checkOne(input, form) && !firstBad) firstBad = input;
         });
         if (firstBad) {
            e.preventDefault();
            firstBad.focus();
         }
      });

      boxesIn(form).forEach(function (input) {
         // check a box when the user leaves it
         input.addEventListener('blur', function () {
            // a short wait so picking from a type-in dropdown counts first
            setTimeout(function () { checkOne(input, form); }, 150);
         });
         // once a box shows a problem, the message updates while they type so it goes away when fixed
         input.addEventListener('input', function () {
            if (input.getAttribute('aria-invalid') === 'true') checkOne(input, form);
         });
         input.addEventListener('change', function () { checkOne(input, form); });
      });
   }

   Array.prototype.forEach.call(document.querySelectorAll('form[data-check-form]'), setUp);
})();
