# Web Task 4C: To Do

Main page: `ArtGallery/artMain.php`, opened at http://localhost/artwork/artMain.php

Demo dates: Tue 10/27 or Thu 10/29, 1:00 to 4:30 pm, ECSS 3.101. Sign up on the sheet and bring your laptop.

---

## Part 3: Main page (view and delete)

### 1. Fix the Artworks tab
- [x] Point the Artworks link in `nav.php` to `artMain.php`

### 2. What each painting shows
- [x] Show the image, title, artist name and year for every painting
- [x] Join the Artists table to get the artist's name
- [x] Handle artists with no first name (Rembrandt) so no blank or "NULL" appears
- [x] Sort dropdown: Newest first (default), Title (A to Z), Year (oldest first)
- [x] Keep the chosen sort after a delete
- [ ] Later: make each painting clickable and open `paintingDetail.php?id=...`
  - Show the painting, artist name, description and a link
  - Data available: every painting has a museum link, 285 have a description, 161 have a Wikipedia link

### 3. Delete a painting and everything related to it
- [x] Add a Delete button to each painting (small form, hidden PaintingID, POST)
- [x] Ask for confirmation before deleting
- [x] Delete in this order, inside a transaction (beginTransaction, commit, rollBack on error):
  1. PaintingGenres
  2. PaintingSubjects
  3. Reviews
  4. OrderDetails
  5. Paintings
- [x] Use prepared statements, not SQL built from text
- [x] Redirect back to `artMain.php` after deleting
- [ ] In the report, note that deleting order lines changes past orders on purpose

### 4. Add a painting from its own tab (bonus, 400 points)
- [x] Create `addPainting.php` with the add form
- [x] Add an "Add Artwork" tab to `nav.php`
- [x] Fields: Title (required), Image file name (required, unique, no .jpg), Artist dropdown, Year, Medium
- [x] Fill the Artist dropdown from the Artists table (same pattern as the gallery dropdown in `galleryPaintings.php`)
- [x] Trim inputs, check required fields, show a friendly message for a duplicate image file name
- [x] Use a prepared INSERT
- [x] Redirect to `artMain.php` after a successful add (newest first puts it at the top)
- [ ] Optional: let the form upload a new image, using the `Upload.php` example from the www slides
  - Save it into `images/art/works/square-medium/`
  - Follow the slide's security tips: only allow .jpg, check the file isn't empty, limit the name to letters, numbers, `_`, `-` and `.`
- [x] State at the top of the report that the Part 3 bonus is done

### 4b. Adding artists (type-in artist dropdown)
- [x] Add Artwork: the Artist box is a dropdown you can type in (`<input list>` + `<datalist>`), not case sensitive
- [x] Add Artwork: typing a name that isn't in the list adds that artist with the painting (one transaction, so a failed painting adds no artist)
- [x] New `addArtist.php` page and "Add Artist" tab: first/last name, nationality (type-in dropdown), gender, birth/death years
- [x] Add Artist: blocks duplicate names, checks years, shows existing artists as you type
- [x] Artists page: "Find artist" box to type any artist's name and jump to their paintings
- [x] Shared artist code lives in `artistHelpers.php`

### 5. Demo painting: Starry Night
Values for adding it back after deleting:

| Field | Value |
|---|---|
| Title | Starry Night |
| Artist | Vincent Van Gogh |
| Year | 1889 |
| Medium | Oil on canvas |
| Image file name | 019070 |

Related rows before deleting (screenshot these, then show they drop to zero):

| Table | Rows |
|---|---|
| PaintingGenres | 1 |
| PaintingSubjects | 1 |
| Reviews | 17 |
| OrderDetails | 14 |

- [x] Screenshot the counts before deleting
- [x] Delete Starry Night and screenshot the counts again (database restored afterwards, so Starry Night is back for the live demo)
- [x] Add it back and screenshot it in the list (it gets a new ID and no genres or reviews)
- [ ] Take one screenshot yourself of the "Delete this painting...?" confirm popup (automated screenshots cannot capture browser popups) and add it to Part 3 picture 5

### 6. Create the CSS file
- [ ] Create `css/artGallery.css`
- [ ] Link it in each page's head, after `semantic/semantic.css`
- [ ] Card images: same height for all, `object-fit: cover`
- [ ] Clickable cards: hover lift or shadow, pointer cursor
- [ ] Delete button: placed at the bottom of each card, separate from the painting link
- [ ] Add form: maximum width
- [ ] Site look: background color, heading style, success and error message colors

---

## Part 4: Login (200 points, required)

### 7. Keep the login separate
- [x] Put all login code in its own files: `artLogin.php`, `authenticate.php`, `logout.php`, `checkLogin.php`
- [x] Add `<?php require 'checkLogin.php'; ?>` as the first line of each protected page
- [x] Logout tab: end the session and redirect to `artLogin.php`

### 8. Build the login (same style as the www13 sample)
- [x] `setupUsers.php`: create a users table in the art database and add the 4 users
  - Columns: first name, last name, user ID (unique), password (stored hashed)
  - Users: first1 / last1 / user1 / pass1 through first4 / last4 / user4 / pass4
  - Hash passwords with `password_hash`, like www13pdo's `setupusers.php`
- [x] `artLogin.php`: form with User ID and Password fields that posts to `authenticate.php`
  - Show an error message when sent back after a failed login
  - No tab bar on this page
- [x] `authenticate.php`: look up the user with a prepared statement, check with `password_verify`
  - Success: `session_start()`, save the user's ID and name in `$_SESSION`, redirect to `artMain.php`
  - Failure: redirect to `artLogin.php` with an error
- [x] `checkLogin.php`: `session_start()`; if no user is in the session, redirect to `artLogin.php` and exit
- [x] `logout.php`: clear the session (like `destroy_session_and_data()` in www13pdo's `continue2.php`), redirect to `artLogin.php`
- [x] Optional: show "Logged in as user1" in the tab bar
- [x] Test: all 4 users log in, a wrong password fails, opening `artMain.php` while logged out goes to the login page, Log out works
- [x] Screenshots: users table in phpMyAdmin, login page, failed login, main page after login, after logout

---

## Parts 1 and 2: Report fixes

### 9. Part 1 (www10)
- [ ] Screenshot the filled-in add form before clicking ADD RECORD
- [ ] Screenshot the table being created (import success or the classics Structure tab)
- [ ] Show the address bar in web page screenshots
- [ ] Renumber: create table, view, add, delete
- [ ] One-line caption under each screenshot
- [ ] Retake the first phpMyAdmin screenshot (tooltip covers a row)
- [ ] Code listings: `mysqlitest.php`, `login.php`, `book.sql`, labeled as unmodified professor sample code

### 10. Part 2 (www13)
- [ ] Optional: screenshot a failed login (private window, wrong password)
- [ ] Optional: screenshot logging in as pjones / pjones
- [ ] Code listings: `setupusers.php`, `authenticate2.php`, `continue.php`, `login.php`, labeled as unmodified professor sample code

---

## Report and submission

### 11. Report (Word document)
- [ ] File name: `cs4485-teamNN-netid-Task4C` (your team number and NetID)
- [ ] Heading: course, task, your name
- [ ] At the top: what is done, what is not done, and that the Part 3 bonus is done (if it is)
- [x] Note the naming: files use camelCase, so `art-main.php` is `artMain.php` and `art-login.php` is `artLogin.php`
- [ ] A subheading for each part, with screenshots and captions
- [x] Code listings: all PHP, HTML and CSS you wrote (`artMain.php`, `nav.php`, `addPainting.php`, login files, `css/artGallery.css`)
- [x] SQL export of the art database: `artGalleryExport.sql` in the artwork folder (do this last, after the users table exists): phpMyAdmin > art > Export

### 12. Submit and demo
- [ ] Commit and push all work to git
- [ ] Zip the artwork folder (the inner `ArtGallery` folder), named so it's clearly the artwork folder
- [ ] Upload the report and zip to a Box folder named `Task4C` in your individual Box folder
- [ ] Sign up for a demo slot and practice the flow: log in, view, sort, delete Starry Night, add it back, log out

---

## Safety net
Re-importing `artDatabase.sql` in phpMyAdmin restores all 319 paintings. It also wipes everything else in the art database, including the users table, so run `setupUsers.php` again afterwards.
