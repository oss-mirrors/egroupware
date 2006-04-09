<?php
	/**************************************************************************\
	* eGroupWare - Info Log on Homepage                                        *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	if (($showevents = $GLOBALS['egw_info']['user']['preferences']['infolog']['homeShowEvents']))
	{
		$save_app = $GLOBALS['egw_info']['flags']['currentapp'];
		$GLOBALS['egw_info']['flags']['currentapp'] = 'infolog';

		$GLOBALS['egw']->translation->add_app('infolog');

		$app_id = $GLOBALS['egw']->applications->name2id('infolog');
		$GLOBALS['portal_order'][] = $app_id;

		$infolog =& CreateObject('infolog.uiinfolog');
		$infolog->called_by = 'home';

		if (in_array($showevents,array('1','2'))) $showevents = 'own-open-today';
		$html = $infolog->index(array('nm' => array('filter' => $showevents)),'','',0,False,True);
		$title = lang('InfoLog').' - '.lang($infolog->filters['own-open-today']);
		unset($infolog);

		$portalbox =& CreateObject('phpgwapi.listbox',array(
			'title'     => $title,
			'primary'   => $GLOBALS['egw_info']['theme']['navbar_bg'],
			'secondary' => $GLOBALS['egw_info']['theme']['navbar_bg'],
			'tertiary'  => $GLOBALS['egw_info']['theme']['navbar_bg'],
			'width'     => '100%',
			'outerborderwidth' => '0',
			'header_background_image' => $GLOBALS['egw']->common->image('phpgwapi/templates/default','bg_filler')
		));
		foreach(array('up','down','close','question','edit') as $key)
		{
			$portalbox->set_controls($key,Array('url' => '/set_box.php', 'app' => $app_id));
		}
		$portalbox->data = $data;

		if (!file_exists(EGW_SERVER_ROOT.($css_file ='/infolog/templates/'.$GLOBALS['egw_info']['user']['preferences']['common']['template_set'].'/app.css')))
		{
			$css_file = '/infolog/templates/default/app.css';
		}
		echo '
<!-- BEGIN InfoLog info -->
<style type="text/css">
<!--
	@import url('.$GLOBALS['egw_info']['server']['webserver_url'].$css_file.');
-->
</style>
'.			$portalbox->draw($html)."\n<!-- END InfoLog info -->\n";

		unset($portalbox);
		unset($html);
		$GLOBALS['egw_info']['flags']['currentapp'] = $save_app;
	}
	unset($showevents);
?>
