<?php
  $phpgw_info["flags"] = array("currentapp" => "polls","admin_header" => True);
  include("../header.inc.php");

  echo '<table border="0" align="center">';
  echo "<tr><td>" . lang("Title") . "</td> <td>" . lang("Date") . "</td></tr>";
  $phpgw->db->query("select * from phpgw_polls_desc");
  while ($phpgw->db->next_record()) {
    echo "<tr><td>" . $phpgw->db->f("poll_title") . "</td> <td>" . $phpgw->common->show_date($phpgw->db->f("poll_timestamp")) . "</td></tr>";

  }
  echo "</table>";
  echo '<p><a href="' . $phpgw->link("admin_add.php") . '">' . lang("Add") . '</a>';



