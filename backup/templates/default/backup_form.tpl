#!/bin/sh
#
# cronfile to backup the phpgroupware data
#

#
# paranoia settings
#
umask 022

PATH=/sbin:/bin:/usr/sbin:/usr/bin
export PATH

{bsqlin}
{sql_comp}

echo -e -n"\nphpgroupware sql-data backup done\n"  

exit 0
