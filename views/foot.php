

<script type="text/javascript">

	function list2comsep(elm) {
		if(!elm) return null;

		var data = $(elm).val();
		var nls = data.match(/\n/g);
		if(null == nls || nls.length < 1) {
			//alert("Something fucked up\nCant find the list or something.....");
		} else {
			$(elm).val(data.replace(/\n/g, ","));
			//alert("Converted list to comma separated list");
		}
	}

	$("#txtNames, #txtDaysSinceWeekendDuty, #txtDaysSinceWeekdayDuty").on("change", function(e) {
		list2comsep(this);
	});
	$("#selMonth").on("change", function(e) {
		var months = { "JAN": 31, "FEB": 28, "MAR": 31, "APR": 30, "MAY": 31, "JUN": 30, "JUL": 31, "AUG": 31, "SEP": 30, "OCT": 31, "NOV": 30, "DEC": 31 };
		var days = [];
		for(var i = 1; i < months[$(this).val().toUpperCase()] + 1; i++) {
			days.push(i);
		}
		$("#txtCqDays").val(days.join(","));
	});
</script>
	</body>

</html>
