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

	if ($submit)
	{
		$phpgw->db->query("insert into phpgw_polls_desc (poll_title,poll_timestamp) values ('"
			. addslashes($question) . "','" . time() . "')",__LINE__,__FILE__);
		$p->set_var("message",lang("New poll has been added.  You should now add some answers for this poll"));
	}
	else
	{
		$p->set_var('message','');
	}

	$p->set_var('header_message',lang('Add new poll question'));
	$p->set_var('td_message',"&nbsp;");
	$p->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$p->set_var('form_action',$phpgw->link('/polls/admin_addquestion.php'));
	$p->set_var('form_button_1','<input type="submit" name="submit" value="' . lang('Add') . '">');
	$p->set_var('form_button_2','</form><form method="POST" action="' . $phpgw->link('/polls/admin.php') . '"><input type="submit" name="submit" value="' . lang('Cancel') . '">');

	add_template_row($p,lang("Enter poll question"),'<input name="question">');

	$p->pparse('out','form');
	$phpgw->common->phpgw_footer();
?>
