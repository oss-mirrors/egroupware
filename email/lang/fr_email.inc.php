<?php

  function lang_email($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "reply":		$s = "R&eacute;pondre";		break;
       case "reply all":	$s = "R&eacute;pondre &agrave; tous";	break;
       case "forward":		$s = "Transf&eacute;rer";		break;
       case "delete":		$s = "Supprimer";		break;
       case "previous":		$s = "Pr&eacute;c&eacute;dent";	break;
       case "next":		$s = "Suivant";		break;
       case "from":		$s = "De";		break;
       case "to":		$s = "A";		break;
       case "cc":		$s = "CC";		break;
       case "files":		$s = "Fichiers";	break;
       case "date":		$s = "Date";		break;
       case "send":		$s = "Envoyer";		break;
       case "subject":		$s = "Sujet";		break;
       case "folder":		$s = "Dossier";		break;
       case "size":		$s = "Taille";		break;
       case "section":		$s = "Section";		break;
       case "image":		$s = "Image";		break;
       case "no subject":	$s = "Pas De Sujet";	break;
       case "compose":		$s = "Composer";	break;
       case "message":		$s = "Message";		break;
       case "messages":		$s = "Messages";	break;
       case "new message":	$s = "Nouveau message";	break;
       case "undisclosed sender":	$s = "Undisclosed Sender";	break;
       case "undisclosed recipients":	$s = "Undisclosed Recipients";	break;
       case "please select a message first":	$s = "Veuillez d'abord selectionner un message";	break;

       case "this folder is empty":	$s = "Ce dossier est vide";	break;

       case "switch current folder to":	$s = "Changer Le Dossier Courant Vers";	break;
       case "move selected messages into":	$s = "D&eacute;placer Les Messages Selectionn&eacute;s Dans";	break;
       case "add to addressbook":	$s = "Ajouter au carnet d'adresses";	break;
       case "monitor":		$s = "Monitorer";		break;
       default: 		$s = "* " . $message;
    }
    return $s;
  }

?>
