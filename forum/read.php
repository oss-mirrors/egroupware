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


if($action == "reply") {

 $host = getenv('REMOTE_ADDR');
 if(!$host) getenv('REMOTE_HOST');

 $stat = 0;

 $phpgw->db->query("select max(id) from f_body");
 $phpgw->db->next_record();
 $next_f_body_id = $phpgw->db->f("0") + 1;

 $phpgw->db->query("select max(id) from f_threads");
 $phpgw->db->next_record();
 $next_f_threads_id = $phpgw->db->f("0") + 1;

$dattim = date("Y-m-d H:i:s",time());

if($pos != 0) {
 $tmp = $phpgw->db->query("select id,pos from f_threads where thread = $thread and pos >= $pos order by pos desc");
 while($phpgw->db->next_record($tmp)) {
	$oldpos = $phpgw->db->f("pos") + 1;
	$oldid = $phpgw->db->f("id");
	//print "$oldid $oldpos<br>";
	$phpgw->db->query("update f_threads set pos=$oldpos where thread = $thread and id = $oldid");
 }
} else $pos = 1;


 $phpgw->db->query("insert into f_threads (postdate,pos,thread,depth,main,parent,cat_id,for_id,author,subject,email,host,stat) VALUES (
	'$dattim',
	$pos,
	$thread,
	$depth,
	$next_f_body_id,
	$msg,
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
<table border="0" width="100%">
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

 include("./inc/bar.inc.php");

 echo "<center>\n";
 echo "<form method=post action=\"read.php\">\n";
 echo $phpgw->session->hidden_var();
 echo "<input type=\"hidden\" name=\"cat\" value=\"$cat\">\n";
 echo "<input type=\"hidden\" name=\"for\" value=\"$for\">\n";
 echo "<input type=\"hidden\" name=\"type\" value=\"$type\">\n";
 echo "<input type=\"hidden\" name=\"msg\" value=\"$msg\">\n";
 echo "<input type=\"hidden\" name=\"pos\" value=\"$pos\">\n";
 if($col) echo "<input type=\"hidden\" name=\"col\" value=\"$col\">\n";
 echo "<input type=\"hidden\" name=\"action\" value=\"reply\">\n";

 echo ' <table border="0" width="80%" bgcolor=' . $phpgw_info["theme"]["table_bg"] . '>';
 $phpgw->db->query("select * from f_threads where id = $msg");
 $phpgw->db->next_record();
 $thread = $phpgw->db->f("thread");
 $depth = $phpgw->db->f("depth") + 1;
 echo "<input type=\"hidden\" name=\"thread\" value=\"$thread\">\n";
 echo "<input type=\"hidden\" name=\"depth\" value=\"$depth\">\n";

 echo "<tr><td>" . lang_forums("Author") .": </td><td>" . $phpgw->db->f("author") . "</td></tr>";
 echo "<tr><td>" . lang_forums("Date") .": </td><td>" . $phpgw->db->f("postdate") . "</td></tr>";
 echo "<tr><td>" . lang_forums("Subject") .": </td><td>" . $phpgw->db->f("subject") . "</td></tr>";
 $msgid = $phpgw->db->f("main");
 $subj = "Re: " . $phpgw->db->f("subject");


 $phpgw->db->query("select * from f_body where id = $msgid");
 $phpgw->db->next_record();

 echo "<tr><td colspan=2><br>" . $phpgw->db->f("message") . "</td></tr>";
 echo "</table>";
 echo "<br>";

 echo "<table border=0 width=80%>";
  showthread($thread,$msg);
 echo "</table><br>";

 echo ' <table border="0" width="80%" bgcolor=' . $phpgw_info["theme"]["table_bg"] . '>';

 $name = $phpgw->session->firstname . " " . $phpgw->session->lastname;
 $email = $phpgw_info["user"]["email_address"];

 echo " <tr><th colspan=3 bgcolor=" . $phpgw_info["theme"]["th_bg"] . ">" . lang_forums("Reply to this message") . "</th></tr>";
 echo " <tr><td>" . lang_forums("Your Name") . ":</td><td><input type=text name=author size=32 maxlength=49 value=\"$name\"></td><td></td></tr>";
 echo " <tr><td>" . lang_forums("Your Email") . ":</td><td><input type=text name=email size=32 maxlength=49 value=\"$email\"></td><td></td></tr>";
 echo " <tr><td>" . lang_forums("Subject") . ":</td><td><input type=text name=subject size=32 maxlength=49 value=\"$subj\"></td><td></td></tr>";
 echo " <tr><td colspan=3><center><textarea rows=20 cols=50 name=message></textarea>";
 echo " <tr><td colspan=2><input type=checkbox name=repmail> " . lang_forums("Email replies to this thread, to the address above") . "</td>";
 echo "  <td align=right><input type=submit value=" . lang_forums("Reply") . "></td></tr>";


 echo "</table>";
 echo "</center>";
   ?>
  </td>
</table>


<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");



