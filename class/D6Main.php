<?php

class D6Main {

	public static $_Months = array(
		"JAN"	=>	31, "FEB"	=>	28,
		"MAR"	=>	31, "APR"	=>	30,
		"MAY"	=>	31, "JUN"	=>	30,
		"JUL"	=>	31, "AUG"	=>	31,
		"SEP"	=>	30, "OCT"	=>	31,
		"NOV"	=>	30, "DEC"	=>	31,
	);


	public static $_DutyRunners = array(
		"CQ"	=>	1,
		"SD"	=>	2,
		"DRC"	=>	2
	);




	public $_dutyNames = array();
	public $_daysSinceWeekendDuty = array();
	public $_daysSinceWeekdayDuty = array();

	// vars to track lastest days since duties
	public $_updatedDaysSinceWeekendDuty = array();
	public $_updatedDaysSinceWeekdayDuty = array();
	//////


	public $_periodMonth;
	public $_cqDays = array();
	public $_sdDays = array();
	public $_drcDays = array();
	// array of arrays of days
	public $_exemptDays = array();

	// main roster with all days
	public $_masterRoster = array();


	// track if theyve already had duty
	public $_hadDutyThisMonth = array();






	public function __construct($dutyNames = "", $daysSinceWeekendDuty = "", $daysSinceWeekdayDuty = "", $periodMonth = "", $cqDays = "", $sdDays = "", $drcDays = "", $depInj = false) {

		// shitty way for dependency injection
		if(!$depInj) {

			$this->_periodMonth = $periodMonth;

			foreach(array("dutyNames", "daysSinceWeekendDuty", "daysSinceWeekdayDuty", "cqDays", "sdDays", "drcDays") as $i => $v) {

				$this->parseData("_$v", ${$v});

			}

			$this->initUpdatedLastDutyDays();

		}
	}


	public function newD6Main($parameters = array()) {

		foreach($parameters as $var => $value) {
			$this->{$var} = $value;
		}

		$this->initUpdatedLastDutyDays();
	}

	public function initUpdatedLastDutyDays() {
		$this->updateLastDutyDays($this->_daysSinceWeekdayDuty, $this->_daysSinceWeekendDuty);
	}


	public function parseData($var = "", $strdata) {

		$this->{$var} = str_getcsv(rtrim($strdata, ","));

	}


	public function buildExemptDayList($data = array()) {

		foreach($data as $i => $str) {
			if(!empty($str) && strlen($str) > 0) {
				$this->_exemptDays[$i] = str_getcsv(rtrim($str, ","));
				// subtract one so the day index matches up with other day indexes
				// because itll pass 1,2,3,4 and first day indexes are 0,1,2,3
				foreach($this->_exemptDays[$i] as $edi => $edv) {
					$this->_exemptDays[$i][$edi]--;
				}
			} else {
				$this->_exemptDays[$i] = array();
			}
		}
	}



	public function buildExemptTable() {

		$tblStr = "<form method=\"POST\" action=\"build.php\" id=\"frmExempt\">";
		$tblRowsStr = "";

		// add in hidden elms
		foreach($this->_dutyNames as $i => $n) {
			$rowColor = "";
			if($i % 2 == 0) {
				$rowColor = " style=\"background-color:#b8c0d5;\"";
			}
			$tblStr .= "<input type=\"hidden\" name=\"_{$n}\" value=\"\">";
			$tblRowsStr .= "<tr{$rowColor}><td style=\"padding:5px;\">{$n}</td>";
			// make one for every day of the month
			for($d = 1; $d < self::$_Months[$this->_periodMonth] + 1; $d++) {
				$tblRowsStr .= "<td style=\"padding:5px;\"><label>{$d}</label><input type=\"checkbox\" name=\"{$n}\" value=\"{$d}\"></td>";
			}
			$tblRowsStr .= "</tr>";
		}

		$tblStr .= "<table>{$tblRowsStr}</table><button type=\"submit\" id=\"btnSubmit\">Submit</button></form>";

		return $tblStr;

	}




	public function buildMasterRosterTable() {

		$tblStr = "<table><tr><th>Name</th>";
		foreach($this->_masterRoster as $mri => $mrdata) {
			$hday = $mri + 1;
			$tblStr .= "<th>{$hday}</th>";
		}
		$tblStr .= "</tr>";

		// add in hidden elms
		foreach($this->_dutyNames as $i => $n) {
			$rowColor = "";
			if($i % 2 == 0) {
				$rowColor = " style=\"background-color:#b8c0d5;\"";
			}
			$tblStr .= "<tr{$rowColor}><td style=\"padding:5px;\">{$n}</td>";
			// make one for every day of the month
			foreach($this->_masterRoster as $mri => $mrdata) {
				if($this->isExempt($this->_dutyNames[$i], $mri)) {
					$mrdata[$i] = "A";
				}
				$tblStr .= "<td style=\"padding:5px;\">{$mrdata[$i]}</td>";
			}
			$tblStr .= "</tr>";
		}

		$tblStr .= "</table>";

		return $tblStr;

	}





	public function buildDataForDay($day = 1) {

		$weekend = $this->isWeekend($day);

		// set day data based on weekend or not
		$dayData = $this->_updatedDaysSinceWeekdayDuty;

		if($weekend) {
			$dayData = $this->_updatedDaysSinceWeekendDuty;
		}
		//////////



		// iterate through and add to lastest day
		foreach($dayData as $i => $num) {
			$dayData[$i]++;
		}
		//////////////

		$dayDuties = $this->findDutiesForDay($day);
		if(!empty($dayDuties)) {

			$highestDays = $this->findHighestLastDuties($dayDuties, $weekend, $day);

			$assignedDuties = $this->assignDuty($dayDuties, $highestDays, $day);
			foreach($assignedDuties as $ai => $data) {
				$nameIndex = $this->findNameIndex($data["name"]);
				$dayData[$nameIndex] = $data["days"];
			}
		}



		$this->addToMasterRoster($dayData);

		if($weekend) {
			$this->updateLastDutyDays(array(), $dayData);
		} else {
			$this->updateLastDutyDays($dayData, array());
		}

	}


	public function resetHasHadDutyLog() {
		echo "<h2 style=\"color:red;\">Reseting everyone whos had duty</h2>";
		$this->_hadDutyThisMonth = array();
	}


	public function hasHadDuty($name) {
		return in_array($name, $this->_hadDutyThisMonth);
	}


	public function hasEveryoneHadDuty() {
		foreach($this->_dutyNames as $i => $n) {
			if(!$this->hasHadDuty($n)) {
				return false;
			}
		}
	}


	public function addToHasHadDuty($name) {
		if(!$this->hasHadDuty($name)) {
			$this->_hadDutyThisMonth[] = $name;
		}
	}



	public function assignDuty($duties, $data, $day) {

		$returnData = array();
		foreach($duties as $idx => $d) {
			$canAssign = self::$_DutyRunners[$d];
			$assigned = 0;
			while($assigned < $canAssign) {
				$data[0]["days"] = $d;
				$returnData[] = $data[0];
				$assigned++;
				$this->addExemption($data[0]["name"], $day);
				$this->addToHasHadDuty($data[0]["name"]);
				array_splice($data, 0, 1);
			}
		}

		return $returnData;

	}


	public function addExemption($name, $day) {

		$this->_exemptDays[$name][] = $day;
	}


	public function findNameIndex($name) {
		foreach($this->_dutyNames as $idx => $n) {
			if($n == $name) {
				return $idx;
			}
		}
	}



	public function findHighestLastDuties($duties, $weekend = false, $day = 1) {

		$numDuties = 0;
		foreach($duties as $idx => $dut) {
			$numDuties += (1 * self::$_DutyRunners[$dut]);
		}

		$daysToCheck = $weekend ? $this->_updatedDaysSinceWeekendDuty : $this->_updatedDaysSinceWeekdayDuty;

		// "name" => num_of_days
		$highestDays = array();

		$currentHighest = array("name" => "", "days" => 0);
		$dayIndex = 0;

		if($this->hasEveryoneHadDuty()) {
			$this->resetHasHadDutyLog();
		}


		// log tries in case things dont match up
		// with exempt days and has had duty this month
		// if too many, reset the has had duty list
		$numTries = 0;

		// wee need to find ix amount of duties so
		// iterate this many times
		for($ix = 0; $ix < $numDuties; $ix++) {

			// go through days and find them
			foreach($daysToCheck as $index => $numOfDays) {

				$numTries++;
				if($numTries > 10000) { $this->resetHasHadDutyLog(); }
				// subtract one from day for the is exempt function
				// because the day will be passed as day of month
				// day first day of month (day o in array) will be passed as 1
				if($numOfDays > $currentHighest["days"] && !$this->isExempt($this->_dutyNames[$index], $day-1) && !$this->hasHadDuty($this->_dutyNames[$index])) {
					$currentHighest = array("name" => $this->_dutyNames[$index], "days" => $numOfDays);
					$dayIndex = $index;
				}

			}

			$highestDays[] = $currentHighest;
			$currentHighest = array("name" => "", "days" => 0);
			unset($daysToCheck[$dayIndex]);
			$dayIndex = 0;


		}



		return $highestDays;
	}




	public function isExempt($name, $day) {
		return in_array($day, $this->_exemptDays[$name]);
	}



	public function findDutiesForDay($day = 1) {

		$duties = array();

		if(in_array($day, $this->_cqDays)) {
			$duties[] = "CQ";
		}

		if(in_array($day, $this->_sdDays)) {
			$duties[] = "SD";
		}

		if(in_array($day, $this->_drcDays)) {
			$duties[] = "DRC";
		}


		return $duties;
	}





	public function addToMasterRoster($data = array()) {

		if(!empty($data)) {
			$this->_masterRoster[] = $data;
		}
	}



	public function updateLastDutyDays($weekday = array(), $weekend = array()) {

		if(!empty($weekday)) {

			foreach($weekday as $i => $d) {
				if(strtolower($d) == "cq" || strtolower($d) == "sd" || strtolower($d) == "drc") {
					$weekday[$i] = 0;
				}
			}
			$this->_updatedDaysSinceWeekdayDuty = $weekday;
		}

		if(!empty($weekend)) {

			foreach($weekend as $i => $d) {
				if(strtolower($d) == "cq" || strtolower($d) == "sd" || strtolower($d) == "drc") {
					$weekend[$i] = 0;
				}
			}
			$this->_updatedDaysSinceWeekendDuty = $weekend;
		}

	}


	public function isWeekend($day = 1) {

		/*
		 * TODO
		 * Add year logic... this will change
		 * based on value passed on first form */

		$dayStr = ($day > 9) ? "{$day}" : "0{$day}";
		$date = "2019-{$this->_periodMonth}-{$dayStr}";

		$dayName = date("D", strtotime($date));

		return (strtolower($dayName) == "sat" || strtolower($dayName) == "sun");

	}



	public static function sessionStart() {
		if(isset($_SESSION["PHPSESSID"]) && $_SESSION["PHPSESSID"] == true && isset($_COOKIE["PHPSESSID"]) && !(is_null(session_id())))
			return false;

		if(isset($_SESSION["PHPSESSID"])) unset($_SESSION["PHPSESSID"]);
		session_start();
		$_SESSION["PHPSESSID"] = true;
		return true;
	}


	public static function sessionStop() {
		if(isset($_SESSION["PHPSESSID"]))
			unset($_SESSION["PHPSESSID"]);

		if(session_id() != "") {
			session_destroy();
		}
	}


	/*
	 * setCookie
	 * Sets a cookie
	 *
	 * @param name string cookie name
	 * @param value string cookie value
	 * @param expr int expire hours
	 * @param pth string path
	 * @param domain string domain
	 * @param secr string https
	 * @param httpOnly string http
	 * @return false on failure
	 */
	public static function setCookie($name = null, $value = null, $expr = 1, $pth = "/", $domain = null, $secr = null, $httpOnly = null) {
		if(!isset($name) || empty($name))
			return false;

		$expr = (time() + (3600 * $expr));
		$domain = (is_null($domain)) ? ((isset($_SERVER["HTTP_HOST"])) ? $_SERVER["HTTP_HOST"] : BASE_PTH) : $domain;
		setcookie($name, $value, $expr, $pth, $domain, $secr, $httpOnly);
	}


	public static function unsetCookie($name) {
		if(!isset($name) || empty($name))
			return false;

		$this->setCookie($name, "", -1);
	}



	public static function unsetAllCookies() {
		foreach($_COOKIE as $name => $props) {
			$this->unsetCookie($name);
		}
	}


	public static function redirect($str) {
		if(!empty($str)) {
			// TODO
			// add ob_* stuff to flush ob
			// session stuff?
			// move to base application class and call $baseApp->redirect(str)?

			//header( 'HTTP/1.1 301 Moved Permanently' );
			header( "Location: {$str}");
			exit;
		}
	}


}


?>
