<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "logout/relogin"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "slette $m"; break;
       case "deleted": $s = "Slettet $m1"; break;
       case "failed to delete": $s = "Kunne ikke slette $m1"; break;
       case "view": $s = "vis"; break;
       case "save": $s = "lagre"; break;
       case "save to filemanager": $s = "lagre til filmanager"; break;
       case "upload": $s = "upload"; break;
       case "uploaded": $s = "Suksessfullt uploaded $m1"; break;
       case "failed to upload": $s = "Kunne ikke uploade $m1"; break;
       case "create new directory": $s = "Laget nytt dir"; break;
       case "failed to mkdir": $s = "Kunne ikke lage nytt direcotory $m1"; break;
       case "empty dirname": $s = "Forsøk å lage et directory uten navn"; break;
       case "module name": $s = "Ftp Klient"; break;
       case "home": $s = "Home"; break;
       case "username": $s="Brukernavne"; break;
       case "password": $s="Passord"; break;
       case "ftpserver": $s="Ftp Server"; break;
       case "connect": $s="Connect"; break;
       case "bad connection": $s="Kunne ikke connecte til $m1 med bruker $m2 og passord $m3"; break;
       case "cancel": $s="avbryt"; break;
       case "rename": $s="rename $m"; break;
       case "renamed": $s="Renamed $m1 til $m2"; break;
       case "created directory": $s="Laget $m1"; break;
       case "failed to rename": $s="Kunne ikke rename $m1 til $m2"; break;
       case "rename from": $s="Rename fra"; break;
       case "rename to": $s="til"; break;
       case "confirm delete": $s="Vil du virkelig slette $m1 ?"; break;

       default: $s = "* $message";
    }
    return $s;
  } 
?>