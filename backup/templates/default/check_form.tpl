#!/bin/sh
#
# checks the dir {server_root}/backup for cronfile to copy it into the
# cron_dir 
# copy this file into your /etc/cron.hourly dir, chown to root and chmod u+x
#

#
# paranoia settings
#
umask 022

PATH=/sbin:/bin:/usr/sbin:/usr/bin
export PATH

# check for daily backup-file
if	test -e {server_root}/backup/phpgw_data_backup.daily ; then
    mv {server_root}/backup/phpgw_data_backup.daily /etc/cron.daily/phpgw_data_backup.daily ;
	chown root.root /etc/cron.daily/phpgw_data_backup.daily ;
	chmod go-r /etc/cron.daily/phpgw_data_backup.daily ;
	chmod u+x /etc/cron.daily/phpgw_data_backup.daily ;
else echo -e -n "\nno script for daily backup of the phpgroupware data\n" ;
fi

# check for weekly backup-file
if test -e {server_root}/backup/backup.weekly ; then
    mv {server_root}/backup/phpgw_data_backup.weekly /etc/cron.weekly/phpgw_data_backup.weekly ;
	chown root.root /etc/cron.weekly/phpgw_data_backup.weekly ;
	chmod go-r /etc/cron.weekly/phpgw_data_backup.weekly ;
	chmod u+x /etc/cron.weekly/phpgw_data_backup.weekly ;
else echo -e -n "no script for weekly backup of the phpgroupware data\n";
fi

# check for monthly backup-file
if test -e {server_root}/backup/backup.monthly ; then
    mv {server_root}/backup/phpgw_data_backup.monthly /etc/cron.monthly/phpgw_data_backup.monthly ;
	chown root.root /etc/cron.monthly/phpgw_data_backup.monthly ;
	chmod go-r /etc/cron.monthly/phpgw_data_backup.monthly ;
	chmod u+x /etc/cron.monthly/phpgw_data_backup.monthly ;
else echo -n "no script for monthly backup of the phpgroupware data\n";
fi

exit 0
