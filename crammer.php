<?php
$crammeron_db_version = "0.01";

function array_push_key (&$array, $key, $value) {
$array[$key] = $value;
}

function unique_content($text) {
$junkwords = array('a', 'an', 'the', 'and', 'of', 'i', 'to', 'is', 'in', 'with', 'for', 'as', 'that', 'on', 'at', 'this', 'my', 'was', 'our', 'it', 'you', 'we', 'about', 'after', 'all', 'almost', 'along', 'also', 'amp', 'another', 'any', 'are', 'area', 'around', 'available', 'back', 'be', 'because', 'been', 'being', 'best', 'better', 'big', 'bit', 'both', 'but', 'by', 'c', 'came', 'can', 'capable','com', 'control', 'could', 'course', 'd', 'dan', 'day', 'decided', 'did', 'didn', 'different', 'div', 'do', 'doesn', 'don', 'down', 'drive', 'e', 'each', 'easily', 'easy', 'edition', 'end', 'enough', 'even', 'every', 'example', 'few', 'find', 'first', 'found', 'from', 'get', 'go', 'going', 'good', 'got', 'gt', 'had', 'hard', 'has', 'have', 'he', 'her', 'here', 'how','http', 'if', 'into', 'isn', 'just', 'know', 'last', 'left', 'li', 'like', 'little', 'll', 'long', 'look', 'lot', 'lt', 'm', 'made', 'make', 'many', 'mb', 'me', 'menu', 'might', 'mm', 'more', 'most', 'much', 'name', 'nbsp', 'need', 'new', 'no', 'not', 'now', 'number', 'off', 'old', 'one', 'only', 'or', 'original', 'other', 'out', 'over', 'part', 'place', 'point', 'pretty', 'probably', 'problem', 'put', 'quite', 'quot', 'r', 're', 'really', 'results', 'right', 's', 'same', 'saw', 'see', 'set', 'several', 'she', 'sherree', 'should', 'since', 'size', 'small', 'so', 'some', 'something', 'special', 'still', 'stuff', 'such', 'sure', 'system', 't', 'take', 'than', 'their', 'them', 'then', 'there', 'these', 'they', 'thing', 'things', 'think', 'those', 'though', 'through', 'time', 'today', 'together', 'too', 'took', 'two', 'up', 'us', 'use', 'used', 'using', 've', 'very', 'want', 'way', 'well', 'went', 'were', 'what', 'when', 'where', 'which', 'while', 'white', 'who', 'will', 'would', 'your');
$ascvals = array('a' => '&#97;','b' => '&#98;','c' => '&#99;','d' => '&#100;','e' => '&#101;','f' => '&#102;','g' => '&#103;','h' => '&#104;','i' => '&#105;','j' => '&#106;','k' => '&#107;','l' => '&#108;','m' => '&#109;','n' => '&#110;','o' => '&#111;','p' => '&#112;','q' => '&#113;','r' => '&#114;','s' => '&#115;','t' => '&#116;','u' => '&#117;','v' => '&#118;','w' => '&#119;','x' => '&#120;','y' => '&#121;','z' => '&#122;');
$newarray = array();
$article = explode(" ", $text);
foreach($junkwords as $word) {
$arr1 = str_split($word);
      foreach($arr1 as $letters) {
      $newword .= $ascvals[$letters];
      }
array_push_key($newarray,$word,$newword);
$newword = "";
}

foreach ($newarray as $key => $words) {
$text = str_replace(" " . $key . " ", " " . $words . " ", $text);
}
return $text;
}

add_filter('content_save_pre', 'unique_content');
?>