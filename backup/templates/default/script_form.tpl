#!/usr/bin/php -q
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

	function get_archives($dir)
	{
		if (is_dir($dir))
		{
			$basedir = opendir($dir);

			$i = 0;
			while ($files = readdir($basedir))
			{
				if (($files != '.') && ($files != '..'))
				{
					$archive[$i]['file'] = $files;
					$archive[$i]['bdate'] = filemtime($dir . '/' . $files);
					$i++;
				}
			}
			closedir($basedir);
			return $archive;
		}
		else
		{
			return False;
		}
	}

	function get_date($type, $versions = '', $bintval = '')
	{
		$bdate		= time();
		$month		= date('m',$bdate);
		$day		= date('d',$bdate);
		$year		= date('Y',$bdate);

		if ($type == 'bdate')
		{
			$bdateout	=  $month . '_' . $day . '_' . $year;
			return $bdateout;
		}

		if ($type == 'rdate')
		{
			switch($bintval)
			{
				case 'daily':	$dd = '-' . $versions; break;
				case 'weekly':	$dd = '-(7*' . $versions . ')'; break;
				case 'monthly':	$dm = '-' . $versions; break;
			}
			$rdate = mktime(0,0,0,date('m',$bdate) . $dm,date('d',$bdate) . $dd,date('Y',$bdate));
			return $rdate;
		}
	}

	function check_datedue($dir)
	{
		$versions = {versions};

		if (is_integer($versions) && $versions != 0)
		{
			$archive = get_archives($dir);

			if (count($archive) >= $versions)
			{
				$bintval	= '{bintval}';

/*				$versions	= $versions-1;
				switch($bintval)
				{
					case 'daily':	$datedue = $versions; break;
					case 'weekly':	$datedue = $versions*4; break;
					case 'monthly':	$datedue = $versions*16; break;
				}
			//	exec("find " . $dir . '-mtime +' . $datedue . ' -exec rm -- {} ; 2>&1 > /dev/null');

				exec("find " . $dir . ' -mtime +' . $datedue,$rarchives);

				if ($rarchives)
				{
					chdir($dir);

					for ($i=0;$i<=count($rarchives);$i++)
					{
						system("rm " . substr($rarchives[$i],strlen($dir)+1) . ' 2>&1 > /dev/null');
					}
				}																							*/

				$rdate = get_date('rdate',$versions,$bintval);

				for ($i=0;$i<=count($archive);$i++)
				{
					if ($archive[$i]['bdate'] <= $rdate)
					{
						unlink($dir . '/' . $archive[$i]['file']);
						echo 'removed ' . $dir . '/' . $archive[$i]['file'] . "\n";
					}
				}
			}
		}
	}

	$basedir	= '{basedir}';

	check_datedue($basedir);

	$bsql		= '{bsql}';
	$bldap		= '{bldap}';
	$bemail		= '{bemail}';

	$bzip2		= '/usr/bin/bzip2';

	$bcomp		= '{bcomp}';

	switch ($bcomp)
	{
		case 'tgz':		$end = 'tar.gz'; break;
		case 'tar.bz2':	$end = 'tar'; break;
		case 'zip':		$end = 'zip'; break;
	}

	switch ($bcomp)
	{
		case 'tgz':		$command = '/bin/tar -czf '; break;
		case 'tar.bz2':	$command = '/bin/tar -cf '; break;
		case 'zip':		$command = '/usr/bin/zip -rq9 '; break;
	}

	if ($bsql)
	{
		switch($bsql)
		{
			case 'mysql':	$database = '/var/lib/mysql'; break;
			case 'pgsql':	$database = '/var/lib/pgsql/data/base'; break;
		}

		chdir($database);
		$out	= $basedir . '/' . get_date('bdate') . '_phpGWBackup_{bsql}.' . $end;
		$in		= ' {db_name}';

		system("$command" . $out . $in);

		if ($bcomp == 'tar.bz2')
		{
			$end = '.bz2';
			system("$bzip2 -z " . $out . ' 2>&1 > /dev/null'); 
			$out = $out . $end;
		}
		$output[]	= $out;
		$input[]	= substr($out,strlen($basedir)+1);
	}

	if ($bldap == 'yes')
	{
		chdir('/var/lib');
		$out	= $basedir . '/' . get_date('bdate') . '_phpGWBackup_ldap.' . $end;
		$in		= ' ldap';

		system("$command" . $out . $in);

		if ($bcomp == 'tar.bz2')
		{
			$end = '.bz2';
			system("$bzip2 -z " . $out . ' 2>&1 > /dev/null'); 
			$out = $out . $end;
		}
		$output[]	= $out;
		$input[]	= substr($out,strlen($basedir)+1);
	}

	if ($bemail == 'yes')
	{
<!-- BEGIN script_ba -->
		if (is_dir('/home/{lid}') == True)
		{
			chdir('/home/{lid}');
			$out	= $basedir . '/' . get_date('bdate') . '_phpGWBackup_email_{lid}.' . $end;
			$in		= ' Maildir';
			system("$command" . $out . $in . ' 2>&1 > /dev/null');

			if ($bcomp == 'tar.bz2')
			{
				$end = '.bz2';
				system("$bzip2 -z " . $out);
				$out = $out . $end;
			}
			$output[]	= $out;
			$input[]	= substr($out,strlen($basedir)+1);
		}
<!-- END script_ba -->
	}

// ----------------------- move to remote host --------------------------------

	$lsave		= '{lsave}';
	$lpath		= '{lpath}';
	$lwebsave	= '{lwebsave}';

	$rsave		= '{rsave}';
	$rapp		= '{rapp}';
	$rip		= '{rip}';
	$rpath		= '{rpath}';
	$ruser		= '{ruser}';
	$rpwd		= '{rpwd}';

	if ($rsave == 'yes')
	{
		if ($rapp == 'ftp')
		{
			$con = ftp_connect("$rip");

			$login_result = ftp_login($con, "$ruser", "$rpwd");

			if (!$con || !$login_result)
			{
				echo 'Connection to remote ftp-server failed !' . "\n";
				exit;
			}

			$rem = ftp_chdir($con, "$rpath");

			for ($i=0;$i<count($output);$i++)
			{
				$put = ftp_put($con, "$input[$i]", "$output[$i]", FTP_BINARY);

				if ($put)
				{
					echo 'ftp backuptransfer ' . $input[$i] . ': success !' . "\n";
				}
				else
				{
					echo 'ftp backuptransfer ' . $input[$i] . ': failed !' . "\n";
					exit;
				}
			}
			ftp_quit($con);
		}

// might not work yet!

		if ($rapp == 'scp')
		{
			for ($i=0;$i<count($output);$i++)
			{
				$pipe = popen("$rapp $output[$i] $ruser@$rip:$rpath/$input[$i]",'w');
				fputs($pipe, "$rpwd");

				if (!$pipe)
				{
					echo 'scp backuptransfer ' . $input[$i] . ': failed !' . "\n";
					exit;
				}
				else
				{
					echo 'scp backuptransfer ' . $input[$i] . ': success !' . "\n";
				}
				pclose($pipe);
			}
		}

// not tested yet! but maybe it works now ...

		if ($rapp == 'smbmount')
		{
			$smbdir = '/mnt';

			$rip = '//' . $rip;

			system("mount.smbfs $rip$rpath $smbdir -o username=$ruser,password=$rpwd,rw 2>&1 > /dev/null");

			check_datedue($smbdir);

			for ($i=0;$i<count($output);$i++)
			{
				system("cp " . $output[$i] . ' ' . $smbdir . '/ 2>&1 > /dev/null');
				echo 'transfer of ' . $output[$i] . ' through smbmount: success !' . "\n";
			}
			system("smbumount " . $smbdir);
		}
	}

	if ($lsave == 'yes')
	{
		if ($lwebsave == 'yes')
		{
			$command = 'cp';
		}
		else
		{
			$command = 'mv';
		}

		if ($lpath != '')
		{
			check_datedue($lpath);

			for ($i=0;$i<count($output);$i++)
			{
				system("$command " . $output[$i] . ' ' . $lpath . '/ 2>&1 > /dev/null'); 
			}
		}
	}
	else
	{
		$command = 'rm';
		for ($i=0;$i<count($output);$i++)
		{
			system("$command " . $output[$i] . ' 2>&1 > /dev/null');
		}
	}

?>
