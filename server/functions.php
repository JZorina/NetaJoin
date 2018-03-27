<?php
define ('SERVERROOT',__DIR__);
date_default_timezone_set('UTC');
require SERVERROOT."/config.php";
require SERVERROOT."/db.php";
require 'PHPMailer/PHPMailerAutoload.php';
require_once(SERVERROOT."/modules/Gender.php");
require_once(SERVERROOT."/modules/Religion.php");
require_once(SERVERROOT."/modules/City.php");
require_once(SERVERROOT."/modules/JoinToNeta/School.php");
require_once(SERVERROOT."/modules/JoinToNeta/NetaCity.php");
require_once(SERVERROOT."/modules/JoinToNeta/_class.php");
require_once(SERVERROOT."/modules/JoinToNeta/HearAboutUs.php");
require_once(SERVERROOT."/modules/JoinToNeta/Nominees.php");
require_once(SERVERROOT."/modules/JoinToNeta/NomineeStatus.php");
$db = new Db($conf->DB->host,$conf->DB->DBName,$conf->DB->userName,$conf->DB->pass,$conf->DB->logError);

$NomineeStatus=new NomineeStatus();
$Nominee=new Nominees();
$Gender = new Gender();
$Religion = new Religion();
$City = new City();
$Class = new _class();
$NetaCity = new NetaCity();
$School = new School();
$mail = new PHPMailer;
$HearAboutUs= new HearAboutUs();


function arrayToTrees ($arr, $id, $parentid, $nestIn)
{
	$trees = array();
	$nestedArrs= array();
	foreach($arr as $index=>$val)
	{
		$nestedArrs[$val[$id]]=$val;
		$nestedArrs[$val[$id]][$nestIn]=array();
	}
	foreach($nestedArrs as $index=>$val)
	{
		if($val[$parentid]!=NULL)
		{
			$nestedArrs[$val[$parentid]][$nestIn][] = $nestedArrs[$index];
		}
		else
		{
			array_push($trees, $nestedArrs[$index]);
		}
	}
	//echo json_encode($trees);
	return $trees;
}
/**
 * takes a flat array, and produces an array of nested objects
 * @param arr - the array on which to perform the changes
 * @param groupBy - which field in the array should be used to destinguish between different objects
 * @param nestedObjectIndex - which index to use to identify a single nested object
 * @param nestedObjects - instructions on which properties to nest, and how to index the array
 * format: [{"nestBy":"arrayIndex", "fieldsToNest":["fieldIndex1"...]}...]
 * @return a nested array
 */
function nestArray($arr, $groupBy, $nestedObjects)
{
	if(!isset($arr)||!isset($groupBy)||!isset($nestedObjects))
	{
		throw new Exception('Bad Input');
		return $arr;
	}
	$afterNesting = array();
	foreach($arr as $row)
	{
		if(!isset($afterNesting[$row[$groupBy]]))
		{
			$afterNesting[$row[$groupBy]] = $row; 
		}
		foreach($nestedObjects as $template)
		{
			if(!isset($afterNesting[$row[$groupBy]][$template['nestIn']]))
			{
				$afterNesting[$row[$groupBy]][$template['nestIn']] = array();
			}
			$nestedProperties = array();
			foreach($template['fieldsToNest'] as $nestedField)
			{
				if(isset($row[$nestedField]))
					$nestedProperties[$nestedField]=$row[$nestedField];
				if(isset($afterNesting[$row[$groupBy]][$nestedField]))
					unset($afterNesting[$row[$groupBy]][$nestedField]);
			}
			if ($row[$template['nestBy']]!=null)
				$afterNesting[$row[$groupBy]][$template['nestIn']][$row[$template['nestBy']]]=$nestedProperties;
		}
	}
	//lose redundant keys in nested objects aaray
	foreach ($afterNesting AS $rowIndex=>$row)
	{
		foreach ($nestedObjects AS $nestedObject)
		{
			$afterNesting[$rowIndex][$nestedObject['nestIn']]=array_values($row[$nestedObject['nestIn']]);
		}
	}
	return array_values($afterNesting);
}
function cutPage($results, $resultsIndex, $page)
{
	$ITEMS_PER_PAGE=15;
	$pages = max(0,floor((count($results)-1)/$ITEMS_PER_PAGE));
	$currPageResults = array_slice($results, $page*$ITEMS_PER_PAGE, $ITEMS_PER_PAGE);
	return array($resultsIndex=>$currPageResults, 'pages'=>$pages);
}
function checkPassword($pwd) {
	//check that the length of the password is above the minimum length
	if (strlen($pwd) < 12) {
		return "password must be at least 12 characters";
	}
	//check that the password contains at least 2 of the three content conditions
	$metConditions=0;
	//check whether the password contains digits
	if (!preg_match("/.*[0-9]+.*/", $pwd)) {
		$metConditions++;
	}
	//check whether the password contains both upper
	if( !preg_match("/.*[a-z]+.*/", $pwd) || !preg_match("/.*[A-Z]+.*/", $pwd) ) {
		$metConditions++;
	}
	//check whether the password contains special symbols
	if( !preg_match("/.*\W+.*/", $pwd) ) {
		$metConditions++;
	}    
	
	if($metConditions>=2)
	{
		return "Password must include at least two of the following: digits, symbols, both upper and lowercase letters";
	}
	else
	{
		return true;
	}
}

function containsDuplicates ($arr){
	$dupe_array = array();
	foreach ($arr as $val) {
		if (isset($dupe_array[$val])) {
			return true;
		}
		$dupe_array[$val]=1;
	}
	return false;
}