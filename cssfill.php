#!/usr/local/bin/php -q
<?php

include_once("lib/Template.php");
include_once("lib/misc.php");
error_reporting(E_ALL ^ E_NOTICE);
//echo("\nNOTE: loaded includes\n");
if(count($argv) < 2) die("\nUsage: cssfill.php <cas|css filename> <xhtml filename>\n\n");

foreach($argv as $arg) {
	if(preg_match('/\.c(s|a|r)s$/',$arg))
		$cssfilename = $arg;
	else if (preg_match('/\.html$/',$arg))
		$htmlfilename = $arg;
}

$t = new Template($htmlfilename,$cssfilename);
$t->fillByCAS();
echo $t->asXML();

?>
