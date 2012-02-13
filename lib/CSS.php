<?php
/* Class for quick'n'dirty parsing of CSS file.
This file is part of the CAST software project and is 
subject to its terms and conditions, defined in LICENSE.txt */

require_once('MSGS.class.php');

class CSS {

	var $filename;
	var $cssStr;

	var $selectors;

	function CSS($filename=null) {
		$this->filename = $filename;
		$this->loadCSSfile($filename);
		$this->parseCSSstr();
	}

	function loadCSSfile($filename) {
		$returnVal = true;
		if(file_exists($filename))
			$this->cssStr = file_get_contents($filename);
		else
			$returnVal = false;

		return $returnVal;	
	}

	function addRule($selector,$property,$value) {
		if(empty($selector) || empty($property) || empty($value))
			return false;
		//print "\n\tCALL: addRule($selector,$property,$value)";
		$this->selectors[$selector][$property] = $value;
		//print "\n\tRETURN: addRule()";
		return true;
	}

	function getVal($selector,$property) {
		return $this->selectors[$selector][$property];
	}

	function getVals($selector) {
		return $this->selectors[$selector];
	}

	function selectorExists($selector) {
		return isset($this->selectors[$selector]);
	}

	function getSelectors() {
		return array_keys($this->selectors);
	}
	
	function parseCSSstr() {
		$this->_msg("CALL: CSS->parseCSSstr()");
		$matchsets = Array();
		$str = $this->cssStr;
		//$str = preg_replace('/(\n|\r)/',' ',$this->cssStr);
		$str = preg_replace('/\/\*(.*?)\*\//s',' ',$this->cssStr);
		preg_match_all('/(\s*)([^{]+)\{([^}]*)\}\s+/s',$str,$matchsets,PREG_SET_ORDER);

		foreach($matchsets as $matchset) {
			$selector = trim($matchset[2]);
			$stylerules = trim($matchset[3]);

			$this->_msg('selector: |',$selector,'|');
			$this->_msg('stylerulesStr: |',$stylerules,'|');
			$stylerules = preg_split('/;\s*$\s*/m',$stylerules);
			$this->_msg('stylerules: |',implode('|',$stylerules),'|');
			
			foreach($stylerules as $stylerule) {
				//$stylerule = trim($stylerule);
				$this->_msg('stylerule: ',$stylerule);
				if(count($ruleparts = preg_split('/:\s*/',$stylerule,2)) != 2)
					$this->_msg('badly formed line, skip');
				else {
					$prop = $ruleparts[0];
					$val  = $ruleparts[1];
				}

				//print "\n\tprop: $prop val: $val";
				$this->addRule($selector,$prop,$val);
			}
			//print "\n";
		} 
		$this->_msg('RETURN: CSS->parseCSSstr()');
	}

	function cssStr() {
		$returnVal = false;
		$newCSSstr = array();

		if(!is_array($this->selectors)) {
			die("ERROR: this->selectors isn't an array!");
		}
		else {
			foreach($this->selectors as $selector => $rules) {
				if(!is_array($rules)) {
					die("ERROR: rules isn't an array!");
				}
				else {
					array_push($newCSSstr,$selector," {\n");;
					foreach($rules as $prop => $val) {
						$newCSSstr[] = "\t$prop: $val;\n";
					}
					$newCSSstr[] = "}\n\n";
				}
			}
			$rv = implode(null,$newCSSstr);
		}
		return $rv;
	}

	function save($filename = null) {
		$rv = false;
		$newCSSstr = '';

		if(!$filename) $filename = $this->filename;

		if(!($fp = fopen($filename,"w")) ) {
		}
		else if( ($newCSSstr = $this->cssStr())
			  && fwrite($fp,$newCSSstr,strlen($newCSSstr)) 
		) {
			$rv = true;
		}
		fclose($fp);

		return $rv;
	}

	function _msg() {
		$args = func_get_args();
		$str = implode(null,$args);
		//MSGS::add($str);
	}
}

?>
