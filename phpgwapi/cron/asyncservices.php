#!/usr/bin/php -q
<?php
	/**************************************************************************\
	* eGroupWare API - Timed Asynchron Services for eGroupWare                 *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* Class for creating cron-job like timed calls of eGroupWare methods       *
	* -------------------------------------------------------------------------*
	* This library is part of the eGroupWare API                               *
	* http://www.egroupware.org/                                               *
	* ------------------------------------------------------------------------ *
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/* $Id$ */

	$path_to_phpgroupware = '../..';	//  need to be adapted if this script is moved somewhere else
	$_GET['domain'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'default';

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'login',
		'noapi'      => True		// this stops header.inc.php to include phpgwapi/inc/function.inc.php
	);
	if (!is_readable($path_to_phpgroupware.'/header.inc.php'))
	{
		echo "asyncservice.php: Could not find '$path_to_phpgroupware/header.inc.php', exiting !!!\n";
		exit(1);
	}
	include($path_to_phpgroupware.'/header.inc.php');
	unset($GLOBALS['phpgw_info']['flags']['noapi']);

	$db_type = $GLOBALS['phpgw_domain'][$_GET['domain']]['db_type'];
	if (!isset($GLOBALS['phpgw_domain'][$_GET['domain']]) || empty($db_type))
	{
		echo "asyncservice.php: Domain '$_GET[domain]' is not configured or renamed, exiting !!!\n";
		exit(1);
	}
	if (!extension_loaded($db_type) && !dl($db_type.'.so'))
	{
		echo "asyncservice.php: Extension '$db_type' is not loaded and can't be loaded via dl('$db_type.so') !!!\n";
	}
	
	$GLOBALS['phpgw_info']['server']['sessions_type'] = 'db';	// no php4-sessions availible for cgi

	include(PHPGW_API_INC.'/functions.inc.php');
	
	$num = ExecMethod('phpgwapi.asyncservice.check_run','crontab');
	// if the following comment got removed, you will get an email from cron for every check performed
	//echo date('Y/m/d H:i:s ').$_GET['domain'].': '.($num ? "$num job(s) executed" : 'Nothing to execute')."\n";

	$GLOBALS['phpgw']->common->phpgw_exit();
