<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "afmelden/opnieuw aanmelden"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "verwijder $m1"; break;
       case "deleted": $s = "$m1 met succes verwijderd"; break;
       case "failed to delete": $s = "Verwijderen van $m1 is mislukt"; break;
       case "view": $s = "weergeven"; break;
       case "save": $s = "opslaan"; break;
       case "save to filemanager": $s = "oplaan naar bestandsbeheerder"; break;
       case "upload": $s = "upload"; break;
       case "uploaded": $s = "$m1 met success ge-upload"; break;
       case "failed to upload": $s = "Upload van $m1 is mislukt"; break;
       case "create new directory": $s = "Maak een nieuwe folder"; break;
       case "failed to mkdir": $s = "Maken van folder $m1 is mislukt"; break;
       case "empty dirname": $s = "Poging om een folder zonder naam te maken"; break;
       case "module name": $s = "Ftp Client"; break;
       case "home": $s = "Home"; break;
       case "username": $s="Gebruikersnaam"; break;
       case "password": $s="Wachtwoord"; break;
       case "ftpserver": $s="Ftp Server"; break;
       case "connect": $s="Verbinden"; break;
       case "bad connection": $s="Verbinden met server $m1 als gebruiker $m2 met wachtwoord $m3 is mislukt"; break;
       case "cancel": $s="annuleren"; break;
       case "rename": $s="$m1 hernoemen"; break;
       case "renamed": $s="$m1 hernoemd als $m2"; break;
       case "created directory": $s="$m1 met succes gemaakt"; break;
       case "failed to rename": $s="Hernoemen van $m1 als $m2 is mislukt"; break;
       case "rename from": $s="Hernoem"; break;
       case "rename to": $s="als"; break;
       case "confirm delete": $s="Wilt u $m1 echt verwijderen?"; break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>