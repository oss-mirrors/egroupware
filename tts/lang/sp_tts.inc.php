<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":  $s = "Trouble Ticket System";  break;
       case "ticket":      $s = "Ticket";    break;
       case "prio":      $s = "Prioridad";    break;
       case "group":      $s = "Groupo";    break;
       case "assigned to":    $s = "Asignado a";  break;
       case "assign to":    $s = "Asignar a";  break;
       case "opened by":    $s = "Abierto por";  break;
       case "date opened":    $s = "Fecha apertura";  break;
       case "subject":      $s = "Asunto";    break;
       case "new ticket":    $s = "Nuevo ticket";  break;
       case "view all tickets":    $s = "Ver todos los tickets";  break;
       case "view only open tickets":  $s = "Ver solo los tickets abiertos";  break;
       case "no tickets found":    $s = "No se encontraron tickets";  break;
       case "status/date closed":  $s = "Estado/Fecha cierre";  break;
       case "add new ticket":    $s = "Agregar nuevo ticket";  break;
       case "detail":      $s = "Detalle";    break;
       case "priority":      $s = "Prioridad";  break;
       case "add ticket":    $s = "Agregar ticket";  break;
       case "clear form":    $s = "Limpiar formulario";  break;
       case "no subject":    $s = "Sin asunto";  break;
       case "view job detail":    $s = "ver detalle del trabajo";  break;
       case "assigned from":    $s = "Asignado desde";  break;
       case "open date":    $s = "Fecha apertura";  break;
       case "close date":    $s = "Fecha cierre";  break;
       case "details":      $s = "Detalles";    break;
       case "additional notes":    $s = "Notas adicionales";  break;
       case "ok":      $s = "OK";    break;
       case "update":      $s = "Actualizar";    break;
       case "close":      $s = "Cerrar";    break;
       case "in progress":    $s = "En progreso";  break;
       case "closed":      $s = "Cerrado";    break;
       case "reopen":      $s = "ReAbierto";    break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }