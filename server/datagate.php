<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
date_default_timezone_set("Asia/Jerusalem");
$type = isset($_GET["type"]) ? $_GET["type"] : null;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$time = $_SERVER['REQUEST_TIME'];
require_once ("functions.php");

$data = new StdClass;
$data = json_decode(file_get_contents("php://input"));

switch ($type) {
    // ------------ Cities ------------
    case "GetCities" :
        $ans = $City -> GetCities();
        break;
    case "AddCity" :
        if ($me['type'] == 'admin')
            $ans = $City -> AddCities($data->cities);
            break;
    case "GetNetaCities" :
        $ans = $NetaCity -> GetNetaCities();
        break;
        // ------------ Genders ------------
    case "GetGenders" :
        $ans = $Gender -> GetGenders();
        break;
    case "AddGender" :
        if ($me['type'] == 'admin')
            $ans = $Gender -> AddGender($data->genders);
            break;
            // ------------ Religions ------------
    case "GetReligions" :
        $ans = $Religion -> GetReligions();
        break;
    case "AddReligion" :
        if ($me['type'] == 'admin')
            $ans = $Religion -> AddReligion($data->religions);
            break;
    // ------------ Schools ------------
    case "GetSchools" :
        $ans = $School -> GetSchools();
        break;

    case "GetSchoolsByNetaCityId" :
        $ans = $School -> GetSchoolsByNetaCityId($data->NetaCityId);
        break;

    // ------------ Classes ------------
    case "GetClasses" :
        $ans = $Grade -> GetClasses();
        break;

    // ------------ HearAboutUs ------------
    case "GetHearAboutUsOptions" :
        $ans = $HearAboutUs -> GetHearAboutUsOptions();
        break;

    // ------------ Nominees ------------
    case "SearchNominees" :
        $ans = $Nominee -> SearchNominees($data->search, $data->sorting, $data->desc, $data->page, $data->netaCityFilter, $data->nomineeStatusFilter);
        break;
    case "AddNominee" :
        $ans = $Nominee -> AddNominee($data->nominee);
        break;
    case "UpdateNomineeStatus" :
        $ans = $Nominee -> UpdateNomineeStatus($data->nomineeid,$data->nomineestatusid);
        break;
    // ------------ Status ------------
    case "GetStatuses" :
        $ans = $NomineeStatus -> GetStatuses();
        break;

    default :
		$ans = array("error" => "not valid type");
}


echo json_encode($ans);
