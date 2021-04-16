<?php
/**
 * Arguments available:
 * --dir      - the output directory, 
 * --chapter  - starting chapter, default 1
 * --limit    - chapter number you want to end at, default 146
 * --help    - prints this args
 */
$args = array();
for ($i = 1; $i < count($argv); $i++) {
       if (preg_match('/^--([^=]+)=(.*)/', $argv[$i], $match)) {
              $args[$match[1]] = $match[2];
       }
}

// create output directory
$dir = isset($args['dir']) ? $args['dir'] : 'chapters';
if (!is_dir($dir))
       mkdir($dir);

// get chapter & limit 
$max_chapters = isset($args['limit']) ? $args['limit'] : 146;
$chapter = isset($args['chapter']) ? $args['chapter'] : 1;

// delete leftover jpgs
array_map('unlink', glob("$dir/*.jpg"));

// 
for ($chapter; $chapter <= $max_chapters; $chapter++) {
       // set title and url
       // note: spelling of 'jujustu' is different for pages > 1
       $title = ($chapter > 1) ? "jujustu-kaisen-chapter-$chapter" : "jujutsu-kaisen-chapter-$chapter";
       $url = isset($args['url']) ? $args['url'] : "https://jujustukaisen.com/manga/$title";

       // get html
       $html = file_get_contents($url);
       $doc = new DOMDocument();
       @$doc->loadHTML($html);
       $tags = $doc->getElementsByTagName('img');


       // download images from parsing
       $filenames = [];
       foreach ($tags as $index => $tag) {
              $img = $tag->getAttribute('src');
              $filenames[$index] = "$dir/pic-$index.jpg";
              file_put_contents($filenames[$index], file_get_contents($img));
       }

       // create the convert command
       $cmd = "convert ";
       foreach ($filenames as $file)
              $cmd .= $file . ' ';

       $output = "$dir/$title.pdf";
       if (file_exists($output))
              unlink($output);

       $cmd .= $output;
       passthru($cmd, $return);

       // cleanup jpgs
       foreach ($filenames as $file)
              unlink($file);

       echo "Chapter $chapter complete." . PHP_EOL;
}
