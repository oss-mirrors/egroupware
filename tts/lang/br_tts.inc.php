<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":	$s = "Trouble Ticket System";	break;
       case "ticket":			$s = "Ticket";		break;
       case "prio":			$s = "Prio";		break;
       case "group":			$s = "Grupo";		break;
       case "assigned to":		$s = "Atribuido a";	break;
       case "opened by":		$s = "Aberto por";	break;
       case "date opened":		$s = "Data abertura";	break;
       case "subject":			$s = "Assunto";		break;
       case "new ticket":		$s = "Novo ticket";	break;
       case "view all tickets":		$s = "Visualizar todos tickets";	break;
       case "view only open tickets":	$s = "Ver todos tickets abertos";	break;
       case "no tickets found":		$s = "Nao foram encontrados tickets";	break;
       case "status/date closed":	$s = "Estado/Data fechado";	break;
       case "add new ticket":		$s = "Adicionar novo ticket";	break;
       case "detail":			$s = "Detalhes";		break;
       case "priority":			$s = "Prioridade";	break;
       case "add ticket":		$s = "Criar ticket";	break;
       case "clear form":		$s = "Limpar formulario";	break;
       case "no subject":		$s = "Sem assunto";	break;
       case "view job detail":		$s = "Ver detalhes";	break;
       case "assigned from":		$s = "Atribuido de";	break;
       case "open date":		$s = "Data abertura";	break;
       case "close date":		$s = "Data fechamento";	break;
       case "details":			$s = "Detalhes";		break;
       case "additional notes":		$s = "Notas adicionais";	break;
       case "ok":			$s = "OK";		break;
       case "update":			$s = "Atualize";		break;
       case "close":			$s = "Feche";		break;
       case "in progress":		$s = "Em progresso";	break;
       case "closed":			$s = "Fechado";		break;
       case "reopen":			$s = "Reaberto";		break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
