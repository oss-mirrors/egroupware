<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":	$s = "Trouble Ticket System";	break;
       case "ticket":			$s = "Ticket";		break;
       case "prio":			$s = "Prio";		break;
       case "group":			$s = "Groupe";		break;
       case "assigned to":		$s = "Assign&eacute; &agrave;";	break;
       case "opened by":		$s = "Ouvert par";	break;
       case "date opened":		$s = "Date d'ouverture";	break;
       case "subject":			$s = "Sujet";		break;
       case "new ticket":		$s = "Nouveau ticket";	break;
       case "view all tickets":		$s = "Voir tous les tickets";	break;
       case "view only open tickets":	$s = "Voir uniquement les tickets ouverts";	break;
       case "no tickets found":		$s = "Aucun ticket trouv&eacute;";	break;
       case "status/date closed":	$s = "Etat/Date de fermeture";	break;
       case "add new ticket":		$s = "Ajouter un nouveau ticket";	break;
       case "detail":			$s = "D&eacute;tail";		break;
       case "priority":			$s = "Priorit&eacute;";	break;
       case "add ticket":		$s = "Ajouter un ticket";	break;
       case "clear form":		$s = "Effacer le formulaire";	break;
       case "no subject":		$s = "Pas de sujet";	break;
       case "view job detail":		$s = "voir le d&eacute;tail du travail";	break;
       case "assigned from":		$s = "Assign&eacute; par";	break;
       case "open date":		$s = "Date d'ouverture";	break;
       case "close date":		$s = "Date de fermeture";	break;
       case "details":			$s = "D&eacute;tails";		break;
       case "additional notes":		$s = "Notes suppl&eacute;mentaires";	break;
       case "ok":			$s = "OK";		break;
       case "update":			$s = "Mettre &agrave; jour";		break;
       case "close":			$s = "Fermer";		break;
       case "in progress":		$s = "En cours";	break;
       case "closed":			$s = "Ferm&eacute;";		break;
       case "reopen":			$s = "ReOuvert";		break;

       default: $s = "* ". $message;
    }
    return $s;
  }
