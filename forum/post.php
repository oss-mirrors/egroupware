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

	$phpgw_info['flags'] = array(
		'currentapp' => 'forum',
		'enable_nextmatchs_class' => True
	);

	if($action)
	{
		$phpgw_info['flags']['noheader'] = True;
		$phpgw_info['flags']['nonavbar'] = True;
	}
	include('../header.inc.php');

	if($action == 'post')
	{
		$host = getenv('REMOTE_ADDR');
		if(!$host) getenv('REMOTE_HOST');

		$stat = 0;

		$phpgw->db->query("select max(id) from f_body");
		$phpgw->db->next_record();
		$next_f_body_id = $phpgw->db->f("0") + 1;

		$phpgw->db->query("select max(id) from f_threads");
		$phpgw->db->next_record();
		$next_f_threads_id = $phpgw->db->f("0") + 1;

		//print "$next_f_threads_id <br> $next_f_body_id";

		$dattim = date("Y-m-d H:i:s",time());

		$phpgw->db->query("insert into f_threads (postdate,pos,thread,depth,main,parent,cat_id,for_id,author,subject,email,host,stat) VALUES (
			'$dattim',0,$next_f_body_id,0,$next_f_body_id,-1,$cat,$for,'$author','$subject','$email','$host',$stat)"
		);

		$phpgw->db->query("insert into f_body (cat_id,for_id,message) VALUES (
			$cat,$for,'$message')"
		);

		Header("Location: ". $phpgw->link("/forum/threads.php","cat=".$cat."&for=".$for."&col=".$col));
		$phpgw->common->phpgw_exit();
	}	
?>
<p>
<table border="1" width="100%">
 <tr>
<?php
	$phpgw->db->query("select * from f_categories where id = $cat");
	$phpgw->db->next_record();
	$category = $phpgw->db->f('name');

	$phpgw->db->query("select * from f_forums where id = $for");
	$phpgw->db->next_record();
	$forums = $phpgw->db->f('name');

	$catfor = "cat=" . $cat . "&for=" . $for;

	echo '<td bgcolor="' . $phpgw_info['theme']['th_bg'] . '" align="left"><font size=+1><a href="' . $phpgw->link('/forum/index.php') .'">' . lang('Forums');
	echo '</a> : <a href="' . $phpgw->link('/forum/forums.php','cat=' . $cat) . '">' . $category . '</a> : ';
	echo '<a href="' . $phpgw->link('/forum/threads.php',$catfor . '&col=' . $col) . '">' . $forums . '</a></font></td></tr>';

	echo '<tr>';
	echo '<td align="left" width="50%" valign="top">';

	echo '<font size=-1>';
	echo '[ <a href="' . $phpgw->link('/forum/post.php',$catfor . '&type=new') . '">' . lang('New Topic') . '</a>';
	if(!$col) { echo ' | <a href="' . $phpgw->link('/forum/threads.php',$catfor . '&col=1') . '">' . lang('View Threads') . '</a>'; }
	else { echo ' | <a href="' . $phpgw->link('/forum/threads.php',$catfor . '&col=0') . '">' . lang('Collapse Threads') . '</a>'; }
	# This file doesn't exist yet
	#echo " | <a href=" . $phpgw->link("/forum/search.php","$catfor") . ">" . lang("Search") . "</a>";
	echo " ]\n</font><br><br>\n";

	echo "<center>\n";
	echo "<form method=post action=\"".$phpgw->link("/forum/post.php")."\">\n";
	echo '<input type="hidden" name="cat"  value="' . $cat . '">' . "\n";
	echo '<input type="hidden" name="for"  value="' . $for . '">' . "\n";
	echo '<input type="hidden" name="type" value="' . $type . '">' . "\n";
	if($col) { echo '<input type="hidden" name="col" value="' . $col . '">' . "\n"; }
	echo '<input type="hidden" name="action" value="post">' . "\n";

	echo ' <table border="0" width="80%" bgcolor="' . $phpgw_info['theme']['table_bg'] . '">';

	$name = $phpgw_info['user']['firstname'] . ' ' . $phpgw_info['user']['lastname'];
	$email = $phpgw_info['user']['email_address'];

	echo ' <tr><th colspan="3" bgcolor="' . $phpgw_info['theme']['th_bg'] . '">' . lang('New Topic') . '</th></tr>';
	echo ' <tr><td>' . lang('Your Name') . ':</td><td><input type="text" name="author" size="32" maxlength="49" value="' . $name . '"></td><td></td></tr>';
	echo ' <tr><td>' . lang('Your Email') . ':</td><td><input type="text" name="email" size="32" maxlength="49" value="' . $email . '"></td><td></td></tr>';
	echo ' <tr><td>' . lang('Subject') . ':</td><td><input type="text" name="subject" size="32" maxlength="49"></td><td></td></tr>';
	echo ' <tr><td colspan="3"><center><textarea rows="20" cols="50" name="message"></textarea>';
	echo ' <tr><td colspan="2"><input type="checkbox" name="repmail"> ' . lang('Email replies to this thread, to the address above') . '</td>';
	echo '  <td align="right"><input type="submit" value="' . lang('Submit') . '"></td></tr>';
?>
	</table>
	</center>
  </td>
</table>
<?php
	$phpgw->common->phpgw_footer();
?>
