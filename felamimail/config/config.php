<?php
	// This is only going to be used durring the port ...

#	if ($phpgw_info['xyz'] != True)
#	{
#		$phpgw_info['xyz'] = True;
#		$phpgw_info['user']['preferences'] = $phpgw->common->create_emailpreferences($phpgw_info['user']['preferences']);
#	}

	$bopreferences = CreateObject('felamimail.bopreferences');
	$preferences = $bopreferences->getPreferences();

	// don't change
	$config_version = "x62";

	//  Organization's logo picture (blank if none)
    	$org_logo = "../images/sm_logo.jpg";

	//  Organization's name
	$org_name = "SquirrelMail";
	
	//  Webmail Title
	//  This is the title that goes at the top of the browser window
	$org_title = "SquirrelMail $version";
	
	//debug
	// setup the imap settings
	// $imapServerAddress = $phpgw_info['user']['preferences']['email']['mail_server'];
	// $imapPort = $phpgw_info['user']['preferences']['email']['mail_port'];
	
	$domain			= $preferences['defaultDomainname'];
	
	$smtpServerAddress	= $preferences['smtpServerAddress'];
	$smtpPort 		= $preferences['smtpPort'];
	
	$imapServerAddress 	= $preferences['imapServerAddress'];
	$imapPort		= $preferences['imapPort'];

	$key			= $preferences['key'];
	$username		= $preferences['username'];

	#print "Data: $username:$key<br>";
	
	// not supported anymore
	//  Uncomment this if you want to deliver locally using sendmail instead
	//  of connecting to a SMTP-server
	//    $useSendmail = true;
	//    $sendmail_path = "/usr/sbin/sendmail";

	// not supported anymore
	//  This is displayed right after they log in
	$motd = "";

	//  Whether or not to use a special color for special folders.  If not, special
	//  folders will be the same color as the other folders
	$use_special_folder_color = true;

	//  The type of IMAP server you are running
	//  Valid type are the following (case is important).
	//  
	//  courier
	//  cyrus
	//  exchange
	//  uw
	$imap_server_type = $preferences['imap_server_type'];

	//  Many servers store mail in your home directory.  With this, they
	//  store them in a subdirectory: mail/ or Mail/, etc.  If your
	//  server does this, please set this to what the default mail folder
	//  should be.  This is still a user preference, so they can change
	//  it if it is different for each user.
	//
	//  Example:
	//     $default_folder_prefix = "mail/";
	//        -- or --
	//     $default_folder_prefix = "Mail/folders/";
	//
	//  If you do not use this, please set it to "".
	$default_folder_prefix = "";
	//  If you do not wish to give them the option to change this, set it to false.
	//  Otherwise, if it is true, they can change the folder prefix to be anything.
	$show_prefix_option = false;

	//  The following are related to deleting messages.
	//    $move_to_trash
	//         - if this is set to "true", when "delete" is pressed, it
	//           will attempt to move the selected messages to the folder
	//           named $trash_folder. If it's set to "false", we won't even
	//           attempt to move the messages, just delete them.
	//    $trash_folder
	//         - This is the path to the default trash folder. For Cyrus
	//           IMAP, it would be "INBOX.Trash", but for UW it would be
	//           "Trash". We need the full path name here.
	//    $auto_expunge
	//         - If this is true, when a message is moved or copied, the
	//           source mailbox will get expunged, removing all messages
	//           marked "Deleted".
	//    $sent_folder
	//         - This is the path to where Sent messages will be stored.
	
	$default_move_to_trash 	= $preferences['move_to_trash'];
	$default_move_to_sent  	= $preferences['move_to_sent'];
	$trash_folder		= $preferences['trash_folder'];
	$auto_expunge 		= false;
	$sent_folder 		= $preferences['sent_folder'];

//  Special Folders are folders that can't be manipulated like normal
//  user created folders can. A couple of examples would be
//  "INBOX.Trash", "INBOX.Drafts". We have them set to Netscape's
//  default mailboxes, but this obviously can be changed. To add one,
//  just add a new number to the array.

    $special_folders[0] = "INBOX";   // The first one has to be the inbox (whatever the name is)
    $special_folders[1] = $trash_folder;
    $special_folders[2] = $sent_folder;
    $special_folders[3] = "INBOX.Drafts";
    $special_folders[4] = "INBOX.Templates";

//  Whether or not to list the special folders first  (true/false)
    $list_special_folders_first = true;

//  Are all your folders subfolders of INBOX (i.e.  cyrus IMAP server)
//  If you are not sure, set it to false.
    $default_sub_of_inbox = true;

//  Some IMAP daemons (UW) handle folders weird. They only allow a
//  folder to contain either messages or other folders, not both at
//  the same time. This option controls whether or not to display an
//  option during folder creation. The option toggles which type of
//  folder it should be.
//
//  If this option confuses you, make it "true". You can't hurt
//  anything if it's true, but some servers will respond weird if it's
//  false. (Cyrus works fine whether it's true OR false).

    $show_contain_subfolders_option = false;

//  This option controls what character set is used when sending mail
//  and when sending HTMl to the browser. Do not set this to US-ASCII,
//  use ISO-8859-1 instead. For cyrillic it is best to use KOI8-R,
//  since this implementation is faster than the alternatives.
    $default_charset = "iso-8859-1";

//  Path to the data/ directory
//    It is a possible security hole to have a writable directory
//    under the web server's root directory (ex: /home/httpd/html).
//    For this reason, it is possible to put the data directory
//    anywhere you would like. The path name can be absolute or
//    relative (to the config directory). It doesn't matter. Here are
//    two examples:
//
//  Absolute:
//    $data_dir = "/usr/local/felamimail/data/";
//
//  Relative (to the config directory):
//    $data_dir = "../data/";

//    $data_dir = "./data/";
	$data_dir = $phpgw_info["server"]["temp_dir"];

//  Path to directory used for storing attachments while a mail is
//  being sent. There are a few security considerations regarding this
//  directory:
//    - It should have the permission 733 (rwx-wx-wx) to make it
//      impossible for a random person with access to the webserver to
//      list files in this directory. Confidential data might be laying
//      around there
//    - Since the webserver is not able to list the files in the content
//      is also impossible for the webserver to delete files lying around 
//      there for too long.
//    - It should probably be another directory than data_dir.

    $attachment_dir = $data_dir;

//  This is the default size of the folder list.  Default is 150,
//  but you can set it to whatever you wish.

   $default_left_size = 150;

//  Some IMAP servers allow a username (like "bob") to log in if they use
//  uppercase in their name (like "Bob" or "BOB").  This creates extra
//  preference files.  Toggling this option to true will transparently
//  change all usernames to lowercase.

   $force_username_lowercase = false;


//  Themes
//     You can define your own theme and put it in this directory.  You must
//     call it as the example below.  You can name the theme whatever you
//     want.  For an example of a theme, see the ones included in the config
//     directory.
//
//  To add a new theme to the options that users can choose from, just add
//  a new number to the array at the bottom, and follow the pattern.

    // The first one HAS to be here, and is your system's default theme.
    // It can be any theme you want
    $theme[0]["PATH"] = "../themes/default_theme.php";
    $theme[0]["NAME"] = "Default";

/*    $theme[1]["PATH"] = "../themes/plain_blue_theme.php";
    $theme[1]["NAME"] = "Plain Blue";

    $theme[2]["PATH"] = "../themes/sandstorm_theme.php";
    $theme[2]["NAME"] = "Sand Storm";

    $theme[3]["PATH"] = "../themes/deepocean_theme.php";
    $theme[3]["NAME"] = "Deep Ocean";

    $theme[4]["PATH"] = "../themes/slashdot_theme.php";
    $theme[4]["NAME"] = "Slashdot";

    $theme[5]["PATH"] = "../themes/purple_theme.php";
    $theme[5]["NAME"] = "Purple";

    $theme[6]["PATH"] = "../themes/forest_theme.php";
    $theme[6]["NAME"] = "Forest";

    $theme[7]["PATH"] = "../themes/ice_theme.php";
    $theme[7]["NAME"] = "Ice";

    $theme[8]["PATH"] = "../themes/seaspray_theme.php";
    $theme[8]["NAME"] = "Sea Spray";

    $theme[9]["PATH"] = "../themes/bluesteel_theme.php";
    $theme[9]["NAME"] = "Blue Steel";

    $theme[10]["PATH"] = "../themes/dark_grey_theme.php";
    $theme[10]["NAME"] = "Dark Grey";

    $theme[11]["PATH"] = "../themes/high_contrast_theme.php";
    $theme[11]["NAME"] = "High Contrast";

    $theme[12]["PATH"] = "../themes/black_bean_burrito_theme.php";
    $theme[12]["NAME"] = "Black Bean Burrito";

    $theme[13]["PATH"] = "../themes/servery_theme.php";
    $theme[13]["NAME"] = "Servery";

    $theme[14]["PATH"] = "../themes/maize_theme.php";
    $theme[14]["NAME"] = "Maize";

    $theme[15]["PATH"] = "../themes/bluesnews_theme.php";
    $theme[15]["NAME"] = "BluesNews";
*/
//  LDAP server(s)
//
//    Array of arrays with LDAP server parameters. See
//    functions/abook_ldap_server.php for a list of possible
//    parameters
//
//    EXAMPLE:
//
//    $ldap_server[0] = Array(
//			"host" => "memberdir.netscape.com",
//			"name" => "Netcenter Member Directory",
//			"base" => "ou=member_directory,o=netcenter.com");



 // you have an option to chose between javascript or html version of
 // address book searching.  
 //   true = javascript
 //  false = html

 $default_use_javascript_addr_book = false;

 // these next two options set the defaults for the way that the users see
 // their folder list.
 //   $default_unseen_notify   specifies whether or not the users will see
 //                            the number of unseen in each folder by default
 //                            and alsy which folders to do this to.
 //                            1=none, 2=inbox, 3=all
 //   $default_unseen_type     specifies the type of notification to give the
 //                            users by default.
 //                            1=(4), 2=(4,25)

 $default_unseen_notify = 2;
 $default_unseen_type   = 1;
 
 // If you are running on a machine that doesn't have the tm_gmtoff
 // value in your time structure and if you are in a time zone that
 // has a negative offset, you need to set this value to 1.
 // This is typically people in the US that are running Solaris 7.
 
 $invert_time = false;

 // To install plugins, just add elements to this array that have
 // the plugin directory name relative to the /plugins/ directory.
 // For instance, for the "sqclock" plugin, you'd put a line like
 // the following:
 //   $plugins[0] = "sqclock";

?>
