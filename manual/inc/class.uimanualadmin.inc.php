<?php
/**************************************************************************\
* eGroupWare - Online User manual                                          *
* http://www.eGroupWare.org                                                *
* Written and copyright (c) 2004-6 by RalfBecker@outdoor-training.de       *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

class uimanualadmin extends wiki_xml
{
	var $public_functions = array(
		'import' =>True,
	);
	var $manual_config;
	var $mconfig;

	function __construct()
	{
		CreateObject('manual.uimanual');	// sets the default config

		$this->mconfig =& CreateObject('phpgwapi.config','manual');
		$this->mconfig->read_repository();
		$this->manual_config =& $this->mconfig->config_data;

		$this->wiki_id = (int) $this->manual_config['manual_wiki_id'];
		parent::__construct($this->wiki_id);	// call the constructor of the class we extend
	}

	function import()
	{
		$url = $this->manual_config['manual_update_url'];
		$from = explode('/',$url);
		$from = count($from) > 2 ? $from[2] : $url;

		if (($langs = $GLOBALS['egw']->translation->get_installed_langs()))
		{
			$langs = implode(',',array_keys($langs));
			$url .= (strpos($url,'?') === False ? '?' : '&').'lang='.$langs;
		}
		// only do an incremental update if the langs are unchanged and we already did an update
		if ($langs == $this->manual_config['manual_langs'] && $this->manual_config['manual_updated'])
		{
			$url .= (strpos($url,'?') === False ? '?' : '&').'modified='.(int) $this->manual_config['manual_updated'];
		}

		$GLOBALS['egw_info']['flags']['app_header'] = lang('manual').' - '.lang('download');
		$GLOBALS['egw']->common->egw_header();
		parse_navbar();
		echo str_pad('<h3>'.lang('Starting import from %1, this might take several minutes (specialy if you start it the first time) ...',
			'<a href="'.$url.'" target="_blank">'.$from.'</a>')."</h3>\n",4096);	// dirty hack to flushes the buffer;
		@set_time_limit(0);

		$status = wiki_xml::import($url,True);

		$this->manual_config['manual_updated'] = $status['meta']['exported'];
		$this->manual_config['manual_langs'] = $langs;
		$this->mconfig->save_repository();

		echo '<h3>'.lang('%1 manual page(s) added or updated',count($status['imported']))."</h3>\n";

		$GLOBALS['egw']->common->egw_footer();
	}

	function menu($args)
	{
		display_section('manual','manual',array(
			'Site Configuration' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uiconfig.index&appname=manual'),
			'install or update the manual-pages' => $GLOBALS['egw']->link('/index.php',array('menuaction'=>'manual.uimanualadmin.import')),
		));
	}

	function config($args)
	{
		$GLOBALS['egw_info']['server']['found_validation_hook'] = True;

		return true;
	}
}

function final_validation($settings)
{
	//echo "final_validation()"; _debug_array($settings);
	if ($settings['manual_allow_anonymous'])
	{
		// check if anon user set and exists
		if (!$settings['manual_anonymous_user'] || !($anon_user = $GLOBALS['egw']->accounts->name2id($settings['manual_anonymous_user'])))
		{
			$GLOBALS['config_error'] = 'Anonymous user does NOT exist!';
		}
		else	// check if anon user has run-rights for manual
		{
			$locations = $GLOBALS['egw']->acl->get_all_location_rights($anon_user,'manual');
			if (!$locations['run'])
			{
				$GLOBALS['config_error'] = 'Anonymous user has NO run-rights for the application!';
			}
		}
	}
}
