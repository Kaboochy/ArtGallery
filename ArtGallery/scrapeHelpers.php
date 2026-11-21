<?php
//scrapeHelpers.php reads the saved WikiArt pages in scrape/pages and pulls out the artist and painting data (task 9)
//it only ever opens files on this computer, it never goes to the real wikiart.org web site
//PHP's DOMDocument turns each saved page into a tree and DOMXPath finds the parts we need, like jQuery selectors
//
//what is read from an artist page (for example edouard-manet.html):
//   name (the h3 in the article, or <meta itemprop="name">), birth and death dates (itemprop birthDate / deathDate),
//   nationality (itemprop nationality), biography (<p itemprop="description">) and the Wikipedia link
//what is read from a painting page (for example edouard-manet__olympia-1863.html):
//   title (the h3 in the article), artist (itemprop creator), year (itemprop dateCreated), style, genre, media,
//   location, dimensions, description and the picture
//the picture is the .jpg with the same name as the page, saved next to it (or the image a "Save as complete" page points to)

//the folder with the saved pages
define('SCRAPE_FOLDER', 'scrape/pages/');

//WikiArt says "French", our artists table says "France", so the common ones are turned into countries
function nationalityToCountry($nationality) {
   $countries = array(
      'french' => 'France', 'dutch' => 'Netherlands', 'spanish' => 'Spain', 'italian' => 'Italy',
      'german' => 'Germany', 'american' => 'USA', 'english' => 'England', 'british' => 'England',
      'belgian' => 'Belgium', 'flemish' => 'Belgium', 'russian' => 'Russia', 'austrian' => 'Austria',
      'norwegian' => 'Norway', 'swiss' => 'Switzerland', 'mexican' => 'Mexico', 'greek' => 'Greece'
   );
   $key = strtolower(trim($nationality));
   return isset($countries[$key]) ? $countries[$key] : trim($nationality);
}

//squashes all the spaces and new lines in a piece of text into single spaces
function cleanText($text) {
   return trim(preg_replace('/\s+/u', ' ', html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

//the last 3 or 4 digit number in a piece of text, "January 23, 1832" gives 1832, nothing found gives 0
function yearIn($text) {
   if (preg_match_all('/\b(\d{3,4})\b/', (string)$text, $matches))
      return (int)end($matches[1]);
   return 0;
}

//text of the first element the xpath query finds, or '' when it finds nothing
function firstText($xpath, $query, $context = null) {
   $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
   return ($nodes && $nodes->length) ? cleanText($nodes->item(0)->textContent) : '';
}

//an attribute of the first element the xpath query finds
function firstAttr($xpath, $query, $attr, $context = null) {
   $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
   return ($nodes && $nodes->length) ? trim($nodes->item(0)->getAttribute($attr)) : '';
}

//wikiart lists facts as <li><s>Label:</s> value</li>, this finds the value for one label
function labelledValue($xpath, $label, $context = null) {
   //.// looks inside $context only, // looks in the whole page
   $query = ($context ? './/' : '//') . "li[s[starts-with(normalize-space(.), '$label')]]";
   $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
   if (!$nodes || !$nodes->length) return '';
   $li = $nodes->item(0);
   $text = cleanText($li->textContent);
   //take the label off the front
   return trim(preg_replace('/^' . preg_quote($label, '/') . '\s*/u', '', $text));
}

//"oil, canvas" becomes "Oil on canvas" like the rest of our paintings table
function mediumFromMedia($media) {
   $parts = array_values(array_filter(array_map('trim', explode(',', $media))));
   if (count($parts) == 0) return '';
   if (count($parts) == 2) return ucfirst($parts[0]) . ' on ' . $parts[1];
   return ucfirst(implode(', ', $parts));
}

//"130.5 x 190 cm" gives height 131 and width 190 (wikiart writes height first)
function sizeFromDimensions($dimensions) {
   if (preg_match('/([\d.]+)\s*[x×]\s*([\d.]+)\s*cm/u', $dimensions, $m))
      return array((int)round((float)$m[1]), (int)round((float)$m[2]));
   return array(0, 0);
}

//the first sentence of a description, used as the short excerpt
function excerptOf($text) {
   if ($text == '') return '';
   if (preg_match('/^(.{40,300}?[.!?])(\s|$)/u', $text, $m)) return $m[1];
   return mb_substr($text, 0, 200, 'UTF-8');
}

//opens one saved page, the xml line makes DOMDocument read it as UTF-8 so letters like é come out right
function loadSavedPage($path) {
   $html = file_get_contents($path);
   $doc = new DOMDocument();
   libxml_use_internal_errors(true);   //real web pages are never perfect html, so the warnings are hidden
   $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
   libxml_clear_errors();
   return new DOMXPath($doc);
}

//the description: the <p itemprop="description">, or the first paragraph of the description or Wikipedia tab
//(some pages, like Berthe Morisot's, only have the Wikipedia tab)
function descriptionOf($xpath) {
   $text = firstText($xpath, "//p[@itemprop='description']");
   if ($text == '') $text = firstText($xpath, "//div[@id='info-tab-description']/p[not(@class)]");
   if ($text == '') $text = firstText($xpath, "//div[@id='info-tab-wikipediaArticle']/p[not(@class)]");
   return $text;
}

//finds the picture for a painting page: first a .jpg with the same name as the page,
//then the file a "Save Page As, complete" copy points to in its _files folder
function findLocalImage($path, $xpath) {
   $sameName = preg_replace('/\.html?$/i', '.jpg', $path);
   if (file_exists($sameName)) return $sameName;

   $src = firstAttr($xpath, "//img[@itemprop='image']", 'src');
   if ($src != '' && !preg_match('#^(https?:)?//#i', $src)) {
      $local = dirname($path) . '/' . rawurldecode(preg_replace('/[?#].*$/', '', $src));
      if (file_exists($local)) return $local;
   }
   return '';
}

//reads an artist page
function scrapeArtist($xpath, $file) {
   $box = $xpath->query("//div[contains(@class,'wiki-layout-artist-info')]")->item(0);

   $name = firstText($xpath, './/article/h3', $box);
   if ($name == '') $name = firstAttr($xpath, "//meta[@itemprop='name']", 'content');
   if ($name == '') $name = cleanText(explode(' - ', firstAttr($xpath, "//meta[@property='og:title']", 'content'))[0]);

   $link = '';
   $wikiLink = $xpath->query("//li[s[starts-with(normalize-space(.), 'Wikipedia')]]//a");
   if ($wikiLink->length) $link = trim($wikiLink->item(0)->getAttribute('href'));
   if ($link == '') $link = firstAttr($xpath, "//meta[@itemprop='sameAs']", 'content');

   return array(
      'file'        => $file,
      'name'        => $name,
      'born'        => firstText($xpath, "//*[@itemprop='birthDate']"),
      'died'        => firstText($xpath, "//*[@itemprop='deathDate']"),
      'yearOfBirth' => yearIn(firstText($xpath, "//*[@itemprop='birthDate']")),
      'yearOfDeath' => yearIn(firstText($xpath, "//*[@itemprop='deathDate']")),
      'nationality' => nationalityToCountry(firstText($xpath, "//*[@itemprop='nationality']")),
      'movement'    => labelledValue($xpath, 'Art Movement:'),
      'details'     => descriptionOf($xpath),
      'link'        => $link
   );
}

//reads a painting page
function scrapePainting($xpath, $path, $file) {
   $box = $xpath->query("//*[contains(@itemtype,'schema.org/Painting')]")->item(0);

   $title = firstText($xpath, './/article/h3', $box);
   if ($title == '') $title = firstText($xpath, './/header/h1', $box);
   if ($title == '') $title = cleanText(explode(',', firstAttr($xpath, "//meta[@property='og:title']", 'content'))[0]);

   $artist = firstText($xpath, ".//*[@itemprop='creator']//*[@itemprop='name']", $box);
   if ($artist == '') $artist = firstText($xpath, './/header/h2', $box);

   //the list of facts (date, style, media...) is searched in the whole page, not just the painting box:
   //wikiart puts a <script> template inside the list and the html reader moves the list items out of the box
   list($height, $width) = sizeFromDimensions(labelledValue($xpath, 'Dimensions:'));
   $description = descriptionOf($xpath);

   return array(
      'file'        => $file,
      'title'       => $title,
      'artist'      => $artist,
      'year'        => yearIn(firstText($xpath, "//*[@itemprop='dateCreated']")),
      'style'       => labelledValue($xpath, 'Style:'),
      'genre'       => labelledValue($xpath, 'Genre:'),
      'medium'      => mediumFromMedia(labelledValue($xpath, 'Media:')),
      'location'    => labelledValue($xpath, 'Location:'),
      'width'       => $width,
      'height'      => $height,
      'description' => $description,
      'excerpt'     => excerptOf($description),
      'wikiLink'    => firstAttr($xpath, "./meta[@itemprop='sameAs']", 'content', $box),
      'image'       => findLocalImage($path, $xpath)
   );
}

//reads every saved page in the folder and sorts out which are artists and which are paintings
//gives back array('artists' => list, 'paintings' => list, 'skipped' => file names that were neither)
function scrapeFolder($folder = SCRAPE_FOLDER) {
   $result = array('artists' => array(), 'paintings' => array(), 'skipped' => array());
   $files = array_merge(glob($folder . '*.html'), glob($folder . '*.htm'));
   sort($files);
   foreach ($files as $path) {
      $file = basename($path);
      $xpath = loadSavedPage($path);
      if ($xpath->query("//*[contains(@itemtype,'schema.org/Painting')]")->length)
         $result['paintings'][] = scrapePainting($xpath, $path, $file);
      else if ($xpath->query("//*[@itemprop='birthDate']")->length || $xpath->query("//div[contains(@class,'wiki-layout-artist-info')]")->length)
         $result['artists'][] = scrapeArtist($xpath, $file);
      else
         $result['skipped'][] = $file;
   }
   return $result;
}

//puts the paintings under their artist, an artist with paintings but no saved artist page gets only a name
function groupByArtist($scraped) {
   $groups = array();
   foreach ($scraped['artists'] as $artist) {
      $groups[strtolower($artist['name'])] = array('artist' => $artist, 'paintings' => array());
   }
   foreach ($scraped['paintings'] as $painting) {
      $key = strtolower($painting['artist']);
      if (!isset($groups[$key])) {
         $groups[$key] = array('artist' => array(
            'file' => '', 'name' => $painting['artist'], 'born' => '', 'died' => '', 'yearOfBirth' => 0, 'yearOfDeath' => 0,
            'nationality' => '', 'movement' => '', 'details' => '', 'link' => ''), 'paintings' => array());
      }
      $groups[$key]['paintings'][] = $painting;
   }
   return $groups;
}

//the image file name used for a scraped painting: "wa-" and the page name, like wa-olympia-1863
function scrapedImageName($painting) {
   $base = preg_replace('/\.html?$/i', '', $painting['file']);
   //the saved names are artist__painting, only the painting part is used
   $parts = explode('__', $base);
   $base = end($parts);
   $base = preg_replace('/[^A-Za-z0-9_-]+/', '-', $base);
   return substr('wa-' . trim($base, '-'), 0, 50);
}

//makes the SQL insert statements for everything that was scraped
//the artist is only added if they are not in the table yet, and each painting finds its artist id by name
function buildInsertSql($groups, $pdo) {
   $q = function ($value) use ($pdo) {
      return ($value === '' || $value === null) ? 'NULL' : $pdo->quote($value);
   };
   $sql = "-- SQL made by scrape.php from the saved WikiArt pages in " . SCRAPE_FOLDER . "\n";
   $sql .= "-- made on " . date('Y-m-d H:i') . "\n\n";
   foreach ($groups as $group) {
      $artist = $group['artist'];
      list($first, $last) = splitArtistName($artist['name']);
      $sql .= "-- " . $artist['name'] . "\n";
      $sql .= "INSERT INTO Artists (FirstName, LastName, Nationality, YearOfBirth, YearOfDeath, Details, ArtistLink)\n"
            . "SELECT " . $q($first) . ", " . $q($last) . ", " . $q($artist['nationality']) . ", "
            . (int)$artist['yearOfBirth'] . ", " . (int)$artist['yearOfDeath'] . ", "
            . $q($artist['details']) . ", " . $q($artist['link']) . "\n"
            . "FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM Artists WHERE "
            . ($first == '' ? "(FirstName IS NULL OR FirstName = '')" : "FirstName = " . $q($first))
            . " AND LastName = " . $q($last) . ");\n\n";

      $artistIdSql = "(SELECT ArtistID FROM Artists WHERE "
            . ($first == '' ? "(FirstName IS NULL OR FirstName = '')" : "FirstName = " . $q($first))
            . " AND LastName = " . $q($last) . " ORDER BY ArtistID LIMIT 1)";
      //a painting is skipped if its image name is used already, or the artist already has a painting with that title
      foreach ($group['paintings'] as $painting) {
         $imageName = $q(scrapedImageName($painting));
         $sql .= "INSERT INTO Paintings (ArtistID, ImageFileName, Title, YearOfWork, Medium, Width, Height, Description, Excerpt, WikiLink)\n"
               . "SELECT " . $artistIdSql . ", " . $imageName . ", " . $q($painting['title']) . ", "
               . (int)$painting['year'] . ", " . $q($painting['medium'] != '' ? $painting['medium'] : 'Oil on canvas') . ", "
               . ($painting['width'] ? (int)$painting['width'] : 'NULL') . ", " . ($painting['height'] ? (int)$painting['height'] : 'NULL') . ", "
               . $q(htmlspecialchars($painting['description'], ENT_NOQUOTES)) . ", " . $q($painting['excerpt']) . ", " . $q($painting['wikiLink']) . "\n"
               . "FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM Paintings WHERE ImageFileName = " . $imageName
               . " OR (Title = " . $q($painting['title']) . " AND ArtistID = " . $artistIdSql . "));\n";
      }
      $sql .= "\n";
   }
   return $sql;
}
?>
