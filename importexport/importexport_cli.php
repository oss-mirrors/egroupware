#!/usr/bin/php -q
<?php
	/**
	 * eGroupWare - importexport
	 *
	 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	 * @package importexport
	 * @link http://www.egroupware.org
	 * @author Cornelius Weiss <nelius@cwtech.de>
	 * @version $Id:  $
	 */
	
	$path_to_egroupware = realpath(dirname(__FILE__).'/..');
	
	$usage = "usage:
			--definition <name of definition>
			--file <name of file>
			--user <eGW username>
			--password <password for user>
			--domain <domain name> \n";
	
	if (php_sapi_name() != 'cli')
	{
		die('This script only runs form command line');
	}
	
	// Include PEAR::Console_Getopt 
	require_once 'Console/Getopt.php'; 
	
	// Define exit codes for errors 
	define('HEADER_NOT_FOUND',9);
	define('NO_ARGS',10); 
	define('INVALID_OPTION',11); 
	
	// Reading the incoming arguments - same as $argv 
	$args = Console_Getopt::readPHPArgv(); 
	
	// Make sure we got them (for non CLI binaries) 
	if (PEAR::isError($args)) { 
	   fwrite(STDERR,"importexport_cli: ".$args->getMessage()."\n".$usage); 
	   exit(NO_ARGS); 
	} 
	
	// Short options 
	$short_opts = 'f:d:'; 
	
	// Long options 
	$long_opts = array( 
	   'definition=', 
	   'file=',
	   'user=',
	   'password=',
	   'domain='
	   ); 
	
	// Convert the arguments to options - check for the first argument 
	if ( realpath($_SERVER['argv'][0]) == __FILE__ ) { 
	   $options = Console_Getopt::getOpt($args,$short_opts,$long_opts); 
	} else { 
	   $options = Console_Getopt::getOpt2($args,$short_opts,$long_opts); 
	} 
	
	// Check the options are valid 
	if (PEAR::isError($options)) { 
	   fwrite(STDERR,"importexport_cli: ".$options->getMessage()."\n".$usage."\n"); 
	   exit(INVALID_OPTION); 
	}
	
	$domain = 'default';
	foreach ($options[0] as $option)
	{
		switch ($option[0])
		{
			case '--file' :
				$file = $option[1];
				break;
			case '--definition' :
				$definition = $option[1];
				break;
			case '--domain' :
				$domain = $option[1];
				break;
			case '--user' :
				$user = $option[1];
				break;
			case '--password' :
				$password = $option[1];
				break;
			default : 
				fwrite (STDERR,$usage."\n");
				exit(INVALID_OPTION); 
		}
	}
	// check file
	if (!$user || !$password)
	{
		fwrite(STDERR,'importexport_cli: You have to supply a username / password'."\n".$usage); 
		exit(INVALID_OPTION); 
	}
	
	$GLOBALS['egw_info']['flags'] = array(
		'disable_Template_class' => True,
		'noheader'  => True,
		'nonavbar' => True,
		'currentapp' => 'importexport',
		'autocreate_session_callback' => 'import_export_access',
		'login' => $user,
		'passwd' => $password,
		'noapi'      => True,
	);
	if (!is_readable($path_to_egroupware.'/header.inc.php'))
	{
		fwrite(STDERR,"importexport.php: Could not find '$path_to_egroupware/header.inc.php', exiting !!!\n");
		exit(HEADER_NOT_FOUND);
	}
	include($path_to_egroupware.'/header.inc.php');
	unset($GLOBALS['egw_info']['flags']['noapi']);

	// check domain
	$db_type = $GLOBALS['egw_domain'][$domain]['db_type'];
	if (!isset($GLOBALS['egw_domain'][$domain]) || empty($db_type))
	{
		fwrite(STDERR,"importexport_cli: ". $domain. ' is not a valid domain name'."\n"); 
	   	exit(INVALID_OPTION);
	}
	$GLOBALS['egw_info']['server']['sessions_type'] = 'db';	// no php4-sessions availible for cgi
	
	include(PHPGW_API_INC.'/functions.inc.php');
	
	// check file
	if (!is_readable($file))
	{
		fwrite(STDERR,"importexport_cli: ". $file. ' is not readable'."\n"); 
		exit(INVALID_OPTION); 
	}

	require_once('./inc/class.definition.inc.php');
	try {
		$definition = new definition($definition);
	}
	catch (Exception $e) {
		fwrite(STDERR,"importexport_cli: ". $e->getMessage(). "\n"); 
		exit(INVALID_OPTION);
	}

	require_once("$path_to_egroupware/$definition->application/inc/class.$definition->plugin.inc.php");
	$po = new $definition->plugin;
	$type = $definition->type;
	$po->$type($definition,array('file' => $file));
	
	$GLOBALS['egw']->common->phpgw_exit();
	
	function import_export_access(&$account)
	{
		$account['login'] = $GLOBALS['egw_info']['flags']['login'];
		$account['passwd'] = $GLOBALS['egw_info']['flags']['passwd'];
		$account['passwd_type'] = 'text';
		return true;
	}
