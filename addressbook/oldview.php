<?php
  /**************************************************************************\
  * phpGroupWare - addressbook                                               *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if ($submit || ! $ab_id) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "addressbook";
  $phpgw_info["flags"]["enable_addressbook_class"] = True;
  include("../header.inc.php");

  function checkfor_specialformat($field,$data)
  {
     global $phpgw_info, $phpgw;

     if ($field == "email") {
        if ($phpgw_info["user"]["apps"]["email"]) {
           $s = '<a href="' . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/email/compose.php",
                                           "to=" . urlencode($data)) . '" target="_new">' . $data . '</a>';
        } else {
           $s = '<a href="mailto:' . $data . '">' . $data . '</a>';
        }
     } else if ($field == "URL") {
        if (! ereg("^http://",$data)) {
           $data = "http://" . $data;
        }
        $s = '<a href="' . $data . '" target="_new">' . $data . '</a>';
     } else if ($field == "birthday") {
        $date = explode("/",$data);
        $s = $phpgw->common->dateformatorder($date[2],$date[1],$date[0],True);
     } else {
        $s = $data . "&nbsp;";
     }     
     return $s;  
  }

  if (! $ab_id) {
    Header("Location: " . $phpgw->link("index.php"));
  }

  if ($filter != "private") {
     $filtermethod = " or ab_access='public' " . $phpgw->accounts->sql_search("ab_access");
  }

  if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
     $phpgw->db->query("SELECT * FROM addressbook as a, customers as c WHERE a.ab_company_id = c.company_id "
		             . "AND ab_id=$ab_id AND (ab_owner='"
	                 . $phpgw_info["user"]["account_id"] . "' $filtermethod)");
  } else {
     $phpgw->db->query("SELECT * FROM addressbook WHERE ab_id=$ab_id AND (ab_owner='"
                     . $phpgw_info["user"]["account_id"] . "' $filtermethod)");
  }
  $phpgw->db->next_record();

  echo "<p>&nbsp;<b>" . lang("Address book - view") . "</b><hr><p>";

  $i = 0;
  while ($column = each($abc)) {
     if ($phpgw->db->f("ab_" . $column[0])) {
        $columns_to_display[$i]["field_name"]  = $column[1];
        $columns_to_display[$i]["field_value"] = $phpgw->db->f("ab_" . $column[0]);
        $i++;
     }
  }
  
  if ($phpgw->db->f("ab_notes")) {
     $columns_to_display[$i]["field_name"]  = "Notes";
     $columns_to_display[$i]["field_value"] = $phpgw->db->f("ab_notes");
  }

  echo '<table border="0" cellspacing="2" cellpadding="2" width="80%" align="center">';
  for ($i=0;$i<200;) {		// The $i<200 is only used for a brake
      if (! $columns_to_display[$i]["field_name"]) break;

      $columns_html .= "<tr><td><b>" . lang($columns_to_display[$i]["field_name"]) . "</b>:</td>"
                     . "<td>" . checkfor_specialformat($columns_to_display[$i]["field_name"],$columns_to_display[$i]["field_value"])
                     . "</td>";

      $i++;

      if (! $columns_to_display[$i]["field_name"]) break;

      $columns_html .= "<tr><td><b>" . lang($columns_to_display[$i]["field_name"]) . "</b>:</td>"
                     . "<td>" . checkfor_specialformat($columns_to_display[$i]["field_name"],$columns_to_display[$i]["field_value"])
                     . "</td>";

      $i++;
	  $columns_html .= "</td></tr>";
  }
  $access = $phpgw->db->f("ab_access");
  $owner  = $phpgw->db->f("ab_owner");
  $ab_id  = $phpgw->db->f("ab_id");

  echo $columns_html . '<tr><td colspan="4">&nbsp;</td></tr>';
  echo "<tr><td><b>" . lang("Record owner") . "</b></td><td>"
     . $phpgw->common->grab_owner_name($owner) . "</td><td><b>"
     . lang("Record Access") . "</b></td><td>";
     
  if ($access != "private" && $access != "public") {
	 echo lang("Group access") . " - " . $phpgw->accounts->convert_string_to_names_access($access);
  } else {
     echo $access;
  }
     
  echo "</td></tr></table>";
?>

 <TABLE border="0" cellpadding="1" cellspacing="1">
  <TR> 
   <TD align="left">
    <?php
      echo $phpgw->common->check_owner($owner,"edit.php","Edit","ab_id=$ab_id");
    ?>
   </TD>
   <TD align=left>
    <form action="<?php echo $phpgw->link("vcardout.php","ab_id=$ab_id&order=$order&start=$start&filter=$filter&query=$query&sort=$sort"); ?>" method="post" name="Vcardform">
     <input type="submit" value="Vcard">
    </form>
   </TD>
   <TD align="left">
    <form action="<?php echo $phpgw->link("index.php","order=$order&start=$start&filter=$filter&query=$query&sort=$sort"); ?>" method="post" name="Doneform">
     <input type="submit" value="Done">
    </form>
   </TD>
  </TR>
 </TABLE>

<?php
  $phpgw->common->phpgw_footer();
?>
