<?php
/*
 * Builds the HTML-only version of the site into docs/ for GitHub Pages.
 *   php build-static.php
 * The static version has no database, no emails and no admin page:
 *   - the demo form opens the visitor's email app (mailto)
 *   - the GPS demo only shows the visitor's own location, nothing is sent
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$root = __DIR__;
$out  = $root . '/docs';
if (!is_dir($out)) mkdir($out, 0755, true);

// Each page is rendered in its own PHP process so helper functions are not declared twice.
$pages = ['index.php' => 'index.html', 'privacy.php' => 'privacy.html'];
foreach ($pages as $src => $dest) {
  $code = 'define("XG_STATIC", true); $_SERVER["REQUEST_METHOD"] = "GET"; $_SERVER["SCRIPT_FILENAME"] = ' . var_export($src, true) . ';'
        . ' chdir(' . var_export($root, true) . '); require ' . var_export($root . '/' . $src, true) . ';';
  $proc = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
  $html = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]);
  fclose($pipes[1]); fclose($pipes[2]); proc_close($proc);
  if (trim($err) !== '') { fwrite(STDERR, "Errors while building $src:\n$err\n"); exit(1); }
  if (!$html || stripos($html, '</html>') === false) { fwrite(STDERR, "Failed to build $src\n"); exit(1); }
  if (preg_match('/(Warning|Notice|Fatal error|Deprecated):/', $html)) { fwrite(STDERR, "PHP error in $src output\n"); exit(1); }
  file_put_contents("$out/$dest", $html);
  echo "built docs/$dest (" . strlen($html) . " bytes)\n";
}
file_put_contents("$out/.nojekyll", '');   // serve files as-is on GitHub Pages
echo "done\n";
