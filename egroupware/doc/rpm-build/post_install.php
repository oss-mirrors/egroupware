#!/usr/bin/php
<?php
/**
 * eGroupWare - RPM post install: automatic install or update EGroupware
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @author RalfBecker@outdoor-training.de
 * @version $Id$
 */

if (isset($_SERVER['HTTP_HOST']))	// security precaution: forbit calling setup-cli as web-page
{
	die('<h1>rpm_post_install.php must NOT be called as web-page --> exiting !!!</h1>');
}
$verbose = false;
$config = array(
	'php'         => '/usr/bin/php',
	'source_dir'  => '/usr/share/egroupware',
	'data_dir'    => '/var/lib/egroupware',
	'header'      => '$data_dir/header.inc.php',	// symlinked to source_dir by rpm
	'setup-cli'   => '$source_dir/setup/setup-cli.php',
	'domain'      => 'default',
	'config_user' => 'admin',
	'config_passwd'   => randomstring(),
	'db_type'     => 'mysql',
	'db_host'     => 'localhost',
	'db_port'     => 3306,
	'db_name'     => 'egroupware',
	'db_user'     => 'egroupware',
	'db_pass'     => randomstring(),
	'db_grant_host' => 'localhost',
	'db_root'     => 'root',	// mysql root user/pw to create database
	'db_root_pw'  => '',
	'backup'      => '',
	'admin_user'  => 'sysop',
	'admin_passwd'=> randomstring(),
	'lang'        => 'en',	// languages for admin user and extra lang to install
	'charset'     => 'utf-8',
	'start_db'    => '/etc/init.d/mysqld',
	'autostart_db' => '/sbin/chkconfig --level 345 mysqld on',
	'start_webserver' => '/etc/init.d/httpd',
	'autostart_webserver' => '/sbin/chkconfig --level 345 httpd on',
	'distro'      => 'rh',
);

// read language from LANG enviroment variable
if (($lang = isset($_ENV['LANG']) ? $_ENV['LANG'] : $_SERVER['LANG']))
{
	@list($lang,$nat) = preg_split('/[_.]/',$lang);
	if (in_array($lang.'-'.strtolower($nat),array('es-es','pt-br','zh-tw')))
	{
		$lang .= '-'.strtolower($nat);
	}
	$config['lang'] = $lang;
}
$config['source_dir'] = dirname(dirname(dirname(__FILE__)));

/**
 * Set distribution spezific defaults
 *
 * @param string $distro=null default autodetect
 */
function set_distro_defaults($distro=null)
{
	global $config;
	if (is_null($distro))
	{
		$distro = file_exists('/etc/SuSE-release') ? 'suse' : (file_exists('/etc/debian_version') ? 'debian' : 'rh');
	}
	switch (($config['distro'] = $distro))
	{
		case 'suse':
			$config['php'] = '/usr/bin/php5';
			$config['start_db'] = '/etc/init.d/mysql';
			$config['autostart_db'] = '/sbin/chkconfig --level 345 mysql on';
			$config['start_webserver'] = '/etc/init.d/apache2';
			$config['autostart_webserver'] = '/sbin/chkconfig --level 345 apache2 on';
			break;
		case 'debian':
			$config['start_db'] = '/etc/init.d/mysql';
			$config['autostart_db'] = '/usr/sbin/update-rc.d mysql defaults';
			$config['start_webserver'] = '/etc/init.d/apache2';
			$config['autostart_webserver'] = '/usr/sbin/update-rc.d apache2 defaults';
			break;
		default:
			$config['distro'] = 'rh';
			// fall through
		case 'rh':	// nothing to do, defaults are already set
			break;
	}
}
set_distro_defaults();

// read config from command line
$argv = $_SERVER['argv'];
$prog = array_shift($argv);

while(($arg = array_shift($argv)))
{
	if ($arg == '-v' || $arg == '--verbose')
	{
		$verbose = true;
	}
	elseif($arg == '-h' || $arg == '--help')
	{
		usage();
	}
	elseif($arg == '--suse')
	{
		set_distro_defaults('suse');
	}
	elseif($arg == '--distro')
	{
		set_distro_defaults(array_shift($argv));
	}
	elseif(substr($arg,0,2) == '--' && isset($config[$name=substr($arg,2)]))
	{
		$config[$name] = array_shift($argv);
	}
	else
	{
		usage("Unknown argument '$arg'!");
	}
}

$replace = array();
foreach($config as $name => $value)
{
	$replace['$'.$name] = $value;
	if (strpos($value,'$') !== false)
	{
		$config[$name] = strtr($value,$replace);
	}
}
// basic config checks
foreach(array('php','source_dir','data_dir','setup-cli') as $name)
{
	if (!file_exists($config[$name])) bail_out(1,$config[$name].' not found!');
}
$setup_cli = $config['php'].' '.$config['setup-cli'];

if (!file_exists($config['header']) || filesize($config['header']) < 200)	// default header redirecting to setup is 147 bytes
{
	// --> new install

	// create header
	$setup_header = $setup_cli.' --create-header '.escapeshellarg($config['config_passwd'].','.$config['config_user']).
		' --domain '.escapeshellarg($config['domain'].','.$config['db_name'].','.$config['db_user'].','.$config['db_pass'].
			','.$config['db_type'].','.$config['db_host'].','.$config['db_port']);
	run_cmd($setup_header);

	// check for localhost if database server is started and start it (permanent) if not
	if ($config['db_host'] == 'localhost' && file_exists($config['start_db']))
	{
		if (exec($config['start_db'].' status',$dummy,$ret) && $ret)
		{
			system($config['start_db'].' start');
			system($config['autostart_db']);
		}
	}
	// create database
	$setup_db = $setup_cli.' --setup-cmd-database sub_command=create_db';
	foreach(array('domain','db_type','db_host','db_port','db_name','db_user','db_pass','db_root','db_root_pw','db_grant_host') as $name)
	{
		$setup_db .= ' '.escapeshellarg($name.'='.$config[$name]);
	}
	run_cmd($setup_db);

	// install egroupware
	$setup_install = $setup_cli.' --install '.escapeshellarg($config['domain'].','.$config['config_user'].','.$config['config_passwd'].','.$config['backup'].','.$config['charset'].','.$config['lang']);
	run_cmd($setup_install);

	if ($config['data_dir'] != '/var/lib/egroupware')
	{
		// set files dir different from default
		$setup_config = $setup_cli.' --config '.escapeshellarg($config['domain'].','.$config['config_user'].','.$config['config_passwd']).
			' --files-dir '.escapeshellarg($config['data_dir'].'/files').' --backup-dir '.escapeshellarg($config['data_dir'].'/backup');
		run_cmd($setup_config);
	}
	// create dummy mailserver config, as fmail otherwise gives fatal error otherwise
	$setup_mailserver = $setup_cli.' --config '.escapeshellarg($config['domain'].','.$config['config_user'].','.$config['config_passwd']).
		' --mailserver localhost,imap --smtpserver localhost,25';
	run_cmd($setup_config);

	// create first user
	$setup_admin = $setup_cli.' --admin '.escapeshellarg($config['domain'].','.$config['config_user'].','.$config['config_passwd'].','.
		$config['admin_user'].','.$config['admin_passwd'].',,,,'.$config['lang']);
	run_cmd($setup_admin);

	// check if webserver is started and start it (permanent) if not
	if (file_exists($config['start_webserver']))
	{
		if (exec($config['start_webserver'].' status',$dummy,$ret) && $ret)
		{
			system($config['start_webserver'].' start');
			system($config['autostart_webserver']);
		}
		else
		{
			system($config['start_webserver'].' reload');
		}
	}
	echo "\n";
	echo "EGroupware successful installed\n";
	echo "===============================\n";
	echo "\n";
	echo "Please note the following user names and passwords:\n";
	echo "\n";
	echo "Setup username:      $config[config_user]\n";
	echo "      password:      $config[config_passwd]\n";
	echo "\n";
	echo "EGroupware username: $config[admin_user]\n";
	echo "           password: $config[admin_passwd]\n";
	echo "\n";
	echo "You can log into EGroupware by pointing your browser to http://localhost/egroupware/\n";
	echo "Please replace localhost with the appropriate hostname, if you connect remote.\n\n";

	if (empty($config['db_root_pw']))
	{
		echo "*** Database has no root password set, please fix that immediatly: mysqladmin -u root password NEWPASSWORD\n\n";
	}
}
else
{
	// --> existing install --> update

	// get user from header and replace password, as we dont know it
	$old_password = patch_header($config['header'],$config['config_user'],$config['config_passwd']);
	// register a shutdown function to put old password back in any case
	register_shutdown_function('patch_header',$config['header'],$config['config_user'],$old_password);

	// update egroupware
	$setup_update = $setup_cli.' --update '.escapeshellarg('all,'.$config['config_user'].','.$config['config_passwd']);
	$ret = run_cmd($setup_update,$output,array(4,15));

	switch($ret)
	{
		case 4:		// header needs an update
			$header_update = $setup_cli.' --update-header '.escapeshellarg($config['config_passwd'].','.$config['config_user']);
			run_cmd($header_update);
			$ret = run_cmd($setup_update,$output,15);
			if ($ret != 15) break;
			// fall through
		case 15:	// missing configuration (eg. mailserver)
			if (!$verbose) echo implode("\n",(array)$output)."\n";
			break;

		case 0:
			echo "\nEGroupware successful updated\n";
			break;
	}
	exit($ret);
}

/**
 * Patches a given password (for header admin) into the EGroupware header.inc.php and returns the old one
 *
 * @param string $filename
 * @param string &$user username on return(!)
 * @param string $password new password
 * @return string old password
 */
function patch_header($filename,&$user,$password)
{
	$header = file_get_contents($filename);

	if (!preg_match('/'.preg_quote("\$GLOBALS['egw_info']['server']['header_admin_user'] = '")."([^']+)';/m",$header,$umatches) ||
		!preg_match('/'.preg_quote("\$GLOBALS['egw_info']['server']['header_admin_password'] = '")."([^']*)';/m",$header,$pmatches))
	{
		bail_out(99,"$filename is no regular EGroupware header.inc.php!");
	}
	file_put_contents($filename,preg_replace('/'.preg_quote("\$GLOBALS['egw_info']['server']['header_admin_password'] = '")."([^']*)';/m",
		"\$GLOBALS['egw_info']['server']['header_admin_password'] = '".$password."';",$header));

	$user = $umatches[1];

	return $pmatches[1];
}

/**
 * Runs given shell command, exists with error-code after echoing the output of the failed command (if not already running verbose)
 *
 * @param string $cmd
 * @param array &$output=null $output of command
 * @param int|array $no_bailout=null exit code(s) to NOT bail out
 * @return int exit code of $cmd
 */
function run_cmd($cmd,array &$output=null,$no_bailout=null)
{
	global $verbose;

	if ($verbose)
	{
		echo $cmd."\n";
		system($cmd,$ret);
	}
	else
	{
		$output[] = $cmd;
		exec($cmd,$output,$ret);
	}
	if ($ret && !in_array($ret,(array)$no_bailout))
	{
		bail_out($ret,$verbose?null:$output);
	}
	return $ret;
}

/**
 * Stop programm execution with a given exit code and optional extra message
 *
 * @param int $ret=1
 * @param array|string $output line(s) to output before temination notice
 */
function bail_out($ret=1,$output=null)
{
	if ($output) echo implode("\n",(array)$output);
	echo "\n\nInstallation failed --> exiting!\n\n";
	exit($ret);
}

/**
 * Return a rand string, eg. to generate passwords
 *
 * @param int $len=16
 * @return string
 */
function randomstring($len=16)
{
	static $usedchars = array(
		'0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f',
		'g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v',
		'w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L',
		'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		'@','!','$','%','&','/','(',')','=','?',';',':','#','_','-','<',
		'>','|','{','[',']','}',	// dont add \,'" as we have problems dealing with them
	);

	$str = '';
	for($i=0; $i < $len; $i++)
	{
		$str .= $usedchars[mt_rand(0,count($usedchars)-1)];
	}
	return $str;
}

/**
 * Give usage information and an optional error-message, before stoping program execution with exit-code 90 or 0
 *
 * @param string $error=null optional error-message
 */
function usage($error=null)
{
	global $prog,$config;

	echo "Usage: $prog [-h|--help] [-v|--verbose] [--distro=(suse|rh|debian)] [options, ...]\n\n";
	echo "options and their defaults:\n";
	foreach($config as $name => $default)
	{
		if (in_array($name,array('config_passwd','db_pass','admin_passwd'))) $default = '<16 char random string>';
		echo '--'.str_pad($name,20).$default."\n";
	}
	if ($error)
	{
		echo "$error\n\n";
		exit(90);
	}
	exit(0);
}
