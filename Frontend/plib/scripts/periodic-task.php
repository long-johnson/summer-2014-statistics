<?php

pm_Context::init('extended-plesk-statistics');

exec('/usr/local/psa/var/modules/extended-plesk-statistics/stat.exe 1>/dev/null 2>/dev/null 3>/dev/null &');