<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":	$s = "Trouble Ticket System";	break;
       case "ticket":			$s = "Ticket";		break;
       case "prio":			$s = "Prio";		break;
       case "group":			$s = "Gruppe";		break;
       case "assigned to":		$s = "Zugewiesen an";	break;
       case "assign to":		$s = "Zuweisen an";	break;
       case "opened by":		$s = "Angelegt von";	break;
       case "date opened":		$s = "Angelegt am";	break;
       case "subject":			$s = "Betreff";		break;
       case "new ticket":		$s = "Neues Ticket";	break;
       case "view all tickets":		$s = "Alle Tickets anzeigen";	break;
       case "view only open tickets":	$s = "Nur offene Tickets anzeigen";	break;
       case "no tickets found":		$s = "Keine Tickets gefunden";	break;
       case "status/date closed":	$s = "Status/Schlie&szlig;datum";	break;
       case "add new ticket":		$s = "Neues Ticket hinzuf&uuml;gen";	break;
       case "detail":			$s = "Detail";		break;
       case "priority":			$s = "Priorit&auml;t";	break;
       case "add ticket":		$s = "Ticket hinzuf&uuml;gen";	break;
       case "clear form":		$s = "Eingabe l&ouml;schen";	break;
       case "no subject":		$s = "Kein Betreff";		break;
       case "view job detail":		$s = "Details anzeigen";	break;
       case "assigned from":		$s = "Zugewiesen von";	break;
       case "open date":		$s = "Angelegt am";	break;
       case "close date":		$s = "Geschlossen am";	break;
       case "details":			$s = "Details";		break;
       case "additional notes":		$s = "Zus&auml;tzliche Notizen";	break;
       case "ok":			$s = "OK";		break;
       case "update":			$s = "Aktualisieren";	break;
       case "close":			$s = "Schlie&szlig;en";	break;
       case "in progress":		$s = "In Bearbeitung";	break;
       case "closed":			$s = "Geschlossen";	break;
       case "reopen":			$s = "Wieder &ouml;ffnen";	break;

       default: $s = "* ". $message;
    }
    return $s;
  }
