<?php
	/**************************************************************************\
	* phpGroupWare - Headlines Administration                                  *
	* http://www.phpgroupware.org                                              *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp' => 'admin',
		'nonavbar'   => True,
		'noheader'   => True
	);
	include('../header.inc.php');

	if ($submit)
	{
		if (! $n_display)
		{
			$errors[] = lang('You must enter a display');
		}

		if (! $n_base_url)
		{
			$errors[] = lang('You must enter a base url');
		}

		if (! $n_newsfile)
		{
			$errors[] = lang('You must enter a news url');
		}

		if (! $n_cachetime)
		{
			$errors[] = lang('You must enter the number of minutes between reload');
		}

		if (! $n_listings)
		{
			$errors[] = lang('You must enter the number of listings display');
		}

		if ($n_listings && ! ereg('^[0-9]+$',$n_listings))
		{
			$errors[] = lang('You can only enter numbers for listings display');
		}

		if ($n_cachetime && ! ereg('^[0-9]+$',$n_cachetime))
		{
			$errors[] = lang('You can only enter numbers minutes bewteen refresh');
		}

		$phpgw->db->query("select display from news_site where base_url='"
				. addslashes(strtolower($n_base_url)) . "' and newsfile='"
				. addslashes(strtolower($n_newsfile)) . "'");
		$phpgw->db->next_record();
		if ($phpgw->db->f('display'))
		{
			$errors[] = lang('That site has already been entered');
		}

		if (! is_array($errors))
		{
			$phpgw->db->lock('news_site');

			$sql = "insert into news_site (display,base_url,newsfile,"
					. "lastread,newstype,cachetime,listings) "
					. "values ('" . addslashes($n_display) . "','"
					. addslashes(strtolower($n_base_url)) . "','" 
					. addslashes(strtolower($n_newsfile)) . "',0,'"
					. $n_newstype . "',$n_cachetime,$n_listings)";

			$phpgw->db->query($sql);

			$phpgw->db->unlock();

			$phpgw->redirect($phpgw->link('/headlines/admin.php','cd=28'));
		}
	}

	$phpgw->common->phpgw_header();
	echo parse_navbar();

	// This is done for a reason (jengo)
	$phpgw->template->set_root($phpgw->common->get_tpl_dir('headlines'));

	$phpgw->template->set_file(array(
		'admin_form' => 'admin_form.tpl'
	));
	$phpgw->template->set_block('admin_form','form');

	if (is_array($errors))
	{
		$phpgw->template->set_var('messages',$phpgw->common->error_list($errors));
	}

	$phpgw->template->set_var('title',lang('Headlines admin'));
	$phpgw->template->set_var('lang_header',lang('Create new headline'));
	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('row_on',$phpgw_info['theme']['row_on']);
	$phpgw->template->set_var('row_off',$phpgw_info['theme']['row_off']);
	$phpgw->template->set_var('lang_display',lang('Display'));
	$phpgw->template->set_var('lang_base_url',lang('Base URL'));
	$phpgw->template->set_var('lang_news_file',lang('News File'));
	$phpgw->template->set_var('lang_minutes',lang('Minutes between refresh'));
	$phpgw->template->set_var('lang_listings',lang('Listings Displayed'));
	$phpgw->template->set_var('lang_type',lang('News Type'));
	$phpgw->template->set_var('lang_button',lang('Add'));

	$phpgw->template->set_var('input_display','<input name="n_display" value="' . $n_display . '" size="40">');
	$phpgw->template->set_var('input_base_url','<input name="n_base_url" value="' . $n_base_url . '" size="40">');
	$phpgw->template->set_var('input_news_file','<input name="n_newsfile" value="' . $n_newsfile . '" size="40">');
	$phpgw->template->set_var('input_minutes','<input name="n_cachetime" value="' . $n_cachetime . '" size="4">');
	$phpgw->template->set_var('input_listings','<input name="n_listings" value="' . $n_listings . '" size="2">');

	$news_type = array('rdf','fm','lt','sf','rdf-chan');
	while (list(,$item) = each($news_type))
	{
		$_select .= '<option value="' . $item . '"' . ($n_newstype == $item?' checked':'')
					. '>' . $item . '</option>';
	}
	$phpgw->template->set_var('input_type','<select name="n_newstype">' . $_select . '</select>');

	$phpgw->template->set_var('action_url',$phpgw->link('/headlines/newheadline.php'));

	$phpgw->template->pfp('out','form');
	$phpgw->common->phpgw_footer();
?>
