<?

  $phpgw_flags["currentapp"] = "forum";
  include("../header.inc.php");

  $forumlibpath="./forumlib.php";



//if($author)
//	setcookie("usernameforumcookie",$author,time()+2592000);

//INCLUDES the forum code library
include($forumlibpath);

//Goes through array of user/pass to check for valid login
	reset($config->adminuser);
	while (list($username, $pass) = each($config->adminuser)) {
	   if($forumusercookie==$username){
	   	if($forumpasscookie==$pass){
	   		$adminlogin=true;
	   		break;	
	   	}
	   }
	}

if($adminlogin){
header("Location: ".$config->forumadminfile);  /* User is admin so redirect to admin page */
exit;  /* kill script*/
}

  //Include the page header
include($config->headerpath);

  //Include Main Forum Code that generates HTML, posts messages...
include($config->forummainpath);


  //Include the page footer
include($config->footerpath);

?>

