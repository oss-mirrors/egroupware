<?php
	/**************************************************************************\
	* phpGroupWare - headlines                                                 *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'              => 'headlines',
		'noheader'                => True,
		'nonavbar'                => True,
		'enable_nextmatchs_class' => True
	);
	include('../header.inc.php');

	if (! $submit)
	{
		$phpgw->common->phpgw_header();
		echo parse_navbar();
     
		$phpgw->template->set_file(array('form' => 'preferences.tpl'));
	     
		$phpgw->template->set_var('form_action',$phpgw->link('/headlines/preferences.php'));
		$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
		$phpgw->template->set_var('lang_header',lang('select headline news sites'));
		$phpgw->template->set_var('lang_headlines',lang('Headline preferences'));

		$phpgw->db->query('SELECT con,display FROM phpgw_headlines_sites ORDER BY display asc',__LINE__,__FILE__);
		while ($phpgw->db->next_record())
		{
			$html_select .= '<option value=\'' . $phpgw->db->f('con') . '\'';
//           . $users_headlines[$phpgw->db->f('con')];

			if ($phpgw_info['user']['preferences']['headlines'][$phpgw->db->f('con')])
			{
				$html_select .= ' selected';
			}
			$html_select .= '>' . $phpgw->db->f('display') . "</option>\n";
		}
		$phpgw->template->set_var('select_options',$html_select);

		$phpgw->template->set_var('tr_color_1',$phpgw->nextmatchs->alternate_row_color());
		$phpgw->template->set_var('tr_color_2',$phpgw->nextmatchs->alternate_row_color());

		$phpgw->template->set_var('lang_submit',lang('submit'));

		$phpgw->template->pparse('out','form');
  } else {

		$i = 0;
		while (is_array($phpgw_info['user']['preferences']['headlines']) && $preference = each($phpgw_info['user']['preferences']['headlines']))
		{
			$phpgw->preferences->delete('headlines',$preference[0]);
		}
	
		if (count($headlines))
		{
			while ($value = each($headlines))
			{
				$phpgw->preferences->change('headlines',$value[1],'True');
			}
		}

		$phpgw->preferences->commit(True);

		Header('Location: ' . $phpgw->link('/preferences/index.php'));
	}
	$phpgw->common->phpgw_footer();
?>
