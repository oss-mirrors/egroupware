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

  $phpgw_info["flags"]["currentapp"] = "forum";
  include("../header.inc.php");

?>

<p>
<table border="0" width="100%">
 <tr>
<?   echo '<td bgcolor="' . $phpgw_info["theme"]["th_bg"] . '" align="left">' . lang("Forums") .'</td>' . '</tr>'; ?>
 <tr>
  <td align="left" width="50%" valign="top">
   <?php
 if($phpgw_info["user"]["app_perms"][1]) 
  echo "<font size=-1><a href=" . $phpgw->link("admin/") . ">" . lang("Admin") . "</a></font>"; 

    echo "<center>";
    echo '<table border="0" width="80%">';

//  Pull all the categories from the table f_categories and display them
	$phpgw->db->query("select * from f_categories");
	while($phpgw->db->next_record()) {
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		echo "<tr bgcolor=".$tr_color."><td><a href=" . $phpgw->link("forums.php","cat=" . $phpgw->db->f("id")) .">". $phpgw->db->f("name") . "</a></td><td align=left valign=top>" . $phpgw->db->f("descr") . "</td></tr>\n";
	}

     echo "</table>";
     echo "</center>";
   ?>
  </td>
</table>

<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
