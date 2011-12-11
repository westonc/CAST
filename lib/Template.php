<?php
/* Class for basic CSS-based templating system
   (c) 2006 Weston Cann weston@canncentral.org
   All Rights Reserved - This isn't released under any license at all
   yet, and unless I gave it to you personally, you don't have the rights
   to use it. */

//error_reporting(E_ALL ^ E_NOTICE);
include_once('MSGS.class.php');
include_once('misc.php');
include_once('css2xpath.php');
include_once('CSS.php');
include_once('CVI.php');
include_once('_Template_XMLManip.php');

class Template {

	var $xmlManip;
	var $cas;

	var $_msgs;

	function Template($tplFilename,$casFilename=null) {
		$this->_msgs = array();
		$this->_msg("CALL: Template($tplFilename,$casFilename)");
		$this->loadTemplate($tplFilename);
		$this->loadCAS($casFilename);
		$this->_msg("RETURN: Template($tplFilename,$casFilename)");
	}

	function loadTemplate($filename) {
		$this->_msg("CALL: Template->loadTemplate($filename)");
		$returnVal = true;
		if(file_exists($filename)) {
			$this->_msg("File |$filename| exists... loading...");
			$this->xmlManip = new _Template_XMLManip($filename);
		}
		else {
			$this->_msg("File |$filename| does not exist");
			$returnVal = false;
		}

		$this->_msg("RETURN: Template->loadTemplate($filename) = $returnVal");
		return $returnVal;	
	}

	function loadCAS($filename) {
		$this->_msg("CALL: Template->loadCAS($filename)");
		if(file_exists($filename)) {
			$this->cas = new CSS($filename);
			$rv = true;
		} else {
			$rv = false;
			$this->_msg("couldn't open cas file |$filename|");
		}
		$this->_msg("RETURN: Template->loadCAS($filename) = $rv");
		return $rv;
	}

	function fillByCAS($passedContext=null) {
		$this->_msg("CALL: Template->fillByCAS(".($objOrContext?'something':'nothing').")");
		if($this->cas) {
			$context = array();

			if($this->cas->selectorExists('@context')) {
				$casContextVals = $this->cas->getVals('@context');
				foreach($casContextVals as $key => $val) {
					$context[$key] = eval("return $val;");
				}
			}
			$this->_msg("Read Context: ",var2str($context));

			if(isset($passedContext)) {
				if(is_object($passedContext)) {
					foreach(get_object_vars($passedContext) as $k => $v) 
						$context[$k] = $v;
				} else if(is_array($passedContext)) {
					foreach(get_object_vars($passedContext) as $k => $v) 
						$context[$k] = $v;
				}
			}

			$selectors = $this->cas->getSelectors();
			$cvi = new CVI($context);
			$this->_msg("CVI: ",var2str($cvi));

			$this->_msg("Selector count: ".count($selectors));
			foreach($selectors as $selector) {
				if($content = $this->cas->getVal($selector,'content')) {
					$this->_msg("filling $selector using rule content: |$content|");
					$content = $cvi->interpret($content);
					$this->_msg("interpreted content: |$content|");
					$this->fill($selector,$content);
				}
			}
		}
		else
			$this->_msg("no CAS?");
		$this->_msg("RETURN: Template->fillByCAS()");
	}

	function fill($address,$value) {
		$this->_msg("CALL: Template->fill($address,$value)");

		/* *Very* broad assumption follows in the code. We allow both XPath
		expressions and CSS selectors for addressing. How do we test which
	 	one the user passed?  

		Quick and Dirty heuristic: anything starting with a '/' is XPath.
		It certainly isn't valid CSS, and passes for the most important 
		XPath cases, including canonical expressions. */

		if(substr($address,0,1) == '/') {
			$xpath = $address;
			$this->_msg("address is xpath: |$xpath|");
		} else {
			$xpath = css2xpath($address);
			$this->_msg("address is css: |$address| --> xpath: |$xpath|");
		}
		$nodes = $this->xmlManip->fillAtXPathMatch($value,$xpath);

		$this->_msg("RETURN: Template->fill() = |$filled| (with $value) ");
		return $filled;
	}

	function asXML($removeVersionTag = true) { 
		$xml = $this->xmlManip->asXML(); 
		if($removeVersionTag) $xml = preg_replace('/<\?xml version="[^"]+"\?>/','',$xml);
		return $xml;
	}

	function _msg() {
		$args = func_get_args();
		$str = implode(null,$args);
		MSGS::add($str);	
		//array_push($this->_msgs,$m);
	}

	function __toString() {
		return 'Template Object'; //var2str($this);
	}
} 

define('REMOVE_VTAG',true);
define('KEEP_VTAG',false);

//echo("\nParsed Template.php ".__LINE__."\n");

?>
