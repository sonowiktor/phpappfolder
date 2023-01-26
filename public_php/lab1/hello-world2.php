/**
* multi-line
* comments here
*/
<?php
$eol = "\n";
$output = '<!DOCTYPE html>' . $eol;
$output .= '<head lang="en">' . $eol;
$output .= '<meta http-equiv="Content-Type" content="text/html; 
charset=utf-8">' . $eol;
$output .= '<meta name="Author" content="Clinton Ingrams">' . $eol;
$output .= '<title>Hello World</title>' . $eol;
$output .= '</head>' . $eol;
$output .= '<body>' . $eol;
$output .= '<h2>Hello World</h2>' . $eol;
$output .= '<p>Today\'s date is ' . gmdate("M d Y") . '</p>' . $eol;
$output .= '</body>' . $eol;
$output .= '</html>' . $eol;
echo $output;
