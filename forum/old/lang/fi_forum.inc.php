<?php

  function lang_mrbs($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {


       case "daybefore":			$s = "Edellinen päivä";			break;


       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>
