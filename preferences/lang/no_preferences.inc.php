<?php

  function lang_pref($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "max matchs per page":
	$s = "Max matches per side";		break;

       case "time zone offset":
	$s = "Tids-sone offset";		break;

       case "this server is located in the x timezone":
	$s = "Denne server er i " . $m1 . " tids-sonen";	break;

       case "date format":	$s = "Dato format";			break;
       case "time format":	$s = "Tids format";			break;
       case "language":		$s = "Spr�ke";			break;

       case "show text on navigation icons":
	$s = "Vis tekst p� navigasjons ikoner";			break;

       case "show current users on navigation bar":
	$s = "Vis current brukere i navigation bar";	break;

       case "show new messages on main screen":
	$s = "Vis nye meldinger p� hovedskjerm";	break;

       case "email signature":
	$s = "E-Post signatur";	break;

       case "show birthday reminders on main screen":
	$s = "Vis f�dselsdags p�minnere p� hovedskjerm";	break;

       case "show high priority events on main screen":
	$s = "Vis h�yprioritets events p� hovedskjermen";	break;

       case "weekday starts on":
	$s = "Ukedag begynner p�";	break;

       case "work day starts on":
	$s = "Arbeidsdag begynner p�";	break;

       case "work day ends on":
	$s = "Arbeidsdag slutter p�";	break;

       case "select headline news sites":
	$s = "Velg Headline News sites";	break;

       case "change your password":
	$s = "Endre passord";		break;

       case "select different theme":
	$s = "Velg annet tema";		break;

       case "change your settings":
	$s = "Endre innstillinger";		break;

       case "enter your new password":
	$s = "Skriv inn ditt nye passord";		break;

       case "re-enter your password":
	$s = "Skriv inn ditt passord igjen";	break;

       case "the two passwords are not the same":
	$s = "Passordene stemmer ikke overens";	break;

       case "you must enter a password":
	$s = "Du m� skrive inn et passord";	break;

       case "your current theme is: x":
	$s = "Ditt tema er: <b>" . $m1 . "</b>";	break;

       case "please, select a new theme":
	$s = "Vennligst velg et nytt tema";	break;

       case "note: this feature does *not* change your email password. this will need to be done manually.":
         $s = "Noter: Denne funksonen endrer *ikke* ditt epost passord. Dette m� gj�res manuellt.";	break;

       default: $s = "* $message";
    }
    return $s;
  }
