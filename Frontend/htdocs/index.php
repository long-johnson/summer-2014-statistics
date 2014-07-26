<?php pm_Context::init('extended-plesk-statistics'); ?>
<html>
  <head>
    <!--<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      //google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['2004',  1000,      400],
          ['2005',  1170,      460],
          ['2006',  660,       1120],
          ['2007',  1030,      540]
        ]);

        var options = {
          title: 'Company Performance',
          hAxis: {title: 'Year', titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>-->
  </head>
  <body>
    <!--<div id="chart_div" style="width: 900px; height: 500px;"></div>-->
	<?php
	$filename = "/usr/local/psa/var/modules/extended-plesk-statistics/subscriptions.txt";
	$subscriptions = array();
	$file = fopen($filename, "r");
	do {
		$newline = fgets($file);
		if ($newline != "")
			$subscriptions[] = trim($newline);
	} while (!feof($file));
	fclose($file);
	
	// form
	echo '<form name="plot" method="post">';
	// combobox for subscription selection
	echo '<select name="subscription" id="subscription" onchange="this.form.submit();">';
	echo '<option disabled>Select a subscription...</option>';
	for($i = 0, $size = count($subscriptions); $i < $size; ++$i) {
		echo '<option ';
		if ($subscriptions[$i] == $_POST['subscription'])
			echo 'selected ';
		echo'value="' . $subscriptions[$i] . '">' . $subscriptions[$i] . '</option>';
	}
	echo '</select>';
	// for first load
	if (!isset($_POST['subscription']))
		$_POST['subscription'] = $subscriptions[0] ;
	// combobox for website selection
	if (isset($_POST['subscription']))
	{
		// go to subscription directory and read sites.txt
		$filename = "/usr/local/psa/var/modules/extended-plesk-statistics/" . trim($_POST['subscription']) . "/sites.txt"; // "/sites.txt";
		$sites = array();
		$file = fopen($filename, "r");
		do {
			$newline = fgets($file);
			if ($newline != "")
				$sites[] = trim($newline);
		} while (!feof($file));
		fclose($file);
		
		echo '<br>';
		echo '<select name="site" id="site" onchange="this.form.submit();">';
		echo '<option disabled>Select a website...</option>';
		for($i = 0, $size = count($sites); $i < $size; ++$i) {
			echo '<option ';
			if ($sites[$i] == $_POST['site'])
				echo 'selected ';
			echo'value="' . $sites[$i] . '">' . $sites[$i] . '</option>';
		}
		// and option to show the whole subscription info
		echo '<option ';
		if ($_POST['site'] == "WHOLE_SUBSCRIPTION")
			echo 'selected ';
		echo 'value="WHOLE_SUBSCRIPTION">Whole subscription</option>';
		echo '</select>';
	}
	
	// выбор базовой временной единицы
	if (!isset($_POST['timetype']))
		$_POST['timetype'] = "hour";
	echo '<select name="timetype" id="timetype" onchange="this.form.submit();">';
	echo '<option disabled>Select a basic time unit...</option>';
	echo '<option '; if ($_POST['timetype'] == "hour") echo 'selected '; echo 'value="hour">Hour</option>';
	echo '<option '; if ($_POST['timetype'] == "day") echo 'selected '; echo 'value="day">Day</option>';
	echo '<option '; if ($_POST['timetype'] == "month") echo 'selected '; echo 'value="month">Month</option>';
	echo '</select>'
	
	
	if (!isset($_POST['begindate'])){
		$_POST['begindate'] = "2014-07-25";
	}
	if (!isset($_POST['enddate'])){
		$_POST['enddate'] = "2014-07-26";
	}
	
	
	// TODO: default time	
	// choose begin date
	echo '<input type="date" id="begindate" name="beginddate" value="' .  .  '"/>'
	// choose end date
	echo '<input type="date" id="enddate" name="enddate" value="' .  .'"/>'
	
	
	switch ($_POST['timetype']) {
    case "hour":
        
		
		
		
		
        break;
    case "day":
        echo "i равно 1";
        break;
    case "month":
        echo "i равно 2";
        break;
	}
	
	
	echo '</form>';
	?>
  </body>
</html>
