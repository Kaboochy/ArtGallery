//artEffects.js has the animation effects for the artworks and painting detail pages
//the effects are based on the lab 10 walkthroughs (06 for fade and slide, 07 for the hover preview)
//this file is loaded at the bottom of the page so everything it looks for already exists
//it runs right away so the page content doesnt flash before it fades in

//turns the animations off for people who set their computer to reduce motion
if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
   $.fx.off = true;
}

//fade in (walkthrough 06)
//the content is hidden here and not in the css so the page still shows if javascript is off
//the whole area fades at once because fading all 319 cards one by one would be slow
$('main > .ui.segment, main > .ui.message, main > .ui.button').hide().fadeIn(700);


//slide panels (walkthrough 06)
//adds a button in front of a panel, clicking the button slides the panel open or closed
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

//artworks page: the sort form stays closed unless a sort other than the default is being used
var sortForm = $('.ui.secondary.segment form');
if (sortForm.length) {
   addSlidePanel(sortForm, 'Sort options', 'Hide sort options', sortForm.find('select').val() != 'newest');
}

//painting detail page: tables dont slide smoothly so the details table gets a div around it
var detailsTable = $('.ui.definition.table');
if (detailsTable.length) {
   detailsTable.wrap('<div class="slidePanel"></div>');
   addSlidePanel(detailsTable.parent(), 'Show details', 'Hide details', true);
}


//hover preview (walkthrough 07)
//only on computers with a mouse, a touch screen has nothing to hover with
if (window.matchMedia('(hover: hover)').matches) {
   //the handlers are on the group of cards so 319 cards dont each need their own
   $('.ui.cards')
      .on('mouseenter', '.card > .image img', showPreview)
      .on('mouseleave', '.card > .image img', removePreview)
      .on('mousemove', '.card > .image img', movePreview);

   //the back button can bring back a page that still has a preview showing
   $(window).on('pagehide', removePreview);
}

function showPreview(e) {
   $('#preview').remove();

   //text() and attr() are used so a title with html characters cant change the page
   var preview = $('<div id="preview"></div>');
   preview.append($('<img>').attr('src', $(this).attr('src')).attr('alt', ''));
   preview.append($('<p></p>').text($(this).attr('alt')));
   $('body').append(preview);

   //make the small image gray and fade the preview in
   $(this).addClass('gray');
   movePreview(e);
   preview.fadeIn(300);
}

function removePreview() {
   $('.gray').removeClass('gray');
   $('#preview').remove();
}

//the preview follows the mouse, it moves to the other side near the edge of the window
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
