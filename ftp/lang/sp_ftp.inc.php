<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "logout/relogin"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "borrar $m1"; break;
       case "deleted": $s = "Exitosamente borrado $m1"; break;
       case "failed to delete": $s = "Fallo al borrar $m1"; break;
       case "view": $s = "ver"; break;
       case "save": $s = "grabar"; break;
       case "save to filemanager": $s = "grabar al manejador de archivo"; break;
       case "upload": $s = "subir"; break;
       case "uploaded": $s = "Exitosamente subido $m1"; break;
       case "failed to upload": $s = "Fallo al subir $m1"; break;
       case "create new directory": $s = "Crear nuevo directorio"; break;
       case "failed to mkdir": $s = "Fallo al crear directorio $m1"; break;
       case "empty dirname": $s = "Intento de crear un directorio sin nombre"; break;
       case "module name": $s = "Cliente Ftp"; break;
       case "home": $s = "Home"; break;
       case "username": $s="Usuario"; break;
       case "password": $s="Contraseña"; break;
       case "ftpserver": $s="Servidor Ftp"; break;
       case "connect": $s="Conectar"; break;
       case "bad connection": $s="Fallo al conectar a $m1 con el usuario $m2 y contraseña $m3"; break;
       case "cancel": $s="cancelar"; break;
       case "rename": $s="renombrar $m1"; break;
       case "renamed": $s="Renombrar $m1 a $m2"; break;
       case "created directory": $s="Exitosamente creado $m1"; break;
       case "failed to rename": $s="Fallo al renombrar $m1 a $m2"; break;
       case "rename from": $s="Renombrar desde"; break;
       case "rename to": $s="a"; break;
       case "confirm delete": $s="Realmente desea borrar $m1 ?"; break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>