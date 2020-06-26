<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once("class/D6Main.php");


D6Main::sessionStart();

var_dump(session_id());

$app = new D6Main("","","","","","","", true);
$app->newD6Main(array(
	"_dutyNames"	=>	$_SESSION["dutyNames"],
	"_daysSinceWeekendDuty"	=>	$_SESSION["daysSinceWeekendDuty"],
	"_daysSinceWeekdayDuty"	=>	$_SESSION["daysSinceWeekdayDuty"],
	"_periodMonth"	=>	$_SESSION["periodMonth"],
	"_cqDays"	=>	$_SESSION["cqDays"],
	"_sdDays"	=>	$_SESSION["sdDays"],
	"_drcDays"	=>	$_SESSION["drcDays"]
));

// populate the request data so we can build the exempt var in object
$requestData = array();
foreach($app->_dutyNames as $idx => $name) {
	$requestData[$name] = $_REQUEST["_{$name}"];
}

$app->buildExemptDayList($requestData);
$_SESSION["exemptDays"] = $app->_exemptDays;
unset($requestData);
/////////////////////////////////////////

for($x = 1; $x < D6Main::$_Months[$app->_periodMonth]+1; $x++) {
	$app->buildDataForDay($x);
}


include_once("views/head.php");

echo "<h1>Final Data</h1>";


echo $app->buildMasterRosterTable();

include_once("views/foot.php");



?>
