<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "logout/relogin"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "delete $m1"; break;
       case "deleted": $s = "Succsessfully deleted $m1"; break;
       case "failed to delete": $s = "Failed to delete $m1"; break;
       case "view": $s = "view"; break;
       case "save": $s = "save"; break;
       case "save to filemanager": $s = "save to filemanager"; break;
       case "upload": $s = "upload"; break;
       case "uploaded": $s = "Successfully uploaded $m1"; break;
       case "failed to upload": $s = "Failed to upload $m1"; break;
       case "create new directory": $s = "Create new dir"; break;
       case "failed to mkdir": $s = "Failed to created direcotory $m1"; break;
       case "empty dirname": $s = "Attempt to create a directory with empty name"; break;
       case "module name": $s = "Ftp Client"; break;
       case "home": $s = "Home"; break;
       case "username": $s="Username"; break;
       case "password": $s="Password"; break;
       case "ftpserver": $s="Ftp Server"; break;
       case "connect": $s="Connect"; break;
       case "bad connection": $s="Failed to connect to $m1 with user $m2 and password $m3"; break;
       case "cancel": $s="cancel"; break;
       case "rename": $s="rename $m1"; break;
       case "renamed": $s="Renamed $m1 to $m2"; break;
       case "created directory": $s="Successfully created $m1"; break;
       case "failed to rename": $s="Failed to rename $m1 to $m2"; break;
       case "rename from": $s="Rename from"; break;
       case "rename to": $s="to"; break;
       case "confirm delete": $s="Do you really want to delete $m1 ?"; break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>