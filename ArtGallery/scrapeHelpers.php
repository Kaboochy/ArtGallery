<?php
//reads saved wikiart pages only, never the real site
//DOMDocument builds a tree and DOMXPath finds parts of it, like jquery selectors

define('SCRAPE_FOLDER', 'scrape/pages/');

//wikiart says french, our table says france
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

//squash spaces and new lines into single spaces
function cleanText($text) {
   return trim(preg_replace('/\s+/u', ' ', html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

//last 3 or 4 digit number, "january 23, 1832" gives 1832, 0 if none
function yearIn($text) {
   if (preg_match_all('/\b(\d{3,4})\b/', (string)$text, $matches))
      return (int)end($matches[1]);
   return 0;
}

//'' when the query finds nothing
function firstText($xpath, $query, $context = null) {
   $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
   return ($nodes && $nodes->length) ? cleanText($nodes->item(0)->textContent) : '';
}

function firstAttr($xpath, $query, $attr, $context = null) {
   $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
   return ($nodes && $nodes->length) ? trim($nodes->item(0)->getAttribute($attr)) : '';
}

//wikiart lists facts as <li><s>Label:</s> value</li>
function labelledValue($xpath, $label, $context = null) {
   //.// searches inside $context only, // searches the whole page
   $query = ($context ? './/' : '//') . "li[s[starts-with(normalize-space(.), '$label')]]";
   $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
   if (!$nodes || !$nodes->length) return '';
   $li = $nodes->item(0);
   $text = cleanText($li->textContent);
   //take the label off the front
   return trim(preg_replace('/^' . preg_quote($label, '/') . '\s*/u', '', $text));
}

//"oil, canvas" becomes "Oil on canvas" to match our table
function mediumFromMedia($media) {
   $parts = array_values(array_filter(array_map('trim', explode(',', $media))));
   if (count($parts) == 0) return '';
   if (count($parts) == 2) return ucfirst($parts[0]) . ' on ' . $parts[1];
   return ucfirst(implode(', ', $parts));
}

//"130.5 x 190 cm" gives height 131, width 190 (wikiart writes height first)
function sizeFromDimensions($dimensions) {
   if (preg_match('/([\d.]+)\s*[x×]\s*([\d.]+)\s*cm/u', $dimensions, $m))
      return array((int)round((float)$m[1]), (int)round((float)$m[2]));
   return array(0, 0);
}

//first sentence, used as the excerpt
function excerptOf($text) {
   if ($text == '') return '';
   if (preg_match('/^(.{40,300}?[.!?])(\s|$)/u', $text, $m)) return $m[1];
   return mb_substr($text, 0, 200, 'UTF-8');
}

//the xml line makes DOMDocument read utf-8 so letters like é come out right
function loadSavedPage($path) {
   $html = file_get_contents($path);
   $doc = new DOMDocument();
   libxml_use_internal_errors(true);   //real pages are never perfect html so warnings are hidden
   $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
   libxml_clear_errors();
   return new DOMXPath($doc);
}

//some pages (like morisot's) only have the wikipedia tab, so fall back to it
function descriptionOf($xpath) {
   $text = firstText($xpath, "//p[@itemprop='description']");
   if ($text == '') $text = firstText($xpath, "//div[@id='info-tab-description']/p[not(@class)]");
   if ($text == '') $text = firstText($xpath, "//div[@id='info-tab-wikipediaArticle']/p[not(@class)]");
   return $text;
}

//first a .jpg named like the page, then the file a complete save points to in its _files folder
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

function scrapePainting($xpath, $path, $file) {
   $box = $xpath->query("//*[contains(@itemtype,'schema.org/Painting')]")->item(0);

   $title = firstText($xpath, './/article/h3', $box);
   if ($title == '') $title = firstText($xpath, './/header/h1', $box);
   if ($title == '') $title = cleanText(explode(',', firstAttr($xpath, "//meta[@property='og:title']", 'content'))[0]);

   $artist = firstText($xpath, ".//*[@itemprop='creator']//*[@itemprop='name']", $box);
   if ($artist == '') $artist = firstText($xpath, './/header/h2', $box);

   //facts are searched in the whole page: wikiart puts a <script> in the list and the parser moves the items out of the box
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

//artist and painting pages are told apart by what is inside them
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

//an artist with paintings but no saved page gets only a name
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

//"wa-" plus the page name, like wa-olympia-1863
function scrapedImageName($painting) {
   $base = preg_replace('/\.html?$/i', '', $painting['file']);
   //saved names are artist__painting, only the painting part is used
   $parts = explode('__', $base);
   $base = end($parts);
   $base = preg_replace('/[^A-Za-z0-9_-]+/', '-', $base);
   return substr('wa-' . trim($base, '-'), 0, 50);
}

//artists are only added if missing, paintings find their artist id by name
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
      //skip a painting if its image name is used or the artist already has that title
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
