<?php

pm_Context::init('extended-plesk-statistics');

//exec('cd /usr/local/psa/var/modules/extended-plesk-statistics/');
chdir('/usr/local/psa/var/modules/extended-plesk-statistics/');
exec('/usr/local/psa/var/modules/extended-plesk-statistics/stat.exe 1>/dev/null 2>/dev/null 3>/dev/null &');