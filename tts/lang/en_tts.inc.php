<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":	$s = "Trouble Ticket System";	break;
       case "ticket":			$s = "Ticket";		break;
       case "prio":			$s = "Prio";		break;
       case "group":			$s = "Group";		break;
       case "assigned to":		$s = "Assigned to";	break;
       case "assign to":		$s = "Assign to";	break;
       case "opened by":		$s = "Opened by";	break;
       case "date opened":		$s = "Date opened";	break;
       case "subject":			$s = "Subject";		break;
       case "new ticket":		$s = "New ticket";	break;
       case "view all tickets":		$s = "View all tickets";	break;
       case "view only open tickets":	$s = "View only open tickets";	break;
       case "no tickets found":		$s = "No tickets found";	break;
       case "status/date closed":	$s = "Status/Date closed";	break;
       case "add new ticket":		$s = "Add new ticket";	break;
       case "detail":			$s = "Detail";		break;
       case "priority":			$s = "Priority";	break;
       case "add ticket":		$s = "Add ticket";	break;
       case "clear form":		$s = "Clear form";	break;
       case "no subject":		$s = "No subject";	break;
       case "view job detail":		$s = "view job detail";	break;
       case "assigned from":		$s = "Assigned from";	break;
       case "open date":		$s = "Open date";	break;
       case "close date":		$s = "Close date";	break;
       case "details":			$s = "Details";		break;
       case "additional notes":		$s = "Additional notes";	break;
       case "ok":			$s = "OK";		break;
       case "update":			$s = "Update";		break;
       case "close":			$s = "Close";		break;
       case "in progress":		$s = "In progress";	break;
       case "closed":			$s = "Closed";		break;
       case "reopen":			$s = "ReOpen";		break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
