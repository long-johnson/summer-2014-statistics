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
		
		if (!isset($_POST['site']))
			$_POST['site'] = $sites[0];
		
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
		echo '</select><br>';
	}
	
	// выбор базовой временной единицы
	if (!isset($_POST['timetype']))
		$_POST['timetype'] = "hour";
	echo '<select name="timetype" id="timetype" onchange="this.form.submit();">';
	echo '<option disabled>Select a basic time unit...</option>';
	echo '<option '; if ($_POST['timetype'] == "hour") echo 'selected '; echo 'value="hour">Hour</option>';
	echo '<option '; if ($_POST['timetype'] == "day") echo 'selected '; echo 'value="day">Day</option>';
	echo '<option '; if ($_POST['timetype'] == "month") echo 'selected '; echo 'value="month">Month</option>';
	echo '</select><br>';
	
	// make path to the file
	$websitepath = "/usr/local/psa/var/modules/extended-plesk-statistics/" . trim($_POST['subscription']) . '/' . trim($_POST['site']) . '/';
	// read files hits, pages, uniq, visits, bandwidth into array strings
	$filehits = file($websitepath . 'hits.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$filepages = file($websitepath . 'pages.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$fileuniq = file($websitepath . 'unique_visitors.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$filevisits = file($websitepath . 'visits.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$fileband = file($websitepath . 'bandwidth.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	
	// в зависимости от промежутка времени (час,день,мес€ц) по-разному посчитать статистику
	switch ($_POST['timetype']) {
    case "hour":	// if we show stats hour by hour
        // TODO: default time
		if (!isset($_POST['begindatehour'])){
			$_POST['begindatehour'] = "2014-07-12";
		}
		if (!isset($_POST['enddatehour'])){
			$_POST['enddatehour'] = "2014-07-15";
		}	
		// choose begin date
		echo '<input type="date" id="begindatehour" name="begindatehour" onchange="this.form.submit();" value="' . $_POST['begindatehour'] . '"/><br>';
		// choose end date
		echo '<input type="date" id="enddatehour" name="enddatehour"  onchange="this.form.submit();" value="' . $_POST['enddatehour'] . '"/><br>';
		
		$interval = new DateInterval('P1D'); 
		$d1 = new Datetime($_POST['begindatehour']); 
		$d2 = new Datetime($_POST['enddatehour']); $d2->add($interval);
		
		// сначала заполним массив статистики нул€ми
		foreach(new DatePeriod($d1, $interval, $d2) as $d) {
			$curDate = $d->format('d/M/Y');
			for ($i=0;$i<24;++$i) {
				$stat['hits'][$curDate][$i] = 0; $stat['pages'][$curDate][$i] = 0;
				$stat['uniq'][$curDate][$i] = 0; $stat['visits'][$curDate][$i] = 0; $stat['band'][$curDate][$i] = 0;
			}
		}
		
		foreach(new DatePeriod($d1, $interval, $d2) as $d) { 
			$curDate = $d->format('d/M/Y');
			//echo($curDate . '<br>');
			// найдем в файле данную строку				// TODO: ќѕ“»ћјЋ№Ќ≈≈!!!
			foreach ($filehits as $record){
				//echo($record . '<br>');
				if (strpos($record,$curDate) !== false){
					preg_match_all('/([\d]+)/', $record, $hourlyhits);	// that extracts all ints from string to int array
					//print_r($hourlyhits);
					for ($i=0;$i<24;++$i) {
						$stat['hits'][$curDate][$i] += (int)$hourlyhits[0][$i+2];
					}
					
				}
			}
		} 
		
		/*foreach ($stat['hits'] as $date => $hour){
			for ($i=0;$i<24;++$i) {
				echo $hour[$i]; echo ' ';
			}
			echo '<br>';
		}*/
		
?>
		<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart);
		function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Day', 'Hits'],
<?php
		foreach ($stat['hits'] as $date => $hour){
			echo '[\'' . $date . $hour;
			for ($i=1;$i<24;++$i) {
				echo  '' $hour[$i];
			}
          ['2004',  1000,      400],
          ['2005',  1170,      460],
          ['2006',  660,       1120],
          ['2007',  1030,      540]
?>
        ]);

        var options = {
          title: 'Company Performance',
          hAxis: {title: 'Year', titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
		</script>
<?php
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

