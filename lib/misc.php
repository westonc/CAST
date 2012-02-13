<?php

/* Random Functions
This file is part of the CAST software project and is subject 
to its terms and conditions, defined in LICENSE.txt */

/* (BTW: many of these functions aren't necessary -- they have built-in 
analogues in PHP. It's a project goal to remove them). */

function var2str($var)
{
    ob_start();
	echo("<pre>");
	print_r($var);
	echo("</pre>");
    $str = ob_get_contents();
    ob_end_clean();

    return $str;
}

function precho($str)
{
	echo "<pre>".var2str($str)."</pre>";
}

function file2aa($filename)
{
	$fptr = 0;
	$aa = array();
	$matches = array();

	if(!($fptr = fopen($filename,'r')))
	{
		$aa['status'] = "error: couldn't open file |$filename|";
		//echo "<BR>error: couldn't open file |$filename|";
	} 
	else
	{
		//$aa['status'] = "opened file |$filename|";
		while(!feof($fptr))
		{
			$fileline = fgets($fptr,8192);
			if(preg_match("/^#/",$fileline))
			{ //then it's a comment line, and we skip it 
			}
			else if(preg_match("/^(.*?):\s*(.*)/",$fileline,$matches))
			{   //then it's to be read into the hash
				$key = $matches[1];
				$val = $matches[2];
				$aa[$key] = $val;
			}
			else
			{   //we assume it's a comment or something else we canignore 
			}
		}
		fclose($fptr);
	}

	return $aa;
}

function getFileExt($filename)
{
	$fileExt = '';

	$lastdot = strrpos($filename,'.');
	if($lastdot)
	{
		$fileExt = substr($filename,$lastdot);
	}
	
	return $fileExt;	
}

function getFileBase($filename)
{
	$fileBase = '';

	$lastdot = strrpos($filename,'.');
	if($lastdot)
		$fileBase = substr($filename,0,$lastdot);
	else
		$fileBase = $filename;
	
	return $fileBase;	
}

function cleanFileName($filename)
{
	return preg_replace("/[^A-Za-z0-9_]/",'',$filename);
}

function boolstr($val)
{
	if($val === true)
		return 'true';
	else if($val === false)
		return 'false';
	else
		return $val;
}

if(function_exists('mysql_query') && !function_exists('mysql_qoperate'))
{
	function mysql_qoperate($query,&$obj,$method,$link=null)
	{
		$returnVal = TRUE;

		if($link)
			$result = mysql_query($query,$link);
		else
			$result = mysql_query($query);

		if(!$result) $returnVal = mysql_error();
		else
			while($row = mysql_fetch_array($result,MYSQL_BOTH))
				$obj->$method($row);

		return $returnVal;
	}
}

?>
