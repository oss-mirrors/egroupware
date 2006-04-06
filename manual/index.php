<?php
	/**************************************************************************\
	* eGroupWare - Online User manual                                          *
	* http://www.eGroupWare.org                                                *
	* Written and (c) by RalfBecker@outdoor-training.de                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/**
	 * Check if we allow anon access and with which creditials
	 * 
	 * @param array &$anon_account anon account_info with keys 'login', 'passwd' and optional 'passwd_type'
	 * @return boolean true if we allow anon access, false otherwise
	 */
	function manual_check_anon_access(&$anon_account)
	{
		$c =& CreateObject('phpgwapi.config','manual');
		$c->read_repository();
		$config =& $c->config_data;
		unset($c);

		if ($config['manual_allow_anonymous'] && $config['manual_anonymous_user'])
		{
			$anon_account = array(
				'login'  => $config['manual_anonymous_user'],
				'passwd' => $config['manual_anonymous_password'],
				'passwd_type' => 'text',
			);
			return true;
		}
		return false;
	}
		
	// uncomment the next line if manual should use a eGW domain different from the first one defined in your header.inc.php
	// and of cause change the name accordingly ;-)
	// $GLOBALS['egw_info']['user']['domain'] = $GLOBALS['egw_info']['server']['default_domain'] = 'developers';

	$GLOBALS['egw_info'] = array(
		'flags' => array(
			'currentapp' => 'manual',
			'autocreate_session_callback' => 'manual_check_anon_access',
			'disable_Template_class' => True,
			'noheader'  => True,
			'nonavbar'   => True,
		),
	);
	include('../header.inc.php');

	ExecMethod('manual.uimanual.view');

	$GLOBALS['egw']->common->egw_footer();
