<?php

pm_Context::init('extended-plesk-statistics');
$taskId = pm_Settings::get('periodic_task_id');
$task = pm_Scheduler::getInstance()->getTaskById($taskId);
pm_Scheduler::getInstance()->removeTask($task);