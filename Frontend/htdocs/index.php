<?php pm_Context::init('extended-plesk-statistics'); ?>
<html>
  <head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
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
	
	// выбор показываемого по-умолчанию параметра
	if (!isset($_POST['paramonplot']))
		$_POST['paramonplot'] = "hits";
	
	// make path to the file
	$websitepath = "/usr/local/psa/var/modules/extended-plesk-statistics/" . trim($_POST['subscription']) . '/' . trim($_POST['site']) . '/';
	// read files hits, pages, uniq, visits, bandwidth into array strings
	$filehits = file($websitepath . 'hits.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$filepages = file($websitepath . 'pages.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$fileuniq = file($websitepath . 'unique_visitors.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$filevisits = file($websitepath . 'visits.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$fileband = file($websitepath . 'bandwidth.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$fileipmap = file($websitepath . 'ip_mapping.stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	
	// в зависимости от промежутка времени (час,день,мес€ц) по-разному посчитать статистику
	switch ($_POST['timetype']) {
    case "hour":	// if we show stats hour by hour
        // TODO: default time
		if (!isset($_POST['begindatehour'])){
			$_POST['begindatehour'] = "2014-07-13";
		}
		if (!isset($_POST['enddatehour'])){
			$_POST['enddatehour'] = "2014-07-13";
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
			// найдем в файле данную строку				// TODO: ќѕ“»ћјЋ№Ќ≈≈!!!
			// HITS
			foreach ($filehits as $record)
				if (strpos($record,$curDate) !== false){
					preg_match_all('/([\d]+)/', $record, $hourlystat);	// that extracts all ints from string to int array
					for ($i=0;$i<24;++$i)
						$stat['hits'][$curDate][$i] += (int)$hourlystat[0][$i+2];
				}
			// PAGES
			foreach ($filepages as $record)
				if (strpos($record,$curDate) !== false){
					preg_match_all('/([\d]+)/', $record, $hourlystat);	// that extracts all ints from string to int array
					for ($i=0;$i<24;++$i)
						$stat['pages'][$curDate][$i] += (int)$hourlystat[0][$i+2];
				}
			// UNIQ
			foreach ($fileuniq as $record)
				if (strpos($record,$curDate) !== false){
					preg_match_all('/([\d]+)/', $record, $hourlystat);	// that extracts all ints from string to int array
					for ($i=0;$i<24;++$i)
						$stat['uniq'][$curDate][$i] += (int)$hourlystat[0][$i+2];
				}
			// VISITS
			foreach ($filevisits as $record)
				if (strpos($record,$curDate) !== false){
					preg_match_all('/([\d]+)/', $record, $hourlystat);	// that extracts all ints from string to int array
					for ($i=0;$i<24;++$i)
						$stat['visits'][$curDate][$i] += (int)$hourlystat[0][$i+2];
				}
			// BAND
			foreach ($fileband as $record)
				if (strpos($record,$curDate) !== false){
					preg_match_all('/([\d]+)/', $record, $hourlystat);	// that extracts all ints from string to int array
					for ($i=0;$i<24;++$i)
						$stat['band'][$curDate][$i] += (int)$hourlystat[0][$i+2]/1024;
				}
				
			// IP <=> hits/bandwidth/pages/visits
			for ($i=0, $size=count($fileipmap); $i<$size; ++$i){
				$str = $fileipmap[$i];
				if (strpos($str,$curDate) !== false){
					++$i; $str = $fileipmap[$i];
					while($str!="_END_"){
						$parsed = explode(" ", $str);
						++$i; $str = $fileipmap[$i];
						if (array_key_exists($parsed[0],$stat['ip'])){
							$stat['ip'][$parsed[0]]['hits'] += (int) $parsed[1];
							$stat['ip'][$parsed[0]]['band'] += (int) $parsed[2]/1024;	//kb
							$stat['ip'][$parsed[0]]['pages'] += (int) $parsed[3];
							$stat['ip'][$parsed[0]]['visits'] += (int) $parsed[4];
						}else{
							$stat['ip'][$parsed[0]]['hits'] = (int) $parsed[1];
							$stat['ip'][$parsed[0]]['band'] = (int) $parsed[2]/1024;	//kb
							$stat['ip'][$parsed[0]]['pages'] = (int) $parsed[3];
							$stat['ip'][$parsed[0]]['visits'] = (int) $parsed[4];
						}
					}
				}
			}
		}
		
		// скрипт на отрисовку графика
		switch ($_POST['paramonplot']){
		case 'hits': $legend= 'Hits'; break; case 'pages': $legend= 'Visited pages'; break; 
		case 'band': $legend= 'Bandwidth (kb)'; break; case 'visits': $legend= 'Number of visits'; break;  
		case 'uniq': $legend= 'Number of unique visitors'; break; 
		}
		echo '<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart);
		function drawChart() {
        var data = google.visualization.arrayToDataTable([
          [\'Day\', \' ' . $legend . '\'],';
		foreach ($stat[$_POST['paramonplot']] as $date => $hour){
			//echo '[\'' . $date . ':' . 0 . 'h\', ' . $hour[0] . '],';
			for ($i=0;$i<24;++$i) {
				echo '[\'' . $date . ':' . $i . 'h\', ' . floor($hour[$i]) . '],';
			}
		}
        echo ']);
        var options = {
          title: \'Hourly statistics\',
          hAxis: {showTextEvery:24}
        };
        var chart = new google.visualization.ColumnChart(document.getElementById(\'chart_div\'));
        chart.draw(data, options);
		}
		</script>';
		
		// скрипт на отрисовку сводной таблицы
		echo "<script type='text/javascript'>
		google.load('visualization', '1', {packages:['table']});
		google.setOnLoadCallback(drawVisualization);
		function drawVisualization() {
			// Create and populate the data table.
			var data = google.visualization.arrayToDataTable([
			['Hour', 'Hits', 'Pages', 'Unique visitors', 'Visits', 'Bandwidth (kb)'],";
		foreach ($stat['hits'] as $date => $hour){
			//echo '[\'' . $date . ':' . 0 . 'h\', ' . $hour[0] . ',' . $stat['pages'][$date][0] . ',' . $stat['uniq'][$date][0] .
				// ',' . $stat['visits'][$date][0] . ',' . $stat['band'][$date][0] . '],';
			for ($i=0;$i<24;++$i) {
				echo '[\'' . $date . ':' . $i . 'h\', ' . $hour[$i] . ',' . $stat['pages'][$date][$i] . ',' . $stat['uniq'][$date][$i] .
				 ',' . $stat['visits'][$date][$i] . ',' . floor($stat['band'][$date][$i]) . '],';
			}
		}
		echo "]);
			visualization = new google.visualization.Table(document.getElementById('table_div'));
			var options = {
				page: 'enable', pageSize: 24
			};
			visualization.draw(data, options);
		} </script>";
		
		// скрипт на отрисовку таблицы с IP
		echo "<script type='text/javascript'>
		google.load('visualization', '1', {packages:['table']});
		google.setOnLoadCallback(drawIpTable);
		function drawIpTable() {
			// Create and populate the data table.
			var data = google.visualization.arrayToDataTable([
			['IP', 'Hits', 'Pages', 'Visits', 'Bandwidth (kb)'],";
		foreach ($stat['ip'] as $ip => $params){
				echo '[\'' . $ip . '\', ' . $params['hits'] . ',' . $params['pages'] . ',' . $params['visits'] .
				 ',' . floor($params['band']) . '],';
		}
		echo "]);
			visualization = new google.visualization.Table(document.getElementById('table_ip'));
			var options = {
				page: 'enable', pageSize: 25
			};
			visualization.draw(data, options);
		} </script>";
		
        break;
    case "day":
        echo "i равно 1";
        break;
    case "month":
        echo "i равно 2";
        break;
	}
	
	
	// выбор показываемого параметра
	echo '<select name="paramonplot" id="paramonplot" onchange="this.form.submit();">';
	echo '<option disabled>Select parameter to show on plot...</option>';
	echo '<option '; if ($_POST['paramonplot'] == "hits") echo 'selected '; echo 'value="hits">Hits</option>';
	echo '<option '; if ($_POST['paramonplot'] == "pages") echo 'selected '; echo 'value="pages">Pages</option>';
	echo '<option '; if ($_POST['paramonplot'] == "uniq") echo 'selected '; echo 'value="uniq">Unique visitors</option>';
	echo '<option '; if ($_POST['paramonplot'] == "visits") echo 'selected '; echo 'value="visits">Visits</option>';
	echo '<option '; if ($_POST['paramonplot'] == "band") echo 'selected '; echo 'value="band">Bandwidth</option>';
	echo '</select><br>';
	echo '</form>';
	
	// собственно элемент графика
	echo "<b>Statistics plot</b> <br>";
	echo "<div id='chart_div' style='height: 400px;'></div><br>";
	// собственно элемент таблицы
	echo "<b>Statistics summary table</b> <br>";
	echo "<div id='table_div'></div><br><br>";
	// собственно элемент таблицы отношений ip <=> параметр
	echo "<b>Statistics by IPs</b> <br>";
	echo "<div id='table_ip'></div><br>";
?>
  </body>
</html>

