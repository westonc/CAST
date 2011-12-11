<?php

$_MSGS = array();
$_MSGS_loghandle = null; 

define('MSGS_SILENT',0);
define('MSGS_LOG',1);
define('MSGS_COMMENT',2);
define('MSGS_ECHO',4);
define('MSGS_HTML',8);
define('MSGS_DIE',16);

$_MSGS_modes = array(
	'silent'	=> MSGS_SILENT,
	'log' 		=> MSGS_LOG,
	'comment' 	=> MSGS_COMMENT,
	'echo' 		=> MSGS_ECHO,
	'html' 		=> MSGS_HTML,
	'die' 		=> MSGS_DIE
);

if(!isset($MSGS_mode)) {
	function _MSGS_infermode() {
		global $argv, $_MSGS_modes;
		static $flag = '--msgsmode=';
		$matches = array();
		$mode = null; 
		if(isset($argv)) {
			foreach($argv as $arg) {
				if(strpos($arg,$flag) === 0) {
					$mode = substr($arg,strlen($flag));
					break;
				}
			}
		} else if(isset($_REQUEST)) {
			foreach($_REQUEST as $k => $v) {
				if($k == 'msgsmode') {
					$mode = $v;
					break;
				}
			}
		}
		if(!is_numeric($mode)) $mode = $_MSGS_modes[$mode];
		if(!$mode) $mode = MSGS_SILENT;
		return $mode;
	}
	$MSGS_mode = _MSGS_infermode();
}

if($MSGS_mode & MSGS_LOG) {
	function _MSGS_inferlog() {
		global $_MSGS_loghandle;
		static $flag = '--msgslog=';
		$log_filename = 'msgs.log';
		if(isset($argv)) {
			foreach($argv as $arg) {
				if(strpos($arg,$flag) === 0) {
					$mode = substr($arg,strlen($flag));
					break;
				}
			}
		} else if(isset($_REQUEST)) {
			foreach($_REQUEST as $k => $v) {
				if($k == 'msgslog') {
					$mode = $v;
					break;
				}
			}
		}
		if(!($_MSGS_loghandle = fopen($log_filename,"a")))
			throw new Exception("Can't open MSGS::log $log_filename");
	}
	_MSGS_inferlog();
}

function _MSGS_relay($msg,$mode=null) {
	global $_MSGS_mode,$_MSGS_loghandle;
	if(!is_numeric($mode)) $mode = $_MSGS_mode;
	if($mode == MSGS_SILENT) return true;
	if($mode & MSGS_LOG) fwrite($_MSGS_loghandle,"$msg\n");
	if($mode & MSGS_COMMENT) echo "\n<!-- $msg -->";
	if($mode & MSGS_ECHO) echo "$msg\n";
	if($mode & MSGS_HTML) echo "\n<br/>",htmlspecialchars($msg);
	if($mode & MSGS_DIE) echo "\n (MSGS is configured to die on the previous)";
}

class MSGS {

	function add() {
		global $_MSGS;
		$args = func_get_args();
		$mode = is_numeric($args[count($args)-1]) ?
			array_pop($args) : null;
		$msg = implode(null,$args);
		array_push($_MSGS,$msg);
		_MSGS_relay($msg,null);
	}

	function getAll() {
		global $_MSGS;
		return $_MSGS;
	}

	function asStr($joinstr=null) {
		global $_MSGS;
		return join($joinstr ? $joinstr : '',$_MSGS);
	}

	function logAll($file) {
		global $_MSGS;
		if(!file_exists($file))
			echo("\n<br/>log failure - |$file| doesn't exist");
		else if(!($fp == fopen($file,"a")) ) 
			echo("\n<br/>log failure - can't open |$file| for write/append");
		else {
			fwrite($fp,"~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n");
			fwrite($fp,MSGS::asStr("\n"));
			fclose($fp);
		}
	}
}
