<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$sessdir = dirname(dirname(__FILE__)).'/session';
ini_set('session.save_path', $sessdir);

include_once("class/D6Main.php");

$requiredVars = array("dutyNames", "daysSinceWeekendDuty", "daysSinceWeekdayDuty", "periodMonth", "cqDays", "sdDays", "drcDays");

$errorStr = "";

foreach($requiredVars as $i => $v) {
	if(!isset($_REQUEST[$v]) || empty($_REQUEST[$v])) {
		$errorStr .= "<center><h1 style=\"color:red;\">Incorrect data for {$v}.</h1></center><br />";

	}
}

include_once("views/head.php");


echo "<h1>Enter Days They Are Exempt</h1>";


if(!empty($errorStr)) {
	echo $errorStr;
	die();
} else {


	$app = new D6Main($_REQUEST["dutyNames"], $_REQUEST["daysSinceWeekendDuty"], $_REQUEST["daysSinceWeekdayDuty"], $_REQUEST["periodMonth"], $_REQUEST["cqDays"],
						$_REQUEST["sdDays"], $_REQUEST["drcDays"]);

	D6Main::sessionStart();

	$_SESSION["dutyNames"] = $app->_dutyNames;
	$_SESSION["daysSinceWeekendDuty"] = $app->_daysSinceWeekendDuty;
	$_SESSION["daysSinceWeekdayDuty"] = $app->_daysSinceWeekdayDuty;
	$_SESSION["periodMonth"] = $app->_periodMonth;
	$_SESSION["cqDays"] = $app->_cqDays;
	$_SESSION["sdDays"] = $app->_sdDays;
	$_SESSION["drcDays"] = $app->_drcDays;

	echo $app->buildExemptTable();


}



echo <<<scr

		<script type="text/javascript">
			$("#btnSubmit").on("click", function(e) {
				e.preventDefault();

				$.each($("input[type=hidden]"), function(i, elm) {
					var inName = elm.name.replace(/\_/g, "");
					var exDaysElms = $("input[name="+inName+"]:checked");
					var exDays = [];
					for(var i = 0; i < exDaysElms.length; i++) {
						exDays.push(exDaysElms[i].value);
					}
					$(elm).val(exDays.toString());
				});

				$("#frmExempt").submit();
			});

		</script>

scr;

include_once("views/foot.php");


?>
