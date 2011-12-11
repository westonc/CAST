<?php

function classdestiny() {
	global $argv;
	static $flag = '--msgsmode=';
	$matches = array();
	$mode = 'silent';	
	if(isset($argv)) {
		foreach($argv as $arg) {
			if(strpos($arg,$flag) === 0) {
				$mode = substr($arg,strlen($flag));
				break;
			}
		}
		return $mode;
	} else if(isset($_REQUEST)) {
		foreach($_REQUEST as $k => $v) {
			if($k == 'msgsmode') {
				$mode = $v;
				break;
			}
		}
	}
	return $mode;
}


if(classdestiny() == 'silent') {
	class A { function speak() { echo "I cannot speak\n"; } }
} else {
	class A { function speak() { echo "The program Smith has grown beyond your ability to control.\n"; } }
}

A::speak();


?>
