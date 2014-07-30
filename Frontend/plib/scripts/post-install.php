<?php

pm_Context::init('extended-plesk-statistics');

$task = new pm_Scheduler_Task();
//$task->setSchedule(pm_Scheduler::$EVERY_DAY);
$task->setSchedule(pm_Scheduler::$EVERY_DAY);
$task->setCmd('periodic-task.php');
pm_Scheduler::getInstance()->putTask($task);
pm_Settings::set('periodic_task_id', $task->getId());


// добавим разрешение на выполнение
exec('chmod 744 /usr/local/psa/var/modules/extended-plesk-statistics/stat.exe');


// то, что будет делатьс€ периодически
exec('cd /usr/local/psa/var/modules/extended-plesk-statistics/; /usr/local/psa/var/modules/extended-plesk-statistics/stat.exe 1>/dev/null 2>/dev/null 3>/dev/null &');

//pm_Settings::set('useAuth', true);
//pm_Settings::set('authToken', md5(uniqid(rand(), true)));

//exec('cd /usr/local/psa/var/modules/extended-plesk-statistics/');
//exec('touch subscriptions.txt');

// TODO: извлечь названи€ подписок

/*$subscriptions = array(
	"jimmy.test.plesk.ru",
	"metallica.test.plesk.ru",
	"site1.test.plesk.ru",
);

$filename = "/usr/local/psa/var/modules/extended-plesk-statistics/subscriptions.txt";
$file = fopen($file,"a");
for($i=0, $size=count($subscriptions); $i < $size; ++$i){
	fwrite($file, $subscriptions[$i] . PHP_EOL);
	// создадим папку дл€ вебсайтов подписки
	
}
fclose($file);
*/
