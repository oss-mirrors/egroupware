<?php

  function lang_email($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "reply":		$s = "Antwoorden";		break;
       case "reply all":	$s = "Allen antwoorden";	break;
       case "forward":		$s = "Doorsturen";		break;
       case "delete":		$s = "Verwijderen";		break;
       case "previous":		$s = "Vorige";	break;
       case "next":			$s = "Volgende";		break;
       case "from":			$s = "Van";		break;
       case "to":			$s = "Aan";			break;
       case "cc":			$s = "CC";			break;
       case "files":		$s = "Bestanden";		break;
       case "date":			$s = "Datum";		break;
       case "send":			$s = "Verzenden";		break;
       case "subject":		$s = "Onderwerp";		break;
       case "folder":		$s = "Folder";		break;
       case "size":			$s = "Grootte";		break;
       case "section":		$s = "Sectie";		break;
       case "image":		$s = "Afbeelding";		break;
       case "no subject":	$s = "Geen onderwerp";	break;
       case "compose":		$s = "Opstellen";		break;
       case "message":		$s = "Bericht";		break;
       case "messages":		$s = "Berichten";	break;
       case "new message":	$s = "Nieuw bericht";	break;
       case "undisclosed sender":	$s = "Onbekende afzender";	break;
       case "undisclosed recipients":	$s = "Onbekende ontvangers";	break;
       case "please select a message first":	$s = "Selecteer eerst een bericht";	break;

       case "this folder is empty":	$s = "Deze folder is leeg";	break;

       case "switch current folder to":	$s = "Verander van Huidige Folder Naar";	break;
       case "move selected messages into":	$s = "Verplaats de geselecteerde berichten naar";	break;
       case "add to addressbook":	$s = "Voeg toe aan adresboek";	break;

       case "1 message has been deleted":
	$s = "1 bericht is verwijderd";		break;

       case "x messages have been deleted":
	$s = "$m1 berichten zijn verwijderd";		break;

       case "your mail has been sent successfully":
	$s = "Uw bericht is succesvol verzonden.";	break;

       case "monitor":		$s = "Monitor";		break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }

?>
