<?php

pm_Context::init('extended-plesk-statistics');

$task = new pm_Scheduler_Task();
$task->setSchedule(pm_Scheduler::$EVERY_DAY);
$task->setCmd('periodic-task.php');
pm_Scheduler::getInstance()->putTask($task);
pm_Settings::set('periodic_task_id', $task->getId());

//pm_Settings::set('useAuth', true);
//pm_Settings::set('authToken', md5(uniqid(rand(), true)));

//exec('cd /usr/local/psa/var/modules/extended-plesk-statistics/');
//exec('touch subscriptions.txt');

// TODO: извлечь названия подписок

/*$subscriptions = array(
	"jimmy.test.plesk.ru",
	"metallica.test.plesk.ru",
	"site1.test.plesk.ru",
);

$filename = "/usr/local/psa/var/modules/extended-plesk-statistics/subscriptions.txt";
$file = fopen($file,"a");
for($i=0, $size=count($subscriptions); $i < $size; ++$i){
	fwrite($file, $subscriptions[$i] . PHP_EOL);
	// создадим папку для вебсайтов подписки
	
}
fclose($file);
*/
