<?php

  function lang_email($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "reply":		$s = "Antworten";	break;
       case "reply all":	$s = "Allen antworten";	break;
       case "forward":		$s = "Weiterleiten";	break;
       case "delete":		$s = "L&ouml;schen";	break;
       case "previous":		$s = "Vorige";		break;
       case "next":		$s = "N&auml;chste";	break;
       case "from":		$s = "Von";		break;
       case "to":		$s = "An";		break;
       case "cc":		$s = "Kopie";		break;
       case "files":		$s = "Dateien";		break;
       case "date":		$s = "Datum";		break;
       case "send":		$s = "Senden";		break;
       case "subject":		$s = "Betreff";		break;
       case "folder":		$s = "Ordner";		break;
       case "size":		$s = "Gr&ouml;&szlig;e";	break;
       case "section":		$s = "Sektion";		break;
       case "image":		$s = "Grafik";		break;
       case "no subject":	$s = "Kein Betreff";	break;
       case "compose":		$s = "Verfassen";	break;
       case "message":		$s = "Nachricht";	break;
       case "messages":		$s = "Nachrichten";	break;
       case "new message":	$s = "Neue Nachricht";	break;
       case "undisclosed sender":	$s = "Verborgener Absender";	break;
       case "undisclosed recipients":	$s = "Verborgene Empf&auml;nger"; break;
       case "please select a message first":	$s = "Bitte w&auml;hlen Sie zuerst eine Nachricht";	break;

       case "this folder is empty":	$s = "Dieser Ordner ist leer";	break;

       case "switch current folder to":	$s = "Wechseln zum Ordner";	break;
       case "move selected messages into":	$s = "Verschiebe ausgew&auml;hlte Nachrichten in";	break;
       case "add to addressbook":	$s = "Zum Addressbuch hinzuf&uuml;gen";	break;

       case "1 message has been deleted":
	$s = "1 Nachricht wurde gel&ouml;scht";		break;

       case "x messages have been deleted":
	$s = "$m1 Nachrichten wurden gel&ouml;scht";		break;

       case "your mail has been sent successfully":
	$s = "Ihre Nachricht wurde erfolgreich versendet.";	break;

       case "monitor":		$s = "Monitor";		break;
       default: 		$s = "* " . $message;
    }
    return $s;
  }

?>
