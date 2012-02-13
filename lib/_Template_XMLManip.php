<?php
/* Class for manipulating XML via XPath
This file is part of the CAST software project and is subject 
to its terms and conditions, defined in LICENSE.txt */

require_once('MSGS.class.php');

if(class_exists('DOMDocument') && class_exists('DOMXPath')) {

	class _Template_XMLManip {

		var $templateStr;
		var $domdoc;

		var $_msgs;	

		function _Template_XMLManip($filename=null,$str=null) {
			$this->_msg("CONSTRUCT: _Template_XMLManip($filename,$str)");
			if(file_exists($filename) && $filename) {
				$this->_msg("loading file");
				$this->templateStr = file_get_contents($filename);
			} else if(is_string($str)) {
				$this->_msg("loading string");
				$this->templateStr = $str;
			} else 
				$this->_msg("File |$filename| does not exist, no str |$str|");
			if($this->templateStr) {
				$this->domdoc = new DOMDocument();
				$this->domdoc->loadHTML($this->templateStr);
				$this->x = new DOMXPath($this->domdoc);
			}
			$this->_msg("CONSTRUCTED: _Template_XMLManip($filename,$str)");
		}

		function fillAtXPathMatch($fillval,$xpathxpr) {
			$this->_msg("CALL: _Template_XMLManip->fillAtXPath($fillval,$xpathxpr)");
			$filled = false;
			$nodes = $this->x->query($xpathxpr);

			if(!$nodes || !($nodes instanceof DOMNodeList))
				$this->_msg("WARNING: typeof \$nodes: |".gettype($nodes).",$nodes| (xpathxpr bad?) ");
            else {
				$this->_msg("got DOMNodeList (length {$nodes->length})");
				foreach($nodes as $node) {
					if($node instanceof DOMElement) {
						$node->nodeValue = implode(null,array($node->nodeValue,$fillval));
					} else if($node instanceof DOMAttr) {
						$node->value = implode(null,array($node->value,$fillval));
					}
				}
                $filled = true;
			}

			$this->_msg("RETURN: _Template_XMLManip->fillAtXPath() = $filled");
		}
	
		function asXML() {
			return $this->domdoc->saveHTML();
		}

		function _msg() {
			$args = func_get_args();
			$str = implode(null,$args);
			MSGS::add($str);
			//$this->_msgs[] = $str;
		}
	}
} else if(false) {
//else if( version_compare(PHP_VERSION,"5.0.0",'>=') 
// && function_exists('simplexml_load_file') )
	//echo "\nPHP 5 & simplexml_load_file detected";
	class _Template_XMLManip {

		var $templateStr;
		var $simpleXMLobj;

		var $msgs;	

		function _Template_XMLManip($filename=null,$str=null) {
			$this->msgs[] = "CALL: _Template_XMLManip($filename,$str)";
			if(file_exists($filename) && $filename) {
				$this->msgs[] = "loading file";
				$this->templateStr = file_get_contents($filename);
				$this->simpleXMLobj = simplexml_load_file($filename);
			}
			else if($str) {
				$this->msgs[] = "loading string";
				$this->templateStr = $str;
				$this->simpleXMLobj = simplexml_load_string($str);
			}	
			else {
				$this->msgs[]="File |$filename| does not exist, no str |$str|";
            }
			$this->msgs[] = "RETURN: _Template_XMLManip($filename,$str)";
		}

		function fillAtXPathMatch($fillval,$xpathxpr) {
			$this->msgs[] = "CALL: _Template_XMLManip->fillAtXPath($fillval,$xpathxpr)";
			$filled = false;
			$nodes = $this->simpleXMLobj->xpath($xpathxpr);

			if(!is_array($nodes))
				$this->msgs[] = "WARNING: typeof \$nodes: |".gettype($nodes).",$nodes| (xpathxpr bad?) ";
            else {
				foreach($nodes as $node) {
					$children = $node->children();
					$children[0] = $fillval . $children[0];
				}
                $filled = true;
			}

			$this->msgs[] = "RETURN: _Template_XMLManip->fillAtXPath() = $filled";
		}
	
		function asXML() {
			return $this->simpleXMLobj->asXML();
		}
	}

	//echo "\nclass _Template_XMLManip defined using SimpleXML";
}
else {
	$statusstr = "\nPHP 4 ";
	if(include_once('XPath.class.php')) $statusstr .= "\nincluded XPath.class.php";
	if(!class_exists('XPath')) $statusstr .= "\nWARNING: no XPath class";
	//echo $statusstr."\n";

	class _Template_XMLManip {

		var $templateStr;
		var $phpXPathObj;

		var $_msgs;	

		function _Template_XMLManip($filename=null,$str=null) {
			$this->_msg("CALL: _Template_XMLManip($filename,$str)");
			if(file_exists($filename) && $filename) {
				$this->_msg("loading file");
				$this->templateStr = file_get_contents($filename);
				$this->phpXPathObj = new XPath(false);
				$this->phpXPathObj->importFromFile($filename);
			}
			else if($str) {
				$this->_msg("loading string");
				$this->templateStr = $str;
				$this->phpXPathObj = new XPath(false);
				$this->phpXPathObj->importFromString($filename);
			}	
			else {
				$this->_msg("File |$filename| does not exist, no str |$str|");
            }
			$this->_msg("RETURN: _Template_XMLManip()");
		}

		function fillAtXPathMatch($fillval,$xpathxpr) {
			$this->_msg("CALL: _Template_XMLManip->fillAtXPath($fillval,$xpathxpr)");
			$filled = false;
			// Get a list of "Canonical"/unique expressions
			$canonical_xprs = $this->phpXPathObj->match($xpathxpr);
			$this->_msg("\nCanonical Expressions: ");
			$this->_msg($canonical_xprs);
			foreach($canonical_xprs as $canonical_xpr)
				$filled = $filled ||
					$this->phpXPathObj->replaceData($canonical_xpr,$fillval);

			$this->_msg("RETURN: _Template_XMLManip->fillAtXPath() = $filled");
			return $filled;
		}

		function asXML() {
			if(!($rv = $this->phpXPathObj->exportAsXml()))
				echo "\nTEMPLATE ERROR: ".print_r($this->msgs);
			return $rv;
		}

		function _msg($m) {
			$this->_msgs[] = $m;
			//echo "\n".$m;
		}
	}

	//echo "\nclass _Template_XMLManip defined using php.XPath\n";
}

?>
