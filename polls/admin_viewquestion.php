<?php
  /**************************************************************************\
  * phpGroupWare - Polls                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'   => 'polls',
		'enable_nextmatchs_class' => True,
		'admin_header' => True
	);
	include('../header.inc.php');

	$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$p->set_file(array('admin' => 'admin_form.tpl'));
	$p->set_block('admin','form','form');
	$p->set_block('admin','row','row');

	$phpgw->db->query("select poll_title from phpgw_polls_desc where poll_id='$poll_id'");
	$phpgw->db->next_record();
	$poll_title = $phpgw->db->f('poll_title');

	$p->set_var('message','');
	$p->set_var('header_message',lang('View poll'));
	$p->set_var('td_message','&nbsp;');
	$p->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$p->set_var('form_action',$phpgw->link('/polls/admin_editquestion.php'));
	$p->set_var('form_button_1','<input type="submit" name="submit" value="' . lang('Edit') . '">');
	$p->set_var('form_button_2','</form><form method="POST" action="'
		. $phpgw->link("/polls/admin_deletequestion.php","poll_id=$poll_id") . '"><input type="submit" name="submit" value="' . lang('Delete') . '">');

	add_template_row($p,lang('Poll question'),$phpgw->strip_html($poll_title));

	$phpgw->db->query("select * from phpgw_polls_data where poll_id='$poll_id'");
	while ($phpgw->db->next_record())
	{
		if (! $title_shown)
		{
			$title = lang('Answers');
			$title_shown = True;
		}
		add_template_row($p,$title,$phpgw->strip_html($phpgw->db->f('option_text')));
		$title = '&nbsp;';
	}

	$p->pparse('out','form');
	$phpgw->common->phpgw_footer();
?>
