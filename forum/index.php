<?php
  /**************************************************************************\
  * phpGroupWare - Forums                                                    *
  * http://www.phpgroupware.org                                              *
  * Written by Jani Hirvinen <jpkh@shadownet.com>                            *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	if (! $sessionid) Header("Location: ../login.php");

	$phpgw_info['flags'] = array(
		'currentapp' => 'forum',
		'enable_nextmatchs_class' => True
	);
	include('../header.inc.php');
?>

<p>
<table border="0" width="100%">
 <tr>
<?php echo '<td bgcolor="' . $phpgw_info['theme']['th_bg'] . '" align="left">' . lang('Forums') .'</td></tr>'; ?>
 <tr>
  <td align="left" width="50%" valign="top">
    <center>
    <table border="0" width="80%">
<?php
	//  Pull all the categories from the table f_categories and display them
	$phpgw->db->query("select * from f_categories");

	while($phpgw->db->next_record())
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		echo "<tr bgcolor=".$tr_color."><td><a href="
		. $phpgw->link('/forum/forums.php','cat='
		. $phpgw->db->f('id')) .'>'. $phpgw->db->f('name')
		. '</a></td><td align=left valign=top>'
		. $phpgw->db->f('descr') . "</td></tr>\n";
	}
?>
    </table>
    </center>
  </td>
</table>
<?php
	$phpgw->common->phpgw_footer();
?>
