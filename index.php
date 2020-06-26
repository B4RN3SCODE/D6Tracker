<?php


$isPost = false;

if(isset($_REQUEST) && isset($_REQUEST["submit"]) && $_REQUEST["submit"] === "true") {
	$isPost = true;
}

include_once("views/head.php");

if(!$isPost) {
?>
<h1>Test</h1>

		<form id="frmMain" action="exempt.php" method="POST">
			<input type="hidden" name="submit" value="true" />

			<table>

				<tr>
					<th><label for="dutyNames">Enter Names:</label>
					</th>
					<th><label for="daysSinceWeekendDuty">Last Weekend Duty:</label>
					</th>
					<th><label for="daysSinceWeekdayDuty">Last Week Day Duty:</label>
					</th>
					<th><label for="periodMonth">For month of:</label>
					</th>
					<th><label for="cqDays">CQ Days (comma separated):</label>
					</th>
					<th><label for="sdDays">SD Days (comma separated):</label>
					</th>
					<th><label for="drcDays">DRC Days (comma separated):</label>
					</th>
				</tr>
				<tr>
					<td>
						<textarea col="15" rows="20" id="txtNames" name="dutyNames"></textarea><br />
					</td>

					<td>
						<textarea col="4" rows="20" id="txtDaysSinceWeekendDuty" name="daysSinceWeekendDuty"></textarea><br />

					</td>

					<td>
						<textarea col="4" rows="20" id="txtDaysSinceWeekdayDuty" name="daysSinceWeekdayDuty"></textarea><br />
					</td>

					<td>
						<select id="selMonth" name="periodMonth">
							<option value="JAN">JAN</option><option value="FEB">FEB</option><option value="MAR">MAR</option><option value="APR">APR</option><option value="MAY">MAY</option><option value="JUN">JUN</option>
							<option value="JUL">JUL</option><option value="AUG">AUG</option><option value="SEP">SEP</option><option value="OCT">OCT</option><option value="NOV">NOV</option><option value="DEC">DEC</option>
						</select>
					</td>

					<td>
						<textarea col="4" rows="20" id="txtCqDays" name="cqDays"></textarea><br />
					</td>


					<td>
						<textarea col="4" rows="20" id="txtSdDays" name="sdDays"></textarea><br />
					</td>


					<td>
						<textarea col="4" rows="20" id="txtDrcDays" name="drcDays"></textarea><br />
					</td>
				</tr>

			</table>
			<button type="submit" id="btnSubmit">Submit</button>
		</form>


<?php } else {

var_dump($_REQUEST);

}


include_once("views/foot.php");


?>
