<?php

  $phpgw_flags["currentapp"] = "tts";
  include("../header.inc.php");
// INCLUDE CONFIGURATION FILES
include("config/html.conf.php3");

?>
 <center><?php echo lang_tts("Trouble Ticket System"); ?>
 <p>
   [ <a href="<?php echo $phpgw->link("newticket.php") ; ?>"><?php echo lang_tts("New ticket"); ?></a> |

<?php
// ******* 

  // select what tickets to view
  // needs to be fixed.  What is $database for ???

  if (! $filter) { $filter="viewopen"; }
  if ($filter == "viewopen") 
     $filtermethod = "where t_timestamp_closed=NULL";

  if (! $sort)
     $sortmethod = "order by t_priority desc";
  else
     $sortmethod = "order by $order $sort";

  $phpgw->db->query("select t_id,t_category,t_priority,t_assignedto,t_timestamp_opened,t_user,t_timestamp_closed,t_subject "
	      . "from ticket $filtermethod $sortmethod");


  if ($filter == "viewall") {
     echo "<a href=\"" . $phpgw->link() . "\">".lang_tts("View only open tickets")."</a>";
  } else {
     echo "<a href=\"" . $phpgw->link("index.php","filter=viewall") . "\">"
	. lang_tts("View all tickets")."</a>";
  }
  echo " ]<p>\n";

  if ($phpgw->db->num_rows() == 0) {
     echo "<p><center>".lang_tts("No tickets found")."</center>";
     exit;
  }

  // Sorting by protrity needs to be added somewhere in here.
?>  
<CENTER>
<BR>
  <TABLE CELLSPACING=1 CELLPADDING=1 BORDER=0>
   <TR bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_id",$order,"index.php",lang_tts("Ticket")." #"); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_priority",$order,"index.php",lang_tts("Prio")); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_category",$order,"index.php",lang_tts("Group")); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_assignedto",$order,"index.php",lang_tts("Assigned to")); ?>
    </td>

    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_user",$order,"index.php",lang_tts("Opened by")); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_opened",$order,"index.php",lang_tts("Date opened")); ?>
    </td>
    <?php
      if ($filter == "viewall") {
        echo "<td align=center>";
        echo $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_closed",$order,"index.php",lang_tts("Status/Date closed"));
        echo "</td>";
      }
    ?>
    <td align=center>
      <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_subject",$order,"index.php",lang_tts("Subject")); ?>
    </td>
   </TR>

<?php
  // Yes are only temp.  I know they are the same as on sourecforge.
  // But, I didn't want to spend too much time fooling with the colors.
  // If someone can come up with some better colors to work with the
  // themes, please, submit them.  
  while ($phpgw->db->next_record()) {
    $priority=$phpgw->db->f("t_priority");
    switch ($priority)
    {
       case 1:		$tr_color = $phpgw_info["theme"]["bg01"];	break;
       case 2:		$tr_color = $phpgw_info["theme"]["bg02"];	break;
       case 3:		$tr_color = $phpgw_info["theme"]["bg03"];	break;
       case 4:		$tr_color = $phpgw_info["theme"]["bg04"];	break;
       case 5:		$tr_color = $phpgw_info["theme"]["bg05"];	break;
       case 6:		$tr_color = $phpgw_info["theme"]["bg06"];	break;
       case 7:		$tr_color = $phpgw_info["theme"]["bg07"];	break;
       case 8:		$tr_color = $phpgw_info["theme"]["bg08"];	break;
       case 9:		$tr_color = $phpgw_info["theme"]["bg09"];	break;
       case 10:		$tr_color = $phpgw_info["theme"]["bg10"];	break;
       default:		$tr_color = $phpgw_info["theme"]["bg_color"];
    }
    echo "<tr bgcolor=\"$tr_color\"><TD align=center><a href=\""
	. $phpgw->link("viewticket_details.php","ticketid=" . $phpgw->db->f("t_id")) . "\">"
	. $phpgw->db->f("t_id") . "</a></TD>";


    $priostr="";
    while ($priority > 0) { $priostr=$priostr . "||"; $priority--; }
    echo "<TD align=left><font size=-2>$priostr</font></TD>";

    $catstr = $phpgw->db->f("t_category")?$phpgw->db->f("t_category"):"none";
    echo "<TD align=center>$catstr</TD>";

    echo "<TD align=center>" . $phpgw->db->f("t_assignedto") . "</TD>";
    echo "<TD align=center>" . $phpgw->db->f("t_user") . "</TD>";
    echo "<TD align=center>" . $phpgw->common->show_date($phpgw->db->f("t_timestamp_opened")) . "</TD>";
    if ( $phpgw->db->f("t_timestamp_closed") > 0 )  {
      echo "<TD align=center>" . $phpgw->common->show_date($phpgw->db->f("t_timestamp_closed")) . "</TD>";
    } elseif ($filter == "viewall") {
      if ( $phpgw->db->f("t_assignedto") == "none" ) {
        echo "<TD align=center>not assigned</TD>";
      }
      else {
        echo "<TD align=center>in progress</TD>";
      }
    }
    echo "<td align=center>" . $phpgw->db->f("t_subject") . "</td>";
    echo "</tr>\n";
  }
  
  echo "</TABLE>\n";
  echo "</CENTER>\n";

  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");