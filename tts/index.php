<?php
  /**************************************************************************\
  * phpGroupWare - Trouble Ticket System                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info["flags"]["enable_nextmatchs_class"] = True;
	$phpgw_info["flags"]["currentapp"] = "tts";
	include("../header.inc.php");
?>
 <center><?php echo lang("Trouble Ticket System"); ?>
 <p>
   [ <a href="<?php echo $phpgw->link("/tts/newticket.php") ; ?>"><?php echo lang("New ticket"); ?></a> |

<?php
	// select what tickets to view
	if (!$filter) { $filter="viewopen"; }
	if ($filter == "viewopen") 
	{
		$filtermethod = "where t_timestamp_closed='0'";
	}

	if (!$sort)
	{
		$sortmethod = "order by t_priority desc";
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	$phpgw->db->query("SELECT COUNT(*) FROM ticket");
	$numtotal = $phpgw->db->num_rows();

	$phpgw->db->query("SELECT t_id FROM ticket where t_timestamp_closed='0'");
	$numopen = $phpgw->db->num_rows();

	$phpgw->db->query("select t_id,t_category,t_priority,t_assignedto,t_timestamp_opened,t_user,t_timestamp_closed,t_subject "
		. "from ticket $filtermethod $sortmethod");

	if ($filter == "viewall")
	{
		echo "<a href=\"" . $phpgw->link("/tts/index.php") . "\">" . lang("View only open tickets")."</a>";
	}
	else
	{
		echo "<a href=\"" . $phpgw->link("/tts/index.php","filter=viewall") . "\">" . lang("View all tickets")."</a>";
	}

	echo " ]<br>\n";
	echo "<center>( " . lang("Tickets total x",$numtotal) ." )<br>( " . lang("Tickets open x",$numopen) ." )</center>";

	if ($phpgw->db->num_rows() == 0)
	{
		echo "<p><center>".lang("No tickets found")."</center>";
		$phpgw->common->phpgw_exit(True);
	}

	// Sorting by protrity needs to be added somewhere in here.
?>  
<CENTER>
<BR>
  <TABLE CELLSPACING=1 CELLPADDING=1 BORDER=0>
   <TR bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_id",$order,"/tts/index.php",lang("Ticket")." #"); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_priority",$order,"/tts/index.php",lang("Prio")); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_category",$order,"/tts/index.php",lang("Group")); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_assignedto",$order,"/tts/index.php",lang("Assigned to")); ?>
    </td>

    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_user",$order,"/tts/index.php",lang("Opened by")); ?>
    </td>
    <td align=center>
     <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_opened",$order,"/tts/index.php",lang("Date opened")); ?>
    </td>
    <?php
      if ($filter == "viewall") {
        echo "<td align=center>";
        echo $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_closed",$order,"/tts/index.php",lang("Status/Date closed"));
        echo "</td>";
      }
    ?>
    <td align=center>
      <?php echo $phpgw->nextmatchs->show_sort_order($sort,"t_subject",$order,"/tts/index.php",lang("Subject")); ?>
    </td>
   </TR>

<?php
	// Yes, these are only temp.  I know they are the same as on sourceforge.
	// But, I didn't want to spend too much time fooling with the colors.
	// If someone can come up with some better colors to work with the
	// themes, please, submit them.  
	while ($phpgw->db->next_record())
	{
		$priority=$phpgw->db->f("t_priority");
		switch ($priority)
		{
			case 1:  $tr_color = $phpgw_info["theme"]["bg01"]; break;
			case 2:  $tr_color = $phpgw_info["theme"]["bg02"]; break;
			case 3:  $tr_color = $phpgw_info["theme"]["bg03"]; break;
			case 4:  $tr_color = $phpgw_info["theme"]["bg04"]; break;
			case 5:  $tr_color = $phpgw_info["theme"]["bg05"]; break;
			case 6:  $tr_color = $phpgw_info["theme"]["bg06"]; break;
			case 7:  $tr_color = $phpgw_info["theme"]["bg07"]; break;
			case 8:  $tr_color = $phpgw_info["theme"]["bg08"]; break;
			case 9:  $tr_color = $phpgw_info["theme"]["bg09"]; break;
			case 10: $tr_color = $phpgw_info["theme"]["bg10"]; break;
			default: $tr_color = $phpgw_info["theme"]["bg_color"];
		}
		echo "<tr bgcolor=\"$tr_color\"><TD align=center><a href=\""
			. $phpgw->link("/tts/viewticket_details.php","ticketid=" . $phpgw->db->f("t_id")) . "\">"
			. $phpgw->db->f("t_id") . "</a></TD>";

		$priostr="";
		while ($priority > 0) { $priostr=$priostr . "||"; $priority--; }
		echo "<TD align=left><font size=-2>$priostr</font></TD>";

		$catstr = $phpgw->db->f("t_category")?$phpgw->db->f("t_category"):"none";
		echo "<TD align=center>$catstr</TD>";

		echo "<TD align=center>" . $phpgw->db->f("t_assignedto") . "</TD>";
		echo "<TD align=center>" . $phpgw->db->f("t_user") . "</TD>";
		echo "<TD align=center>" . $phpgw->common->show_date($phpgw->db->f("t_timestamp_opened")) . "</TD>";
		if ( $phpgw->db->f("t_timestamp_closed") > 0 )
		{
			echo "<TD align=center>" . $phpgw->common->show_date($phpgw->db->f("t_timestamp_closed")) . "</TD>";
		}
		elseif ($filter == "viewall")
		{
			if ( $phpgw->db->f("t_assignedto") == "none" )
			{
				echo "<TD align=center>not assigned</TD>";
			}
			else
			{
				echo "<TD align=center>in progress</TD>";
			}
		}
		echo "<td align=center>" . $phpgw->db->f("t_subject") . "</td>";
		echo "</tr>\n";
	}

	echo "</TABLE>\n";
	echo "</CENTER>\n";

	$phpgw->common->phpgw_footer();
?>
