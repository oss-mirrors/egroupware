<?php
	// $Id$

	// General initialization code.

	require('lib/defaults.php');
	//require('config.php');		// this has gone into the admin-page

	$sessionid = isset($_GET['sessionid']) ? $_GET['sessionid'] : (isset($_COOKIE['sessionid']) ? $_COOKIE['sessionid'] : '');

	if (!$sessionid)
	{
		// uncomment the next line if sitemgr should use a eGW domain different from the first one defined in your header.inc.php
		// and of cause change the name accordingly ;-)
		//$GLOBALS['phpgw_info']['server']['default_domain'] = 'other';

		$GLOBALS['phpgw_info']['flags'] = array(
			'disable_Template_class' => True,
			'login' => True,
			'currentapp' => 'login',
			'noheader'  => True,
		);
		include('../header.inc.php');
		$GLOBALS['phpgw_info']['flags']['currentapp'] = 'wiki';

		$c = CreateObject('phpgwapi.config','wiki');
		$c->read_repository();
		$config = $c->config_data;
		unset($c);

		if ($config['allow_anonymous'] && $config['anonymous_username'])
		{
			$sessionid = $GLOBALS['phpgw']->session->create($config['anonymous_username'],$config['anonymous_password'], 'text');
		}
		if (!$sessionid)
		{
			$GLOBALS['phpgw']->redirect('../login.php'.
				(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ?
				'?phpgw_forward='.urlencode('/wiki/index.php?'.$_SERVER['QUERY_STRING']):''));
			$GLOBALS['phpgw']->phpgw_exit();
		}
		// we redirect to the same page again, as we cant reset some of the defines in the API
		$GLOBALS['phpgw']->redirect_link('/wiki/index.php',$_SERVER['QUERY_STRING']);
	}
	// if we get here, we have a sessionid

	include('../header.inc.php');

	$c = CreateObject('phpgwapi.config','wiki');
	$c->read_repository();
	$config = $c->config_data;

	// anonymous sessions have no navbar !!!
	$GLOBALS['phpgw_info']['flags']['nonavbar'] = $config['allow_anonymous'] &&
		$config['anonymous_username'] == $GLOBALS['phpgw_info']['user']['account_lid'];

	$HomePage = (isset($config[wikihome])?$config[wikihome]:'eGroupWare');
	$InterWikiPrefix = (isset($config[InterWikiPrefix])?$config[InterWikiPrefix]:'EGroupWare');
	$EnableFreeLinks = (isset($config[Enable_Free_Links])?$config[Enable_Free_Links]:1);
	$EnableWikiLinks = (isset($config[Enable_Wiki_Links])?$config[Enable_Wiki_Links]:1);
	$EditWithPreview = (isset($config[Edit_With_Preview])?$config[Edit_With_Preview]:1);

	$UserName = $GLOBALS['phpgw_info']['user']['account_lid'];
	if (!($action == 'save' && !$Preview) && $action != 'admin' && !($action == 'prefs' && $Save) && $action != 'xml')
	{
		$GLOBALS['phpgw']->common->phpgw_header();
	}

	define('TemplateDir', 'template');

	$Charset = $GLOBALS['phpgw']->translation->charset();
	if (strtolower($Charset) == 'iso-8859-1')	// allow all iso-8859-1 extra-chars
	{
		$UpperPtn = "[A-Z\xc0-\xde]";
		$LowerPtn = "[a-z\xdf-\xff]";
		$AlphaPtn = "[A-Za-z\xc0-\xff]";
		$LinkPtn = $UpperPtn . $AlphaPtn . '*' . $LowerPtn . '+' .
			$UpperPtn . $AlphaPtn . '*(\\/' . $UpperPtn . $AlphaPtn . '*)?';
	}

	$WikiLogo = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/default/images/logo.png';

	require('lib/url.php');
	require('lib/messages.php');

	$pagestore = CreateObject('wiki.sowiki');

	$FlgChr = chr(255);                     // Flag character for parse engine.

	$Entity = array();                      // Global parser entity list.

	// Strip slashes from incoming variables.

	if(get_magic_quotes_gpc())
	{
		$document = stripslashes($document);
		$categories = stripslashes($categories);
		$comment = stripslashes($comment);
		if (is_array($page))
		{
			$page['name'] = stripslashes($page['name']);
		}
		else
		{
			$page = stripslashes($page);
		}
	}

	// Read user preferences from cookie.

	$prefstr = isset($_COOKIE[$CookieName])
	? $_COOKIE[$CookieName] : '';

	if(!empty($prefstr))
	{
		if(ereg("rows=([[:digit:]]+)", $prefstr, $result))
		{ $EditRows = $result[1]; }
		if(ereg("cols=([[:digit:]]+)", $prefstr, $result))
		{ $EditCols = $result[1]; }
		if(ereg("user=([^&]*)", $prefstr, $result))
		{ $UserName = urldecode($result[1]); }
		if(ereg("days=([[:digit:]]+)", $prefstr, $result))
		{ $DayLimit = $result[1]; }
		if(ereg("auth=([[:digit:]]+)", $prefstr, $result))
		{ $AuthorDiff = $result[1]; }
		if(ereg("min=([[:digit:]]+)", $prefstr, $result))
		{ $MinEntries = $result[1]; }
		if(ereg("hist=([[:digit:]]+)", $prefstr, $result))
		{ $HistMax = $result[1]; }
		if(ereg("tzoff=([[:digit:]]+)", $prefstr, $result))
		{ $TimeZoneOff = $result[1]; }
	}

	#if($Charset != '')
	#  { header("Content-Type: text/html; charset=$Charset"); }

	?>
