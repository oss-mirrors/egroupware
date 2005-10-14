<?php
	/**************************************************************************\
	* eGroupWare - Headlines                                                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	if(@$GLOBALS['egw_info']['user']['preferences']['headlines']['mainscreen_showheadlines'])
	{
//		$GLOBALS['egw']->template->pfp('out', "\n<!-- Start Headlines -->\n", True);

		while($preference = @each($GLOBALS['egw_info']['user']['preferences']['headlines']))
		{
			if(is_int($preference[0]))
			{
				$sites[] = $preference[0];
			}
		}

		$title = '<center><font color="#FFFFFF">'.lang('Headlines').'</font></center>';

		$portalbox =& CreateObject('phpgwapi.listbox',
			array(
				'title'     => $title,
				'primary'   => $GLOBALS['egw']->prefs->data['theme']['navbar_bg'],
				'secondary' => $GLOBALS['egw']->prefs->data['theme']['navbar_bg'],
				'tertiary'  => $GLOBALS['egw']->prefs->data['theme']['navbar_bg'],
				'width'     => '100%',
				'outerborderwidth' => '0',
				'header_background_image' => $GLOBALS['egw']->common->image('bg_filler.png')
			)
		);
		$app_id = $GLOBALS['egw']->apps->name2id('headlines');
		$GLOBALS['portal_order'][] = $app_id;
		$var = Array(
			'up'       => Array('url' => '/set_box.php', 'app' => $app_id),
			'down'     => Array('url' => '/set_box.php', 'app' => $app_id),
			'close'    => Array('url' => '/set_box.php', 'app' => $app_id),
			'question' => Array('url' => '/set_box.php', 'app' => $app_id),
			'edit'     => Array('url' => '/set_box.php', 'app' => $app_id)
		);

		while(list($key,$value) = each($var))
		{
			$portalbox->set_controls($key,$value);
		}

		$portalbox->data = Array();
		$headlines =& CreateObject('headlines.headlines');

		while(list(,$site) = @each($sites))
		{
			$j++;
			$headlines->readtable($site);

			$portalbox->data[] = array(
				'text' => '<strong><b>' . lang('News from x',$headlines->display) . '</b></strong>',
				'link' => $headlines->base_url
			);

			$links = $headlines->getLinks($site);
			@reset($links);
			if($links == False)
			{
				$portalbox->data[] = array(
					'text' => lang('Unable to retrieve links') . '.',
					'link' => ''
				);
			}
			else
			{
				while(list($title,$link) = each($links))
				{
					if($link && $title)
					{
						$portalbox->data[] = array(
							'text' => stripslashes($title),
							'link' => stripslashes($link)
						);
					}
				}
			}
			$portalbox->data[] = array(
				'text' => '<hr>',
				'link' => ''
			);
		}

		$GLOBALS['egw']->template->pfp('out', $portalbox->draw());
		unset($portalbox);
		$GLOBALS['egw']->template->pfp('out', "\n<!-- End Headlines -->\n", True);
	}
?>
