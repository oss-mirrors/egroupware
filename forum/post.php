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

  if($action) $phpgw_flags = array("noheader" => True, "nonavbar" => True);

  $phpgw_flags["currentapp"] = "forum";
  include("../header.inc.php");


if($action == "post") {

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
	'$dattim',
	0,
	$next_f_body_id,
	0,
	$next_f_body_id,
	-1,
	$cat,
	$for,
	'$author',
	'$subject',
	'$email',
	'$host',
	$stat)");

  $phpgw->db->query("insert into f_body (cat_id,for_id,message) VALUES (
	$cat,
	$for,
	'$message')");
	

  Header("Location: ". $phpgw->link("threads.php","cat=".$cat."&for=".$for."&col=".$col));
  exit;

}




?>

<p>
<table border="1" width="100%">
 <tr>
<? 
 $phpgw->db->query("select * from f_categories where id = $cat");
 $phpgw->db->next_record();
 $category = $phpgw->db->f("name");

 $phpgw->db->query("select * from f_forums where id = $for");
 $phpgw->db->next_record();
 $forums = $phpgw->db->f("name");

 $catfor = "cat=" . $cat . "&for=" . $for;

 echo '<td bgcolor="' . $phpgw_info["theme"]["th_bg"] . '" align="left"><font size=+1><a href=' . $phpgw->link("index.php") .'>' . lang_forums("Forums") ;
 echo '</a> : <a href=' . $phpgw->link("forums.php","cat=" . $cat) . '>' . $category . '</a> : ';
 echo "<a href=" . $phpgw->link("threads.php","$catfor&col=" . $col) . ">". $forums . "</a></font></td></tr>";


 echo "<tr>";
 echo '<td align="left" width="50%" valign="top">';

 echo "<font size=-1>";
 echo "[ <a href=" . $phpgw->link("post.php","$catfor&type=new") . ">" . lang_forums("New Topic") . "</a> | ";
 if(!$col) echo "<a href=" . $phpgw->link("threads.php","$catfor&col=1") . ">" . lang_forums("View Threads") . "</a> | ";
 if($col) echo "<a href=" . $phpgw->link("threads.php","$catfor&col=0") . ">" . lang_forums("Collapse Threads") . "</a> | ";
 echo "<a href=" . $phpgw->link("search.php","$catfor") . ">" . lang_forums("Search") . "</a> ]\n";
 echo "</font><br><br>\n";


 echo "<center>\n";
 echo "<form method=post action=\"post.php\">\n";
 echo $phpgw->session->hidden_var();
 echo "<input type=\"hidden\" name=\"cat\" value=\"$cat\">\n";
 echo "<input type=\"hidden\" name=\"for\" value=\"$for\">\n";
 echo "<input type=\"hidden\" name=\"type\" value=\"$type\">\n";
 if($col) echo "<input type=\"hidden\" name=\"col\" value=\"$col\">\n";
 echo "<input type=\"hidden\" name=\"action\" value=\"post\">\n";

 echo ' <table border="0" width="80%" bgcolor=' . $phpgw_info["theme"]["table_bg"] . '>';

 $name = $phpgw->session->firstname . " " . $phpgw->session->lastname;
 $email = $phpgw_info["user"]["email_address"];

 echo " <tr><th colspan=3 bgcolor=" . $phpgw_info["theme"]["th_bg"] . ">" . lang_forums("New Topic") . "</th></tr>";
 echo " <tr><td>" . lang_forums("Your Name") . ":</td><td><input type=text name=author size=32 maxlength=49 value=\"$name\"></td><td></td></tr>";
 echo " <tr><td>" . lang_forums("Your Email") . ":</td><td><input type=text name=email size=32 maxlength=49 value=\"$email\"></td><td></td></tr>";
 echo " <tr><td>" . lang_forums("Subject") . ":</td><td><input type=text name=subject size=32 maxlength=49></td><td></td></tr>";
 echo " <tr><td colspan=3><center><textarea rows=20 cols=50 name=message></textarea>";
 echo " <tr><td colspan=2><input type=checkbox name=repmail> " . lang_forums("Email replies to this thread, to the address above") . "</td>";
 echo "  <td align=right><input type=submit value=" . lang_forums("Submit") . "></td></tr>";


 echo "</table>";
 echo "</center>";
   ?>
  </td>
</table>


<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");



