<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "LogOut/ReLogin"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "$m1 l&ouml;schen"; break;
       case "deleted": $s = "$m1 erfolgreich gel&ouml;scht"; break;
       case "failed to delete": $s = "Konnte $m1 nicht l&ouml;schen"; break;
       case "view": $s = "Anzeigen"; break;
       case "save": $s = "Speichern"; break;
       case "save to filemanager": $s = "In den FileManager speichern"; break;
       case "upload": $s = "Upload"; break;
       case "uploaded": $s = "$m1 erfolgreich hochgeladen"; break;
       case "failed to upload": $s = "$m1 konnte nicht hochgeladen werden"; break;
       case "create new directory": $s = "Neues Verzeichnis erstellen"; break;
       case "failed to mkdir": $s = "Failed to created direcotory $m1"; break;
       case "empty dirname": $s = "Es wurde versucht, ein Verzeichnis mit leerem Namen anzulegen"; break;
       case "module name": $s = "Ftp Client"; break;
       case "home": $s = "Home"; break;
       case "username": $s="Username"; break;
       case "password": $s="Password"; break;
       case "ftpserver": $s="Ftp Server"; break;
       case "connect": $s="Connect"; break;
       case "bad connection": $s="Verbindung zu $m1 mit UserNamen $m2 und Passwort $m3 fehlgeschlagen"; break;
       case "cancel": $s="abbrechen"; break;
       case "rename": $s="$m1 umbenennen"; break;
       case "renamed": $s="$m1 umbenannt in $m2"; break;
       case "created directory": $s="$m1 erfolgreich erstellt"; break;
       case "failed to rename": $s="Konnte $m1 nicht in $m2 umbenennen"; break;
       case "rename from": $s="Umbenennen von"; break;
       case "rename to": $s="in"; break;
       case "confirm delete": $s="M”chten Sie $m1 wirklich l&ouml;schen ?"; break;

       default: $s = "* ". $message;
    }
    return $s;
  }
?>