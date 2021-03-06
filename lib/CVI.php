<?php
/* Class for Interpreting Values in Content Adress Sheet rules
This file is part of the CAST software project and is subject 
to its terms and conditions, defined in LICENSE.txt */


//error_reporting(E_ALL ^ E_NOTICE);
require_once('MSGS.class.php');

// *** Context Value Interpreter ***
class CVI { 

	var $context;

	function CVI($c=null) {
		$this->setContext($c);
	}

	function setContext($c=null) {
		return ($this->context = $c);
	}

	function interpret($value) {
		$this->_msg("CALL: CVI->interpret($value)");
		$matches = array();
		$content = '(unprocessed!)';
		if($this->context) 
			extract($this->context);
		/* Rule #1: quoted content is treated as a literal c-string
		if(preg_match('/^("|\')(.*)[^\\]\\1$/',$value,$matches)) { */
		$this->_msg("evalstr: \"return $value;\"");
		$content = eval("return $value;");
		if(is_object($content)) {
			foreach(array('asXML','asHTML','__toString') as $method) {
				if(method_exists($content,$method)) {
					$content = $content->$method();
					break;
				}
			}
		}
		$this->_msg("RETURN: CVI->interpret($value) = |$content|");
		return $content;
	}

	function _msg() {
		$args = func_get_args();
		$str = implode(null,$args);
		MSGS::add($str);
		//echo "\n<br/>".implode('',$args);
		//array_push($this->_msgs,$args);
	}
} 

//echo("\nParsed CVI.php ".__LINE__."\n");

?>
