<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":	$s = "Problemen Registratie Systeem";	break;
       case "ticket":			$s = "Melding";		break;
       case "prio":			$s = "Prio";		break;
       case "group":			$s = "Groep";		break;
       case "assigned to":		$s = "Toegewezen aan";	break;
       case "assign to":		$s = "Toewijzen aan";	break;
       case "opened by":		$s = "Aangenomen door";	break;
       case "date opened":		$s = "Aanmaak datum";	break;
       case "subject":			$s = "Onderwerp";		break;
       case "new ticket":		$s = "Nieuwe melding";	break;
       case "view all tickets":		$s = "Geef alle meldingen weer";	break;
       case "view only open tickets":	$s = "Geef alleen openstaande meldingen weer";	break;
       case "no tickets found":		$s = "Geen meldingen gevonden";	break;
       case "status/date closed":	$s = "Status/Datum gesloten";	break;
       case "add new ticket":		$s = "Voeg nieuwe melding toe";	break;
       case "detail":			$s = "Detail";		break;
       case "priority":			$s = "Prioriteit";	break;
       case "add ticket":		$s = "Voeg melding toe";	break;
       case "clear form":		$s = "Formulier wissen";	break;
       case "no subject":		$s = "Geen onderwerp";	break;
       case "view job detail":		$s = "Details werkzaamheden weergeven";	break;
       case "assigned from":		$s = "Toegewezen door";	break;
       case "open date":		$s = "Datum opening";	break;
       case "close date":		$s = "Datum sluiting";	break;
       case "details":			$s = "Details";		break;
       case "additional notes":		$s = "Opmerkingen";	break;
       case "ok":			$s = "OK";		break;
       case "update":			$s = "Bijwerken";		break;
       case "close":			$s = "Sluiten";		break;
       case "in progress":		$s = "In uitvoering";	break;
       case "closed":			$s = "Gesloten";		break;
       case "reopen":			$s = "Heropenen";		break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
