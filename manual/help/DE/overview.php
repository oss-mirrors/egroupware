<?php
  /**************************************************************************\
  * phpGroupWare - User manual                                               *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "manual", "enable_utilities_class" => True);
  include("../../../header.inc.php");
  $font = $phpgw_info['theme']['font'];
?>
<img src="<?php echo $phpgw->common->image('../../../manual/DE/phpgwapi','logo.gif'); ?>" border="0">
<font face="<?php echo $font; ?>" size="2"><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Die folgenden Seiten können als Richtlinien für die Navigation durch diese Seiten hier, und die Funktionalität jedes Programms wird erkl&auml;rt.<p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
Bitte besuchen Sie die Deutsche Projektseite <a href="http://www.phpgw.de" target=_new><b> 
www.phpgw.de </b></a> oder die englische Projekt-Hauptseite<a href="http://www.phpgroupware.org" target=_new><b> 
www.phpgroupware.org </b></a> dieses Freien Open Source Projekts, als versuch 
den jungen Leuten die dieses Paket geschrieben (oder zumindest viel davon zusammengefügt 
haben) zu sagen, Danke. Ohne solch eifrige und enthusiastische Zusammenarbeit 
würden viele Projekte im Internet, wie wir sie kennen, nicht existieren. Oder 
sie würden mindestens den bekannten <b> kostenpflichtigen </b> Alternativen gleichen.<br/>
Für die von euch die "<a href="http://www.fsf.org/philosophy/free-sw.de.html" target=_new>freie 
Software</a>" noch nicht kennen, bitte schaut euch kurz die Philosophie der <a href="http://www.fsf.org" target=_new>Freie 
Software Stiftung</a> an.<p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
Nun zum &Uuml;berblick: (Bitte beachten Sie das dies ein sich entwickelndes Projekt 
ist, also werden einige Funktionen noch nicht f&uuml;r Sie verfügbar sein, oder 
einige Extras noch nicht in diesem Dokument integriert sein.) Wenn Sie probleme 
mit diesen Seiten oder was da geschrieben steht haben, bitte <a href=mailto:"kim@vuurwerk.nl"> 
ein Mail </a> und wir werden unser bestes tun, Probleme zu beheben oder Sie zu 
unterst&uuml;tzen.
<p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Die Namen sind ziemlich augenscheinlich (Nur leider manchmal in Englisch).
<ul>
  <li><b>Adressbuch:</b><br/>
Ein schnelles und detailliertes Adressbuch, um verschiedene Kontaktinformationen aufbewahren zu k&ouml;nnen und einer Suchfunktion
um Leute die sie brauchen schnell zu finden.</li><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Administration:</b><br/>
    Hier kann der Administrator der Systems Benutzer und Gruppen erstellen, Zugriffsebenen 
    für die Benutzer und Gruppen einstellen, aktive Session's anzeigen (schauen 
    wer im System eingeloggt ist), zugriffs Logs anzeigen, Schlagzeilen Statistiken 
    einstellen, Netzwerk-News setzten und einsehen was sonst noch f&uuml;r interessante 
    dinge mit dem System passieren. </li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Bookmarks:</b><br/>
    Hier können Sie Lesezeichen &auml;hnlich wie in Ihrem gewohnten Browser (Bookmarks 
    [Netscape/Mozilla], Favoriten [MS Internet Explorer]) anlegen, und auf jedem 
    Computer mit dem Sie auf die Groupware kommen darauf zugreifen. Da jetzt immer 
    noch in Entwicklung, wird es m&ouml;glicherweise noch in andere Eigenschaften 
    integriert.</li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Kalender:</b><br/>
    T&auml;gliche, w&ouml;chentliche und monatliche Anzeige, mit stündlicher Aufteilung 
    jedes Tages, Termin Planung und mit der Möglichkeit anderen Benutzern oder 
    Gruppen Ihre verfügbarkeit anzuzeigen. Auch hier gibt es eine Suchfunktion, 
    die sehr n&uuml;tzlich ist um diese vergessenen Geburtstage und Termine aufzuspüren 
    ;) </li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Chat:</b><br/>
Chat-R&auml;ume um in Echtzeit mit anderen benutzern in dem System zu palavern.</li><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Email:</b><br/>
Pop3 und IMAP funktionalit&auml;t für Webbasiertes Email.</li><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>File Manager:</b><br/>
    Programm das hilft Dokumente in einer Gruppe oder privat zu organisieren. 
    Hochladen, editieren und kopieren.</li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Schlagzeilen:</b><br/>
    Neueste teilchen vom Neuesten, wie es von Ihrem Systemadministrator konfiguriert 
    und von Ihnen in Ihren Einstellungen ausgew&auml;hlt wurde.</li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <li><b>Willkommen:</b><br/>
Die erste Seite die Sie sehen wenn Sie sich in das System einloggen.</li><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Company Directory:</b><br/>
    Sehen Sie wo alle Leute im System hingeh&ouml;ren.. Welcher Gruppe sie Angehören 
    und welche Gruppen welche Benutzer haben.</li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Einstellungen</b><br/>
    Hier k&ouml;nnen Sie Ihr Passwort &auml;ndern, verscheidene Anzeige-Schemen 
    ausw&auml;hlen, Ihre Einstellungen &auml;ndern und ausw&auml;hlen welche News-Gruppen 
    sie gerne Abonnieren m&ouml;chten.. viel Spass :)</li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Inventar:</b><br/>
Erstellen und unterhalten sie eine Inventarsverwaltung, gekoppelt mit Ihrem Adressbuch.</li><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Aufgaben:</b><br/>
&Uuml;berpr&uuml;fung Ihrer eigenen Aufgaben und der ihrer Gruppen mitglieder. Auch hier ist eine Suchfunktion integriert.</li><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>NNTP:</b><br/>
Ihr Newsreader.</li><p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<li><b>Trouble Ticket System:</b><br/>
    Nachf&uuml;hrungs-System für problem Ticket's und Problem-Aufl&ouml;sung.</li>
  <p/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</ul>
Das ist es... f&uuml;r detailliertere Informationen schauen Sie bitte in die eingehenden 
Kapitel... und vergessen Sie nicht auf die kleinen viereckigen K&auml;stchen in 
der n&auml;he der Drop-Down-Menues zu klicken die es in jedem Programm gibt.</font> 
<?php $phpgw->common->phpgw_footer(); ?>
