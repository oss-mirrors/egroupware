<?php
	/**************************************************************************\
	* phpGroupWare - news headlines                                            *
	* http://www.phpgroupware.org                                              *
	* Written by Mark Peters <mpeters@satx.rr.com>                             *
	* Based on pheadlines 0.1 19991104 by Dan Steinman <dan@dansteinman.com>   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'           => 'headlines',
		'enable_network_class' => True,
		'noheader'             => True,
		'nonavbar'             => True
	);
	include('../header.inc.php');

	if (! count($phpgw_info['user']['preferences']['headlines']))
	{
		Header('Location: ' . $phpgw->link('/headlines/preferences.php'));
	}
	else
	{
		$phpgw->common->phpgw_header();
		echo parse_navbar();
	}

	if (! $phpgw_info['user']['preferences']['headlines']['headlines_layout'])
	{
		$phpgw->preferences->change('headlines','headlines_layout','basic');
		$phpgw->preferences->commit(True);
		$phpgw_info['user']['preferences']['headlines']['headlines_layout'] = 'basic';
	}

	while ($preference = each($phpgw_info['user']['preferences']['headlines']))
	{
		if ($preference[0] != 'headlines_layout')
		{
			$sites[] = $preference[0];
		}
	}

	$headlines = new headlines;
	$phpgw->template->set_file(array(
		'layout_row' => 'layout_row.tpl',
		'form'       => $phpgw_info['user']['preferences']['headlines']['headlines_layout'] . '.tpl'
	));
	$phpgw->template->set_block('form','channel');
	$phpgw->template->set_block('form','row');

	$j = 0;
	$i = count($sites);
	while (list(,$site) = each($sites))
	{
		$j++;
		$headlines->readtable($site);

		$phpgw->template->set_var('channel_url',$headlines->base_url);
		$phpgw->template->set_var('channel_title',$headlines->display);

		$links = $headlines->getLinks($site);
		if($links == False)
		{
			$var = Array(
				'item_link'	=> '',
				'item_label'	=> '',
				'error'	=> lang('Unable to retrieve links').'.'
			);
			$GLOBALS['phpgw']->template->set_var($var);
			$s .= $GLOBALS['phpgw']->template->parse('o_','row');
		}
		else
		{
			while (list($title,$link) = each($links))
			{

				$var = Array(
					'item_link'	=> stripslashes($link),
					'item_label'	=> stripslashes($title)
				);
				$GLOBALS['phpgw']->template->set_var($var);
				$s .= $GLOBALS['phpgw']->template->parse('o_','row');
			}
		}
		$phpgw->template->set_var('rows',$s);
		unset($s);

		$phpgw->template->set_var('section_' . $j,$phpgw->template->parse('o','channel'));

		if ($j == 3 || $i == 1)
		{
			$phpgw->template->pfp('out','layout_row');
			$phpgw->template->set_var('section_1', '');
			$phpgw->template->set_var('section_2', '');
			$phpgw->template->set_var('section_3', '');
			$j = 0;
		}
		$i--;
	}

	$phpgw->common->phpgw_footer();
?>
