<?php
	/*******************************************************************\
	* phpGroupWare - Backup                                             *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Administration Tool for data backup                               *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2001 Bettina Gille                                  *
	*                                                                   *
	* This program is free software; you can redistribute it and/or     *
	* modify it under the terms of the GNU General Public License as    *
	* published by the Free Software Foundation; either version 2 of    *
	* the License, or (at your option) any later version.               *
	*                                                                   *
	* This program is distributed in the hope that it will be useful,   *
	* but WITHOUT ANY WARRANTY; without even the implied warranty of    *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
	* General Public License for more details.                          *
	*                                                                   *
	* You should have received a copy of the GNU General Public License *
	* along with this program; if not, write to the Free Software       *
	* Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
	\*******************************************************************/
	/* $Id$ */

	$bdate = time();
	$month  = date('n',$bdate);
	$day    = date('d',$bdate);
	$year   = date('Y',$bdate);
	$bdateout =  $day . '_' . $month . '_' . $year;

	$bmysql = '{bmysql}';
//	$bpsql = '{bpsql}';
	$bldap = '{bldap}';
	$bemail = '{bemail}';

	$tar = '/bin/tar';


	if ($bmysql == 'yes')
	{
		chdir('/var/lib/mysql');
		$out = '{server_root}/backup/' . $bdateout . '_backup_{db_type}.tar.gz';
		$in = ' {db_name}';

		system("$tar -czf " . $out . $in);
	}

	if ($bemail == 'yes')
	{
<!-- BEGIN script_ba -->
		if (is_dir('/home/{lid}') == True)
		{
			chdir('/home/{lid}');
			$out = '{server_root}/backup/' . $bdateout . '_backup_email_{lid}.tar.gz';
			$in = ' Mail';
			system("$tar -czf " . $out . $in);
		}
<!-- END script_ba -->
	}
?>
