#!/bin/bash
step=20 #间隔的秒数
for (( i = 0; i < 60; i=(i+step) )); do
    /usr/bin/php /www/wwwroot/xhy_admin/artisan sync:SyncGameJoin
    sleep $step
done
exit 0
