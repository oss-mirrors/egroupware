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

	if ($confirm)
	{
		$phpgw->db->query("delete from phpgw_polls_data where vote_id='$vote_id'");
	}
	else
	{
		echo '<p><br><table border="0" width="40%" align="center"><tr><td align="center" colspan="center">';
		echo lang('Are you sure want to delete this answer ?') . '</td></tr>';
		echo '<tr><td align="left"><a href="' . $phpgw->link('/polls/admin.php','show=answers') . '">' . lang('No') . '</td>';
		echo '    <td align="right"><a href="' . $phpgw->link('/polls/admin_deleteanswer.php','vote_id='
			. $vote_id . '&confirm=True') . '">' . lang('Yes') . '</td></tr>';
		echo '</table>';
	}
	$phpgw->common->phpgw_footer();
?>
