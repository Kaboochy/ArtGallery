//based on lab 10 walkthroughs 06 (fade, slide) and 07 (hover preview)
//runs right away so the content doesnt flash before fading in

//no animations for people who set reduce motion
if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
   $.fx.off = true;
}

//fade in
//hidden here instead of in css so the page still shows without javascript
//fade the whole area at once, 319 separate fades would be slow
$('main > .ui.segment, main > .ui.message, main > .ui.button').hide().fadeIn(700);

//slide panels
function addSlidePanel(panel, openLabel, closeLabel, startOpen) {
   var button = $('<button type="button" class="ui button slideButton"></button>');
   button.text(startOpen ? closeLabel : openLabel);
   button.attr('aria-expanded', startOpen ? 'true' : 'false');
   button.insertBefore(panel);

   if (!startOpen) panel.hide();

   button.on('click', function () {
      var isOpen = button.attr('aria-expanded') == 'true';
      button.text(isOpen ? openLabel : closeLabel);
      button.attr('aria-expanded', isOpen ? 'false' : 'true');
      panel.stop(true, true).slideToggle(400);
   });
}

//artMain.php sets data-start-open when the sort was changed
var sortForm = $('#sortForm');
if (sortForm.length) {
   addSlidePanel(sortForm, 'Sort options', 'Hide sort options', sortForm.attr('data-start-open') == 'yes');
}

//tables dont slide smoothly so the details table gets a div around it
var detailsTable = $('.ui.definition.table');
if (detailsTable.length) {
   detailsTable.wrap('<div class="slidePanel"></div>');
   addSlidePanel(detailsTable.parent(), 'Show details', 'Hide details', true);
}

//hover preview
//only with a mouse, touch screens cant hover
if (window.matchMedia('(hover: hover)').matches) {
   //one handler on the cards group instead of one per card
   $('.ui.cards')
      .on('mouseenter', '.card > .image img', showPreview)
      .on('mouseleave', '.card > .image img', removePreview)
      .on('mousemove', '.card > .image img', movePreview);

   //the back button can restore a page with a preview still showing
   $(window).on('pagehide', removePreview);
}

function showPreview(e) {
   $('#preview').remove();

   //text() and attr() so a title with html cant change the page
   var preview = $('<div id="preview"></div>');
   preview.append($('<img>').attr('src', $(this).attr('src')).attr('alt', ''));
   preview.append($('<p></p>').text($(this).attr('alt')));
   $('body').append(preview);

   $(this).addClass('gray');
   movePreview(e);
   preview.fadeIn(300);
}

function removePreview() {
   $('.gray').removeClass('gray');
   $('#preview').remove();
}

//flips to the other side near the edge of the window
function movePreview(e) {
   var preview = $('#preview');
   if (!preview.length) return;

   var win = $(window);
   var left = e.pageX + 30;
   var top = e.pageY - 10;

   if (left + preview.outerWidth() > win.scrollLeft() + win.width())
      left = e.pageX - preview.outerWidth() - 30;

   if (top + preview.outerHeight() > win.scrollTop() + win.height())
      top = win.scrollTop() + win.height() - preview.outerHeight() - 10;

   preview.css({ left: Math.max(win.scrollLeft(), left), top: Math.max(win.scrollTop(), top) });
}
