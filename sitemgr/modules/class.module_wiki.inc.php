<?php
	/**************************************************************************\
	* eGroupWare SiteMgr - Web Content Management                              *
	* http://www.egroupware.org                                                *
	* -------------------------------------------------                        *
	* Copyright (C) 2004 RalfBecker@outdoor-training.de                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	// This Module fetches Pages from the Wiki and displays them as blocks.
	// The wiki-links get rewriten in a way, that they call the same SiteMgr page, with an extra wikipage parameter.
	// The module is a hack / big mess until wiki gets rewriten in an objectoriented way ;-)

	global $ParseEngine,$DisplayEngine,$ViewMacroEngine;
	global $UpperPtn,$LowerPtn,$AlphaPtn,$LinkPtn,$UrlPtn,$InterwikiPtn,$MaxNesting,$MaxHeading,$MinEntries,$DayLimit;

	require(EGW_SERVER_ROOT.'/wiki/lib/defaults.php');

	global $Admin,$HomePage,$InterWikiPrefix,$EnableFreeLinks,$EnableWikiLinks;
	$config = $GLOBALS['egw_info']['server']['wiki'];
	$Admin = $config[emailadmin];
	$HomePage = (isset($config[wikihome])?$config[wikihome]:'eGroupWare');
	$InterWikiPrefix = (isset($config[InterWikiPrefix])?$config[InterWikiPrefix]:'EGroupWare');
	$EnableFreeLinks = (isset($config[Enable_Free_Links])?$config[Enable_Free_Links]:1);
	$EnableWikiLinks = (isset($config[Enable_Wiki_Links])?$config[Enable_Wiki_Links]:1);

	global $Charset,$UserName;
	$Charset = $GLOBALS['egw']->translation->charset();
	$UserName = $GLOBALS['egw_info']['user']['account_lid'];

	require(EGW_SERVER_ROOT.'/wiki/lib/url.php');
	require(EGW_SERVER_ROOT.'/wiki/lib/messages.php');

	global $pagestore;
	$GLOBALS['pagestore'] =& CreateObject('wiki.sowiki');

	global $FlgChr,$Entity;
	$FlgChr = chr(255);                     // Flag character for parse engine.
	$Entity = array();                      // Global parser entity list.

	require(EGW_SERVER_ROOT.'/wiki/parse/transforms.php');
	require(EGW_SERVER_ROOT.'/wiki/parse/main.php');
	require(EGW_SERVER_ROOT.'/wiki/parse/macros.php');
	require(EGW_SERVER_ROOT.'/wiki/parse/html.php');

	function isEditable($page_mutable=True)
	{
		return False;
	}

	class module_wiki extends Module
	{
		function module_wiki()
		{
			$this->arguments = array(
				'startpage' => array(
					'type' => 'textfield',
					'label' => lang('Wiki startpage')
				),
				'title' => array(
					'type' => 'select',
					'label' => lang('Show the title of the wiki page'),
					'options' => array(
						0 => lang('never'),
						1 => lang('only on the first page'),
						2 => lang('on all pages'),
					)
				)	
			);
			$this->properties = array();
			$this->title = lang('Wiki');
			$this->description = lang('Use this module for displaying wiki-pages');

			$this->wikipage_param = 'wikipage';		// name of the get-param used to transport the wiki-page-names
		}

		function get_content(&$arguments,$properties)
		{
			if (!is_readable(EGW_SERVER_ROOT.'/wiki') || !@$GLOBALS['egw_info']['user']['apps']['wiki'])
			{
				return lang('You have no rights to view wiki content or the wiki is not installed at all !!!');
			}

			$wikipage = empty($_GET[$this->wikipage_param]) ? (empty($arguments['startpage']) ? $GLOBALS['HomePage'] : $arguments['startpage']) : stripslashes(urldecode($_GET['wikipage']));
			$parts = explode(':',$wikipage);
			if (count($parts) > 1)
			{
				$lang = array_pop($parts);
				if (strlen($lang) == 2 || strlen($lang) == 5 && $lang[2] == '-')
				{
					$wikipage = implode(':',$parts);
				}
				else
				{
					$lang = '';
				}
			}
			$pg = $GLOBALS['pagestore']->page($wikipage,$lang);
			$pg->read();

			// we need to set ViewBase to the name of the actual page, to get wiki to stay inside this page
			global $ViewBase;
			$ViewBase = $_SERVER['PHP_SELF'].'?';
			foreach($_GET as $name => $val)
			{
				if ($name != $this->wikipage_param)
				{
					$ViewBase .= $name.'='.urlencode($val).'&';
				}
			}
			$ViewBase .= $this->wikipage_param.'=';

			$title = '';
			if ($arguments['title'] == 2 || $arguments['title'] == 1 && $wikipage == $arguments['startpage'])
			{
				$title = '<div class="wiki-title">' . htmlspecialchars($pg->title) . "</div>\n";
			}
			return $title . '<div class="wiki-content">' . parseText($pg->text, $GLOBALS['ParseEngine'], $wikipage) . "</div>\n";
		}
	}
