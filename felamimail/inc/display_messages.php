<?php
   /**
    **  display_messages.php
    **
    **  This contains all messages, including information, error, and just
    **  about any other message you can think of.
    **
    ** $Id$
    **/

    $display_messages_php = true;

    function error_username_password_incorrect($color) {
      echo '<BR>';
      echo " <TABLE COLS=1 WIDTH=75% NOBORDER BGCOLOR=\"$color[4]\" ALIGN=CENTER>";
      echo '   <TR>';
      echo "      <TD BGCOLOR=\"$color[0]\">";
      echo '         <B><CENTER>ERROR</CENTER></B>';
      echo '   </TD></TR><TR><TD>';
      echo '      <CENTER><BR>' . lang("Unknown user or password incorrect.") . '<BR><A HREF="login.php" TARGET=_top>' . lang("Click here to try again") . '</A>.</CENTER>';
      echo '   </TD></TR>';
      echo '</TABLE>';
      echo '</BODY></HTML>';
    }

    function general_info($motd, $org_logo, $version, $org_name, $color) {
      echo '<BR>';
      echo "<TABLE COLS=1 WIDTH=80% CELLSPACING=0 CELLPADDING=2 NOBORDER ALIGN=CENTER><TR><TD BGCOLOR=\"$color[9]\">";
      echo '<TABLE COLS=1 WIDTH=100% CELLSPACING=0 CELLPADDING=3 NOBORDER BGCOLOR="#FFFFFF" ALIGN=CENTER>';
      echo '   <TR>';
      echo "      <TD BGCOLOR=\"$color[0]\">";
      echo '         <B><CENTER>';
      printf (lang("Welcome to %1's WebMail system"), $org_name);
      echo '         </CENTER></B>';
      echo '   <TR><TD BGCOLOR="#FFFFFF">';
      echo '      <TABLE COLS=2 WIDTH=90% CELLSPACING=0 CELLPADDING=3 NOBORDER align="center">';
      echo '         <TR>';
      echo '            <TD BGCOLOR="#FFFFFF"><CENTER>';
      if (strlen($org_logo) > 3)
         echo "               <IMG SRC=\"$org_logo\">";
      else
         echo "               <B>$org_name</B>";
      echo '         <BR><CENTER>';
      printf (lang("Running SquirrelMail version %1 (c) 1999-2000."), $version);
      echo '            </CENTER><BR>';
      echo '            </CENTER></TD></TR><TR>';
      echo '            <TD BGCOLOR="#FFFFFF">';
      echo "               $motd";
      echo '            </TD>';
      echo '         </TR>';
      echo '      </TABLE>';
      echo '   </TD></TR>';
      echo '</TABLE>';
      echo '</TD></TR></TABLE>';
   }

    function error_message($message, $mailbox, $sort, $startMessage, $color) {
	  global $phpgw;
      $urlMailbox = urlencode($mailbox);

      echo '<BR>';
      echo "<TABLE COLS=1 WIDTH=70% NOBORDER BGCOLOR=\"$color[4]\" ALIGN=CENTER>";
      echo '   <TR>';
      echo "      <TD BGCOLOR=\"$color[0]\">";
      echo "         <FONT COLOR=\"$color[2]\"><B><CENTER>" . lang("ERROR") . '</CENTER></B></FONT>';
      echo '   </TD></TR><TR><TD>';
      echo "      <CENTER><BR>$message<BR>\n";
      echo '      <BR>';
      echo "         <A HREF=\"". $phpgw->link("/felamimail/index.php","sort=$sort&startMessage=$startMessage&mailbox=$urlMailbox")."\">";
      printf (lang("Click here to return to %1"), $mailbox);
      echo '</A>.';
      echo '   </TD></TR>';
      echo '</TABLE>';
    }

    function plain_error_message($message, $color) {
      echo '<BR>';
      echo "<TABLE COLS=1 WIDTH=70% NOBORDER BGCOLOR=\"$color[4]\" ALIGN=CENTER>";
      echo '   <TR>';
      echo "      <TD BGCOLOR=\"$color[0]\">";
      echo "         <FONT COLOR=\"$color[2]\"><B><CENTER>" . lang("ERROR") . '</CENTER></B></FONT>';
      echo '   </TD></TR><TR><TD>';
      echo "      <CENTER><BR>$message";
      echo '      </CENTER>';
      echo '   </TD></TR>';
      echo '</TABLE>';
    }
?>
