<?php



  function lang_email($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")

  {

    $message = strtolower($message);



    switch($message)

    {

       case "reply":    $s = "Responder";    break;

       case "reply all":  $s = "Responder a Todos";  break;

       case "forward":    $s = "Reenviar";    break;

       case "delete":    $s = "Borrar";    break;

       case "previous":    $s = "Previo";  break;

       case "next":      $s = "Proximo";    break;

       case "from":      $s = "De";    break;

       case "to":      $s = "Para";      break;

       case "cc":      $s = "CC";      break;

       case "files":    $s = "Archivos";    break;

       case "date":      $s = "Fecha";    break;

       case "send":      $s = "Enviar";    break;

       case "subject":    $s = "Asunto";    break;

       case "folder":    $s = "Carpeta";    break;

       case "size":      $s = "Tamaño";    break;

       case "section":    $s = "Sección";    break;

       case "image":    $s = "Imagen";    break;

       case "no subject":  $s = "Sin Asunto";  break;

       case "compose":    $s = "Componer";    break;

       case "message":    $s = "Mensaje";    break;

       case "messages":    $s = "Mensajes";  break;

       case "new message":  $s = "Nuevo mensaje";  break;

       case "undisclosed sender":  $s = "Undisclosed Sender";  break;

       case "undisclosed recipients":  $s = "Undisclosed Recipients";  break;

       case "please select a message first":  $s = "Por favor seleccione un mensaje primero";  break;



       case "this folder is empty":  $s = "Esta carpeta esta vacia";  break;



       case "switch current folder to":  $s = "Cambiar la presente carpeta a";  break;

       case "move selected messages into":  $s = "Mover los mensajes seleccionados a";  break;

       case "add to addressbook":  $s = "Agregar a la libreta de direcciones";  break;



       case "1 message has been deleted":

  $s = "1 mensaje fue borrado";    break;



       case "x messages have been deleted":

  $s = "$m1 mensajes han sido borrados";    break;





       case "monitor":    $s = "Monitor";    break;



       default: $s = "<b>*</b> ". $message;

    }

    return $s;

  }



?>