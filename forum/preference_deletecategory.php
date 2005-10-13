<?php
	/*****************************************************************************\
	* eGroupWare - Forums                                                       *
	* http://www.egroupware.org                                                 *
	* Written by Jani Hirvinen <jpkh@shadownet.com>                               *
	* -------------------------------------------                                 *
	*  This program is free software; you	can redistribute it and/or modify it   *
	*  under the terms of	the GNU	General	Public License as published by the  *
	*  Free Software Foundation; either version 2	of the License,	or (at your *
	*  option) any later version.                                                 *
	\*****************************************************************************/

	/* $Id$ */

	$GLOBALS['egw_info']["flags"] = array("currentapp" => "forum", "enable_nextmatchs_class" => True);
	if($confirm || ! $cat_id) {
		$GLOBALS['egw_info']["flags"]["noheader"] = True;
		$GLOBALS['egw_info']["flags"]["nonavbar"] = True;
	}
	include("../header.inc.php");

	if (($cat_id) && (! $confirm)) {
		?>
		 <center>
			<table border=0 width=65%>
			 <tr colspan=2>
				<td align=center>
				 <?php echo lang("Are you sure you want to delete this category?"); ?>
				<td>
			 </tr>
			 <tr colspan=2>
				<td align=center>
				 <?php echo "<font color=\"red\"><blink>".lang("All forums, user posts, and topics in this category will be lost!")."</blink></font>"; ?>
				</td>
			 </tr>
			 <tr>
				 <td>
					 <a href="<?php echo $GLOBALS['egw']->link("/forum") . "\">" . lang("No"); ?></a>
				 </td>
				 <td>
					 <a href="<?php echo $GLOBALS['egw']->link("/forum/preference_deletecategory.php","cat_id=$cat_id&confirm=true") . "\">" . lang("Yes"); ?></a>
				 </td>
			 </tr>
			</table>
		 </center>
		<?php
		$GLOBALS['egw']->common->egw_footer();
	}

	if (($cat_id) && ($confirm)) {
		$GLOBALS['egw']->db->query("delete from f_threads where cat_id=$cat_id");
		$GLOBALS['egw']->db->query("delete from f_body where cat_id=$cat_id");
		$GLOBALS['egw']->db->query("delete from f_forums where cat_id=$cat_id");
		$GLOBALS['egw']->db->query("delete from f_categories where id=$cat_id");

		Header("Location: " . $GLOBALS['egw']->link("/forum"));
	}
?>
