<?php
  /**************************************************************************\
  * eGroupWare API loader                                                    *
  * This file was originaly written by Dan Kuykendall and Joseph Engo        *
  * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
  * Parts Copyright (C) 2003 Free Software Foundation                        *
  * -------------------------------------------------------------------------*
  * Rewritten by RalfBecker@outdoor-training.de to store the eGW enviroment  *
  * (egw-object and egw_info-array) in a php-session and restore it from     *
  * there instead of creating it completly new on each page-request.         *
  * The enviroment gets now created by the egw-class                         *
  * -------------------------------------------------------------------------*
  * This library is part of the eGroupWare API http://www.egroupware.org     *
  * ------------------------------------------------------------------------ *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	/**
	 * New written class to create the eGW enviroment AND restore it from a php-session
	 *
	 * @author	RalfBecker@outdoor-training.de
	 * @copyright GPL
	 * @package api
	 * @access	public
	 */
	class egw
	{
		/**
		 * Turn on debug mode. Will output additional data for debugging purposes.
		 * @var	string
		 * @access	public
		 */
		var $debug = 0;		// This will turn on debugging information.
		/**
		 * Instance of the db-object
		 * 
		 * @var egw_db
		 */
		var $db;
		var $config_table = 'egw_config';
		/**
		 * Instance of the account object
		 *
		 * @var accounts
		 */
		var $accounts;
		/**
		 * Instace of the common object
		 *
		 * @var common
		 */
		var $common;

		/**
		 * savant2 templating object
		 * 
		 * @var tplsavant2 
		 */
		var $tplsav2;

		/**
		 * Constructor: Instantiates the sub-classes
		 *
		 * @author RalfBecker@outdoor-training.de
		 * @param array $domain_names array with valid egw-domain names
		 */
		function egw($domain_names=null)
		{
			$GLOBALS['egw'] =& $this;	// we need to be immediately available there for the other classes we instantiate
			// for the migration: reference us to the old phpgw object
			$GLOBALS['phpgw'] =& $this;
			$this->setup($domain_names,True);
		}

		/**
		 * Called every time the constructor is called.  Also called by sessions to ensure the correct db,
		 *  in which case we do not recreate the session object.
		 * @author RalfBecker@outdoor-training.de (moved to setup() by milos@groupwhere.org
		 * @param array $domain_names array with valid egw-domain names
		 * @param boolean $createsessionobject True to create the session object (default=True)
		 */
		function setup($domain_names,$createsessionobject=True)
		{
			// create the DB-object
			$this->db =& CreateObject('phpgwapi.egw_db');
			if ($this->debug)
			{
				$this->db->Debug = 1;
			}
			$this->db->set_app('phpgwapi');

			$this->db->Halt_On_Error = 'no';
			$this->db->connect(
				$GLOBALS['egw_info']['server']['db_name'],
				$GLOBALS['egw_info']['server']['db_host'],
				$GLOBALS['egw_info']['server']['db_port'],
				$GLOBALS['egw_info']['server']['db_user'],
				$GLOBALS['egw_info']['server']['db_pass'],
				$GLOBALS['egw_info']['server']['db_type']
			);
			// check if eGW is already setup, if not redirect to setup/
			$this->db->select($this->config_table,'COUNT(config_name)',false,__LINE__,__FILE__);
			if(!$this->db->next_record())
			{
				$setup_dir = str_replace($_SERVER['PHP_SELF'],'index.php','setup/');

				// we check for the old table too, to not scare updating users ;-)
				$this->db->select('phpgw_config','COUNT(config_name)',false,__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					throw new Exception('<center><b>Fatal Error:</b> You need to <a href="' . $setup_dir . '">update eGroupWare</a> before you can continue using it.</center>',999);
				}
				else
				{
					throw new Exception('<center><b>Fatal Error:</b> It appears that you have not created the database tables for '
						.'eGroupWare.  Click <a href="' . $setup_dir . '">here</a> to run setup.</center>',999);
				}
				exit;
			}
			$this->db->Halt_On_Error = 'yes';

			// Set the DB's client charset if a system-charset is set
			$this->db->select($this->config_table,'config_value',array(
				'config_app'  => 'phpgwapi',
				'config_name' => 'system_charset',
			),__LINE__,__FILE__);
			if ($this->db->next_record() && $this->db->f(0))
			{
				$this->db->Link_ID->SetCharSet($this->db->f(0));
			}
			// load up the $GLOBALS['egw_info']['server'] array
			$this->db->select($this->config_table,'*',array('config_app'  => 'phpgwapi'),__LINE__,__FILE__);
			while (($row = $this->db->row(true)))
			{
				$GLOBALS['egw_info']['server'][$row['config_name']] = stripslashes($row['config_value']);
			}
			// setup the other subclasses
			$this->log            =& CreateObject('phpgwapi.errorlog');
			$this->translation    =& CreateObject('phpgwapi.translation');
			$this->common         =& CreateObject('phpgwapi.common');
			$this->hooks          =& CreateObject('phpgwapi.hooks');
			$this->auth           =& CreateObject('phpgwapi.auth');
			$this->accounts       =& CreateObject('phpgwapi.accounts');
			$this->acl            =& CreateObject('phpgwapi.acl');
			/* Do not create the session object if called by the sessions class.  This way
			 * we ensure the correct db based on the user domain.
			 */
			if($createsessionobject)
			{
				$this->session    =& CreateObject('phpgwapi.sessions',$domain_names);
			}
			$this->preferences    =& CreateObject('phpgwapi.preferences');
			$this->applications   =& CreateObject('phpgwapi.applications');
			$this->contenthistory =& CreateObject('phpgwapi.contenthistory');
			$this->datetime       =& CreateObject('phpgwapi.egw_datetime');

			include_once(EGW_INCLUDE_ROOT.'/phpgwapi/inc/class.error.inc.php');

			register_shutdown_function(array($this->common, 'egw_final'));

			if ($GLOBALS['egw_info']['flags']['currentapp'] != 'login' && $GLOBALS['egw_info']['flags']['currentapp'] != 'logout')
			{
				$this->verify_session();
				$this->applications->read_installed_apps();	// to get translated app-titles, has to be after verify_session
				
				$this->define_egw_constants();

				// setup the new eGW framework (template sets)
				$class = $GLOBALS['egw_info']['server']['template_set'].'_framework';
				require_once(EGW_INCLUDE_ROOT . '/phpgwapi/templates/' . $GLOBALS['egw_info']['server']['template_set'].
					'/class.'.$class.'.inc.php');
				$this->framework = new $class;		// =& removed because of problems with php4, does not matter with php5 anyway!

				$this->check_app_rights();

				$this->load_optional_classes();
			}
			else	// set the defines for login, in case it's more then just login
			{
				$this->define_egw_constants();
			}
		}

		/**
		 * __wakeup function gets called by php while unserializing the egw-object, eg. reconnects to the DB
		 *
		 * @author RalfBecker@outdoor-training.de
		 */
		function __wakeup()
		{
			$GLOBALS['egw'] =& $this;	// we need to be immediately available there for the other classes we instantiate
			// for the migration: reference us to the old phpgw object
			$GLOBALS['phpgw'] =& $this;
			register_shutdown_function(array($this->common, 'egw_final'));

			$this->db->connect();	// we need to re-connect
			// Set the DB's client charset if a system-charset is set
			$this->db->select($this->config_table,'config_value',array(
				'config_app'  => 'phpgwapi',
				'config_name' => 'system_charset',
			),__LINE__,__FILE__);
			if ($this->db->next_record() && $this->db->f(0))
			{
				$this->db->Link_ID->SetCharSet($this->db->f(0));
			}
			
			if ($GLOBALS['egw_info']['system']['system_charset'])
			{
				$this->db->Link_ID->SetCharSet($GLOBALS['egw_info']['system']['system_charset']);
			}
			foreach(array('translation','hooks','auth','accounts','acl','session','preferences','applications','contenthistory','contacts') as $class)
			{
				if (is_object($this->$class->db))
				{
					$this->$class->db->Link_ID =& $this->db->Link_ID;
				}
			}
			$this->define_egw_constants();
		}

		/**
		 * wakeup2 funcontion needs to be called after unserializing the egw-object
		 *
		 * It adapts the restored object/enviroment to the changed (current) application / page-request
		 *
		 * @author RalfBecker@outdoor-training.de
		 */
		function wakeup2()
		{
			// do some application specific stuff, need to be done as we are different (current) app now
			if (is_object($this->template))
			{
				$this->template->set_root(EGW_APP_TPL);
			}
			$this->translation->add_app($GLOBALS['egw_info']['flags']['currentapp']);

			// verify the session
			$GLOBALS['egw']->verify_session();
			$GLOBALS['egw']->check_app_rights();

			$this->load_optional_classes();
		}

		/**
		 * load optional classes by mentioning them in egw_info[flags][enable_CLASS_class] => true
		 *
		 * Also loads the template-class if not egw_info[flags][disable_Template_class] is set
		 *
		 * Maybe the whole thing should be depricated ;-)
		 */
		function load_optional_classes()
		{
			// load classes explicitly mentioned
			foreach($GLOBALS['egw_info']['flags'] as $enable_class => $enable)
			{
				if ($enable && substr($enable_class,0,7) == 'enable_')
				{
					$enable_class = substr($enable_class,7,-6);
					$this->$enable_class =& CreateObject('phpgwapi.'.$enable_class);
				}
			}

			// load the template class, if not turned off
			if(!$GLOBALS['egw_info']['flags']['disable_Template_class'])
			{
				$this->template =& CreateObject('phpgwapi.Template',EGW_APP_TPL);
			}

			// output the header unless the developer turned it off
			if (!@$GLOBALS['egw_info']['flags']['noheader'])
			{
				$GLOBALS['egw']->common->egw_header();
			}

			// Load the (depricated) app include files if they exists
			if (EGW_APP_INC != "" && ! preg_match ('/phpgwapi/i', EGW_APP_INC) &&
			file_exists(EGW_APP_INC . '/functions.inc.php') && !isset($_GET['menuaction']))
			{
				include(EGW_APP_INC . '/functions.inc.php');
			}
			if (!@$GLOBALS['egw_info']['flags']['noheader'] && !@$GLOBALS['egw_info']['flags']['noappheader'] &&
			file_exists(EGW_APP_INC . '/header.inc.php') && !isset($_GET['menuaction']))
			{
				include(EGW_APP_INC . '/header.inc.php');
			}
		}

		/**
		 * Verfiy there is a valid session
		 *
		 * One can specify a callback, which gets called if there's no valid session. If the callback returns true, the parameter
		 * containst account-details (in keys login, passwd and passwd_type) to automatic create an (anonymous session)
		 *
		 * It also checks if enforce_ssl is set in the DB and redirects to the https:// version of the site.
		 *
		 * If there is no valid session and none could be automatic created, the function will redirect to login and NOT return
		 */
		function verify_session()
		{
			if(isset($GLOBALS['egw_info']['server']['enforce_ssl']) && !$_SERVER['HTTPS'])
			{
				Header('Location: https://' . $GLOBALS['egw_info']['server']['hostname'] . $GLOBALS['egw_info']['server']['webserver_url'] . $_SERVER['REQUEST_URI']);
				exit;
			}
			// check if we have a session, if not try to automatic create one
			if ($this->session->verify()) return true;

			if (($account_callback = $GLOBALS['egw_info']['flags']['autocreate_session_callback']) && function_exists($account_callback) &&
				($sessionid = $account_callback($account)) === true)	// $account_call_back returns true, false or a session-id
			{
				$sessionid = $this->session->create($account);
			}
			if (!$sessionid)
			{
				//echo "<p>account_callback='$account_callback', account=".print_r($account,true).", sessionid=$sessionid</p>\n"; exit;
				// we forward to the same place after the re-login
				if ($GLOBALS['egw_info']['server']['webserver_url'] && $GLOBALS['egw_info']['server']['webserver_url'] != '/')
				{
					list(,$relpath) = explode($GLOBALS['egw_info']['server']['webserver_url'],$_SERVER['PHP_SELF'],2);
				}
				else	// the webserver-url is empty or just a slash '/' (eGW is installed in the docroot and no domain given)
				{
					if (preg_match('/^https?:\/\/[^\/]*\/(.*)$/',$relpath=$_SERVER['PHP_SELF'],$matches))
					{
						$relpath = $matches[1];
					}
				}
				// this removes the sessiondata if its saved in the URL
				$query = preg_replace('/[&]?sessionid(=|%3D)[^&]+&kp3(=|%3D)[^&]+&domain=.*$/','',$_SERVER['QUERY_STRING']);
				Header('Location: '.$GLOBALS['egw_info']['server']['webserver_url'].'/login.php?cd=10&phpgw_forward='.urlencode($relpath.(!empty($query) ? '?'.$query : '')));
				exit;
			}
		}

		/**
		 * Verify the user has rights for the requested app
		 *
		 * If the user has no rights for the app (eg. called via URL) he get a permission denied page (this function does NOT return)
		 */
		function check_app_rights()
		{
			if ($GLOBALS['egw_info']['flags']['currentapp'] != 'about')
			{
				// This will need to use ACL in the future
				if (!$GLOBALS['egw_info']['user']['apps'][$currentapp = $GLOBALS['egw_info']['flags']['currentapp']] ||
					($GLOBALS['egw_info']['flags']['admin_only'] && !$GLOBALS['egw_info']['user']['apps']['admin']))
				{
					if ($currentapp == 'admin' || $GLOBALS['egw_info']['flags']['admin_only'])
					{
						throw new egw_exception_no_permission_admin();
					}
					else
					{
						throw new egw_exception_no_permission_app($currentapp);
					}
					// old code no longer called
					$this->common->egw_header();
					if ($GLOBALS['egw_info']['flags']['nonavbar'])
					{
						echo parse_navbar();
					}
					error_log('Permission denied, attempted to access '.$GLOBALS['egw_info']['flags']['currentapp']);
					$this->log->write(array('text'=>'W-Permissions, Attempted to access %1','p1'=>$GLOBALS['egw_info']['flags']['currentapp']));
					
					$this->tplsav2 = CreateObject('phpgwapi.tplsavant2');	// create it only when neccessary
					$this->tplsav2->assign('currentapp',$GLOBALS['egw_info']['flags']['currentapp']);
					$this->tplsav2->set_tpl_path($this->tplsav2->get_tpl_dir(false,'phpgwapi'));
					$this->tplsav2->display('appl_access_not_permitted.tpl.php');
					$this->common->egw_exit(True);
				}
			}
		}

		/**
		 * create all the defines / constants of the eGW-environment (plus the deprecated phpgw ones)
		 */
		function define_egw_constants()
		{
			define('SEP',filesystem_separator());
			define('EGW_ACL_READ',1);
			define('EGW_ACL_ADD',2);
			define('EGW_ACL_EDIT',4);
			define('EGW_ACL_DELETE',8);
			define('EGW_ACL_PRIVATE',16);
			define('EGW_ACL_GROUP_MANAGERS',32);
			define('EGW_ACL_CUSTOM_1',64);
			define('EGW_ACL_CUSTOM_2',128);
			define('EGW_ACL_CUSTOM_3',256);
			// and the old ones
			define('PHPGW_ACL_READ',1);
			define('PHPGW_ACL_ADD',2);
			define('PHPGW_ACL_EDIT',4);
			define('PHPGW_ACL_DELETE',8);
			define('PHPGW_ACL_PRIVATE',16);
			define('PHPGW_ACL_GROUP_MANAGERS',32);
			define('PHPGW_ACL_CUSTOM_1',64);
			define('PHPGW_ACL_CUSTOM_2',128);
			define('PHPGW_ACL_CUSTOM_3',256);
			// A few hacker resistant constants that will be used throught the program
			define('EGW_TEMPLATE_DIR', $this->common->get_tpl_dir('phpgwapi'));
			define('EGW_IMAGES_DIR', $this->common->get_image_path('phpgwapi'));
			define('EGW_IMAGES_FILEDIR', $this->common->get_image_dir('phpgwapi'));
			define('EGW_APP_ROOT', $this->common->get_app_dir());
			define('EGW_APP_INC', $this->common->get_inc_dir());
			define('EGW_APP_TPL', $this->common->get_tpl_dir());
			define('EGW_IMAGES', $this->common->get_image_path());
			define('EGW_APP_IMAGES_DIR', $this->common->get_image_dir());
			// and the old ones
			define('PHPGW_TEMPLATE_DIR',EGW_TEMPLATE_DIR);
			define('PHPGW_IMAGES_DIR',EGW_IMAGES_DIR);
			define('PHPGW_IMAGES_FILEDIR',EGW_IMAGES_FILEDIR);
			define('PHPGW_APP_ROOT',EGW_APP_ROOT);
			define('PHPGW_APP_INC',EGW_APP_INC);
			define('PHPGW_APP_TPL',EGW_APP_TPL);
			define('PHPGW_IMAGES',EGW_IMAGES);
			define('PHPGW_APP_IMAGES_DIR',EGW_APP_IMAGES_DIR);
		}

		/**
		 * force the session cache to be re-created, because some of it's data changed
		 *
		 * Needs to be called if user-preferences, system-config or enabled apps of the current user have been changed and
		 * the change should have immediate effect
		 */
		function invalidate_session_cache()
		{
			unset($_SESSION['egw_info_cache']);
			unset($_SESSION['egw_included_files']);
			unset($_SESSION['egw_object_cache']);
		}

		/**
		 * run string through htmlspecialchars and stripslashes
		 *
		 * @param string $s
		 * @return string The string with html special characters replaced with entities
		 */
		function strip_html($s)
		{
			return htmlspecialchars(stripslashes($s));
		}

		/**
		 * Link url generator
		 *
		 * Used for backwards compatibility and as a shortcut. If no url is passed, it will use PHP_SELF. Wrapper to session->link()
		 *
		 * @param string	$string	The url the link is for
		 * @param string/array	$extravars	Extra params to be passed to the url
		 * @return string	The full url after processing
		 */
		function link($url = '', $extravars = '')
		{
			return $this->session->link($url, $extravars);
		}

		function redirect_link($url = '',$extravars='')
		{
			$this->redirect($this->session->link($url, $extravars));
		}

		/**
		 * Handles redirects under iis and apache, it does NOT return (calls exit)
		 *
		 * This function handles redirects under iis and apache it assumes that $phpgw->link() has already been called
		 *
		 * @param  string The url ro redirect to
		 */
		function redirect($url = '')
		{
			/* global $HTTP_ENV_VARS; */

			$iis = @strpos($GLOBALS['HTTP_ENV_VARS']['SERVER_SOFTWARE'], 'IIS', 0);

			if(!$url)
			{
				$url = $_SERVER['PHP_SELF'];
			}
			if($iis)
			{
				echo "\n<HTML>\n<HEAD>\n<TITLE>Redirecting to $url</TITLE>";
				echo "\n<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$url\">";
				echo "\n</HEAD><BODY>";
				echo "<H3>Please continue to <a href=\"$url\">this page</a></H3>";
				echo "\n</BODY></HTML>";
			}
			else
			{
				Header("Location: $url");
				print("\n\n");
			}
			exit;
		}

		/**
		 * Shortcut to translation class
		 *
		 * This function is a basic wrapper to translation->translate()
		 *
		 * @deprecated only used in the old timetracker
		 * @param  string	The key for the phrase
		 * @see	translation->translate()
		 */
		function lang($key,$args=null)
		{
			if (!is_array($args))
			{
				$args = func_get_args();
				array_shift($args);
			}
			return $this->translation->translate($key,$args);
		}
	}
