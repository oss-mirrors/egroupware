<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "logout/relogin"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "supprimer $m1"; break;
       case "deleted": $s = "$m1 supprim&eacute; avec succ&egrave;s"; break;
       case "failed to delete": $s = "Impossible de supprimer $m1"; break;
       case "view": $s = "voir"; break;
       case "save": $s = "sauver"; break;
       case "save to filemanager": $s = "sauver vers le gestionnaire de fichiers"; break;
       case "upload": $s = "upload"; break;
       case "uploaded": $s = "$m1 upload&eacute; avec succ&egrave;s"; break;
       case "failed to upload": $s = "Impossible d'uploader $m1"; break;
       case "create new directory": $s = "Cr&eacute;er un nouveau r&eacute;pertoire"; break;
       case "failed to mkdir": $s = "Impossible de cr&eacute;er le r&eacute;pertoire $m1"; break;
       case "empty dirname": $s = "Essaie de cr&eacute;er un r&eacute;pertoire sans nom"; break;
       case "module name": $s = "Client Ftp"; break;
       case "home": $s = "Home"; break;
       case "username": $s="Utilisateur"; break;
       case "password": $s="Mot de passe"; break;
       case "ftpserver": $s="Server Ftp"; break;
       case "connect": $s="Connecter"; break;
       case "bad connection": $s="Impossible de se connecter &agrave; $m1 avec l'utilisateur $m2 et le mot de passe $m3"; break;
       case "cancel": $s="annuler"; break;
       case "rename": $s="renomer $m1"; break;
       case "renamed": $s="$m1 renom&eacute; en $m2"; break;
       case "created directory": $s="$m1 cr&eacute;&eacute; avec succ&egrave;s"; break;
       case "failed to rename": $s="imossible de renomer $m1 en $m2"; break;
       case "rename from": $s="Renomer depuis"; break;
       case "rename to": $s="vers"; break;
       case "confirm delete": $s="Voulez-vous vraiment supprimer $m1 ?"; break;

       default: $s = "* ". $message;
    }
    return $s;
  }
?>

