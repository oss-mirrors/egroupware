#!/bin/sh
#
# cronfile to start the phpgroupware data backup
#

#
# paranoia settings
#
umask 022

PATH=/sbin:/bin:/usr/sbin:/usr/bin
export PATH

php {server_root}/backup/inc/phpgw_data_backup.php

exit 0
