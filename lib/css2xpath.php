<?php
/* Function (and class) for converting CSS selectors to XPath expressions
   (c) 2006 Weston Cann weston@canncentral.org
   All Rights Reserved - This isn't released under any license at all
   yet, and unless I gave it to you personally, you don't have the rights
   to use it. */

function css2xpath($css) {
	$tokens = CSS2XPath::tokenize($css);
	$transtokens = CSS2XPath::translateTokens($tokens);
	return '//'.join('',$transtokens);
}

class CSS2XPath {

	function tokenize($css) {

		$tokens = array();
		$wcss = trim($css); //working css selector

		while($wcss) {

			$nextsp = strpos($wcss,' ');
			$nextspgt = strpos($wcss,' >');	
			$nextgt = strpos($wcss,'>');	

			if(($nextsp === FALSE) && ($nextgt === FALSE)) 
			{
				if($wcss) array_push($tokens,$wcss);
				$wcss = '';
			}
			else if(($nextgt === FALSE) || !($nextsp === FALSE))
			{
				$tok = substr($wcss,0,$nextsp);
				array_push($tokens,$tok);
				array_push($tokens,' ');
				$wcss = ltrim(substr($wcss,$nextsp+1));
			}
			else if(($nextgt < $nextsp) || ($nextsp === FALSE))
			{
				$tok = substr($wcss,0,$nextgt);
				array_push($tokens,$tok);
				array_push($tokens,'>');
				$wcss = ltrim(substr($wcss,$nextgt+1));
			}
			else if($nextsp == $nextspgt)
			{
				$tok = substr($wcss,0,$nextspgt);
				array_push($tokens,$tok);
				array_push($tokens,'>');
				$wcss = ltrim(substr($wcss,$nextspgt+2));
			}
			else if(!($nextsp === FALSE))
			{
				$tok = substr($wcss,0,$nextsp);
				array_push($tokens,$tok);
				array_push($tokens,' ');
				$wcss = ltrim(substr($wcss,$nextsp+1));
			}
			else
				array_push($tokens,"ERROR: [wcss:$wcss|nextsp:$nextsp|nextspgt:$nextspgt|nextgt:$nextgt]");
		}

		return $tokens;
	}

	function translateTokens($tokens) {

		$transtokens = array();
		$toklen = count($tokens);
		for($i=0;$i<$toklen;$i++) {
			if($tokens[$i] == ' ') {
				$transtokens[$i] = '//'; 
			}
			else if($tokens[$i] == '>') {
				$transtokens[$i] = '/'; 
			}
			else {
				$transtokens[$i] = CSS2XPath::translateSimpleSelector($tokens[$i]);
			}
		}

		return $transtokens;
	}

	function translateSimpleSelector($token)
	{
		$matches = array();
		if(preg_match('/^(\w+)/',$token,$matches))
			$type = $matches[1];
		if(preg_match('/\.(\w+)/',$token,$matches))
			$class = $matches[1];
		if(preg_match('/\#(\w+)/',$token,$matches))
			$id = $matches[1];

		$xpr = isset($type) ? $type : '*';
		if(isset($class)) $xpr .= CSS2XPath_class2xpath($class); // see below
		if(isset($id)) $xpr .= "[@id='$id']";

		//return "ID=|$id| CLASS=|$class| TYPE=|$type|";
		return $xpr;
	}


}

// PHP5's XML handlers can handle the complex full correct XPath expression 
// that's equivalent to a CSS class selector. The PHP4 XPath Library I've used 
// to provide backward compatibility, on the other hand, doesn't seem to manage 
// well with the full expression, but can manage with a close hack. So below 
// we wrap the conversion in a function which is defined at runtime depending 
// the detected PHP version (checking for simplexml for good measure).

//if( version_compare(PHP_VERSION,"5.0.0",">=")
// && function_exists('simplexml_load_file') )
if(false)
// for some reason, with PHP 5.2, this breaks. Need to fix.
{
	function CSS2XPath_class2xpath($class)
	{ return "[contains(concat(' ',@class,' '),concat(' ','$class',' '))]"; }
}
else
{
	function CSS2XPath_class2xpath($class)
	{ return "[contains(@class,'$class')]"; } // allows multiple classes but false matches
	//{ return "[@class='$class']"; }         // non-ambiguous, but single-class only
	                                          // decisions, decisions...
}

?>
