<?php
/**
 * eGroupWare Gallery2 integration
 * 
 * Move this file into the gallery2 directory (after you installed and configured G2),
 * in Order to get multidomain support for G2 together with eGW.
 * 
 * If you are NOT using the default files dir, you need to set your dir around line 55,
 * otherwise the eGW enviroment needs to be created for every picture !!!
 * 
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

if (!isset($GLOBALS['egw_info']))
{
	/**
	 * Grant anonymous access via anonymous/anonymous
	 * 
	 * ToDo: make that configurable as eg. in the wiki
	 *
	 * @param array $anon_account
	 * @return boolean
	 */
	function gallery_anon_access(&$anon_account)
	{
		// should be configurable ...
		$anon_account = array(
			'login'  => 'anonymous',
			'passwd' => 'anonymous',
			'passwd_type' => 'text',
		);
		return true;
	}
	$GLOBALS['egw_info'] = array(
		'flags' => array(
			'disable_Template_class' => true,
			'currentapp' => 'gallery',
			'noheader'   => true,
			'autocreate_session_callback' => 'gallery_anon_access',
		),
	);
	if (strtoupper(substr(PHP_OS,0,3)) == 'WIN')
	{
		$GLOBALS['egw_info']['server']['files_dir'] = 'c:\\Program files\\egroupware\\'.$_REQUEST['domain'].'\\files';
	}
	else
	{
		$GLOBALS['egw_info']['server']['files_dir'] = '/var/lib/egroupware/'.$_REQUEST['domain'].'/files';
	}
	// if you are NOT using the default dirs, you need to set your dir(s) here:
	//$GLOBALS['egw_info']['server']['files_dir'] = '/other/dir';

	// if we have a session, we try - for performance reasons, NOT to setup eGW complete 
	// as G2 can handle the picture downloads for itself a lot faster ;-)
	if ($_REQUEST['sessionid'] && $_REQUEST['domain'] && is_dir($GLOBALS['egw_info']['server']['files_dir']))
	{
		$GLOBALS['egw_info']['flags']['noapi'] = true;
	}
	include (dirname(__FILE__).'/../../header.inc.php');
	
	if ($GLOBALS['egw_info']['flags']['noapi'])
	{
		foreach(array('db_host','db_port','db_name','db_user','db_pass','db_type') as $name)
		{
			$GLOBALS['egw_info']['server'][$name] = $GLOBALS['egw_domain'][$_REQUEST['domain']][$name];
		}
		unset($GLOBALS['egw_domain']);	// as the API does for security reasons
	}
}

/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

/*
 * When display_errors is enabled, PHP errors are printed to the output.
 * For production web sites, you're strongly encouraged to turn this feature off,
 * and use error logging instead.
 * During development, you should set the value to 1 to ensure that you notice PHP
 * warnings and notices that are not covered in unit tests (e.g. template issues).
 */
@ini_set('display_errors', 0);

/*
 * Prevent direct access to config.php.
 */
if (!isset($gallery) || !method_exists($gallery, 'setConfig')) {
    exit;
}

/*
 * As a security precaution, we limit access to Gallery's test suite
 * to those people who know the password, below.  Example:
 *
 *  $gallery->setConfig('setup.password', 'A PASSWORD');
 *
 * Choose something random and enter it in plain text.  You don't have to
 * remember it because you can always refer to this file.  You'll only be asked
 * for this password when you run Gallery's lib/tools code.  We don't provide a
 * default password because we want you to choose one of your own (which
 * lessens the chance that you'll get hacked).
 */
$gallery->setConfig('setup.password', 'secret');

/*
 * In order for Gallery to manage your data, you must provide it with
 * a directory that it can write to.  Gallery is a webserver application,
 * so the directory that you create must be writeable by the
 * webserver, not just by you.
 *
 * Create an empty directory anywhere you please.  Gallery will fill this
 * directory with its own files (that you shouldn't mess with).  This directory
 * can be anywhere on your filesystem.  For security purposes, it's better
 * if the directory is not accessible via your webserver (ie, it should
 * not be in your DocumentRoot).  If you *do* make it available via your
 * web server then you probably won't have any security for your data files.
 *
 * Don't make this the same as your gallery directory!
 */
$gallery->setConfig('data.gallery.base',$GLOBALS['egw_info']['server']['files_dir'].'/gallery/');
//error_log("\$gallery->setConfig('data.gallery.base','".$GLOBALS['egw_info']['server']['files_dir'].'/gallery/'."');");

/*
 * Gallery can store its data in multiple different back ends.  Currently we
 * support MySQL, PostgreSQL and Oracle.  Enter the hostname where your
 * database lives, and the username and password you use to connect to it.
 *
 * You must specify the name of a database that already exists.  Gallery will
 * not create the database for you, because it's very difficult to do that in
 * a reliable, database-neutral fashion.  The user that you use should have
 * the following permissions:
 *
 *    SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER
 *
 * You must specify a table and column prefix.  This is a few characters that
 * is prepended to any table or column name to avoid conflicting with reserved
 * words in the database or other tables you have in the same database.  In
 * fact, it's fine to let Gallery uses the same database as other applications
 * (including other instances of Gallery itself); the prefix is enough
 * to distinguish Gallery's tables from other applications.
 *
 * We provide some reasonable default prefixes below.  If you modify these
 * after Gallery has created its various tables, it will stop working until
 * you modify it back.
 *
 * The possible database types are:
 *  mysqlt       MySQL (3.23.34a and newer)
 *  mysqli       MySQL (4.1 and newer) with PHP5 mysqli extension
 *  mysql        Older MySQL (no transactions)
 *  postgres7    PostgreSQL 7.x and newer
 *  postgres     PostgreSQL 6.x (not tested)
 *  oci8po       Oracle 9i and newer
 *  db2          IBM DB2 8.2 and newer
 *  ado_mssql    Microsoft SQL Server
 */
switch($GLOBALS['egw_info']['server']['db_type'])
{
	case 'mysql':
	case 'mysqlt':
	case 'mysqli':
		$storeConfig['type'] = extension_loaded('mysqli') ? 'mysqli' : $GLOBALS['egw_info']['server']['db_type'];
		break;
	case 'pgsql':
		$storeConfig['type'] = 'postgres7';
		break;
	case 'mssql':
		$storeConfig['type'] = 'ado_mssql';
		break;
	case 'oracle':
		$storeConfig['type'] = 'oci8po';
		break;
	default:
		die("Database type '{$GLOBALS['egw_info']['server']['db_type']}' not supported by Gallery2!");
}
$storeConfig['hostname'] = $GLOBALS['egw_info']['server']['db_host'];
$storeConfig['database'] = $GLOBALS['egw_info']['server']['db_name'];
$storeConfig['username'] = $GLOBALS['egw_info']['server']['db_user'];
$storeConfig['password'] = $GLOBALS['egw_info']['server']['db_pass'];
$storeConfig['tablePrefix'] = 'g2_';
$storeConfig['columnPrefix'] = 'g_';
$storeConfig['usePersistentConnections'] = $GLOBALS['egw_info']['server']['db_persistent'];
$gallery->setConfig('storage.config', $storeConfig);
//error_log("\$gallery->setConfig('storage.config',".str_replace("\n",',',print_r($storeConfig,true)).");");

/*
 * Put Gallery into debug mode.  Useful for tracking down problems with the
 * application.  Not a good idea to leave it this way, though.  Possible debug
 * choices are: 'buffered', 'logged', 'immediate' or false.  Don't forget to
 * use the quotes for any value but false!
 *
 * If you choose 'immediate', you'll see debugging information as soon as
 * Gallery generates it.  This can be useful at times, but it'll screw up some
 * parts of the application flow.
 *
 * If you choose 'buffered', Gallery will display debug information in a table
 * as part of the application.  You won't necessarily get *all* the debug
 * information but the application should work normally.
 *
 * If you choose 'logged', you must also specify:
 *    $gallery->setDebugLogFile('/path/to/writeable/file');
 * and all debug output will get printed into that file.  You'll get all the
 * debug output and the application will work normally.
 *
 * For best debugging output use this line:
 *
 * $gallery->setDebug('buffered');
 *
 */
$gallery->setDebug(false);

/*
 * Profiling mode.  You can enable profiling for different parts of G2 to get an
 * idea of what's fast and slow.  Right now the only options are to enable SQL
 * profiling:
 *
 * $gallery->setProfile(array('sql'));
 *
 */
$gallery->setProfile(false);

/*
 * Maintenance mode.  You can disable access to the site for anyone but
 * site administrators by setting this flag.  Set value below to:
 *  true (without quotes) - to use a basic notification page; themed
 *    view with admin login link when codebase is up to date, but a
 *    plain unstyled page when codebase has been updated but upgrader
 *    has not yet been run.
 *  url (with quotes) - provide a url where requests are redirected in
 *    either case described above.  Example: '/maintenance.html'
 *  false (without quotes) - maintenance mode off
 */
$gallery->setConfig('mode.maintenance', false);

/*
 * Embedded mode.  You can disable direct access to main.php (standalone G2)
 * by setting this flag.  Set value below to:
 *  true (without quotes) - block direct requests
 *  url (with quotes) - redirect requests to this url
 *  false (without quotes) - allow direct requests
 */
$gallery->setConfig('mode.embed.only', false);

/*
 * Allow a particular IP address to access the session (it still must know the
 * session id) even though it doesn't match the address/user agent that created
 * the session.  Put the address of validator.w3.org ('128.30.52.13') here to allow
 * validation of non-public Gallery pages from the links at the bottom of the page.
 */
$gallery->setConfig('allowSessionAccess', false);

/*
 * URL of Gallery codebase; required only for multisite install.
 */
$gallery->setConfig('galleryBaseUrl', '');

/* 
 * This setting can be used to override Gallery's auto-detection of the domain-name, 
 * protocol (http/https), URL path, and of the file & query string.
 * Most users can leave this empty. If the server is misconfigured or for very special
 * setups, this setting can be quite handy.
 * Examples (the positions of the slashes ('/') are important):
 *   override the path: $gallery->setConfig('baseUri', '/another/path/');
 *   override the host + path: $gallery->setConfig('baseUri', 'example.com/gallery2/');
 *   override the protocol + host + path + file:
 *           $gallery->setConfig('baseUri', 'https://example.com:8080/gallery2/index.php');
 */
$gallery->setConfig('baseUri', '');
?>
