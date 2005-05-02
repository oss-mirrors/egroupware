<?php
	/**************************************************************************\
	* eGroupWare SiteMgr - Web Content Management                              *
	* http://www.egroupware.org                                                *
	* Written and (c) by RalfBecker@outdoor-training.de                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['egw_info']['flags'] = array
	(
			'currentapp' => 'sitemgr',
			'nonavbar'   => True,
			'noheader'   => True,
			'noapi'      => False
	);
	include('../header.inc.php');

	$GLOBALS['Common_BO'] =& CreateObject('sitemgr.Common_BO');
	$GLOBALS['Common_BO']->sites->set_currentsite(False,'Administration');

	$GLOBALS['egw']->template->set_file('theme_info','theme_info.tpl');
	if ($_GET['theme'] && ($info = $GLOBALS['Common_BO']->theme->getThemeInfos($_GET['theme'])))
	{
		if ($info['thumbnail']) $info['thumbnail'] = '<img src="'.$info['thumbnail'].'" />';
		$GLOBALS['egw']->template->set_var($info);
		$GLOBALS['egw']->template->set_var(array(
			'lang_author' => lang('Author'),
			'lang_copyright' => lang('Copyright'),
		));
	}
	$GLOBALS['egw']->template->pfp('out','theme_info');

	$GLOBALS['egw']->common->egw_exit();
