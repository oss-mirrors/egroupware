<?
/*********FORUM ADMINISTRATION TOOL**************/

//Set path for the Library, here's what I used as an example....
$forumlibpath="./forumlib.php";




//Cookies must be set before anything else
if($forumuser)
	setcookie("forumusercookie",$forumuser,time()+2592000);
if($forumpass)
	setcookie("forumpasscookie",$forumpass,time()+2592000);  //sets expire time for a month	
if($author)
	setcookie("usernameforumcookie",$author,time()+2592000);

if($logout){
	setcookie("forumpasscookie");
	setcookie("forumusercookie");
}

//INCLUDES 
include($forumlibpath);
//Include the page header
include($config->headerpath);

$adminlogin=false; //preset this, else a GET method variable could auto log you in, like "adminlogin=1" for example


if(!$logout){
	
	if($forumusercookie!="")
	$forumuser=$forumusercookie;
	if($forumpasscookie!="")
	$forumpass=$forumpasscookie;
	
	//Goes through array of user/pass to check for valid login
	reset($config->adminuser);
	while (list($username, $pass) = each($config->adminuser)) {
	   if($forumuser==$username){
	   	if($forumpass==$pass){
	   		$adminlogin=true;
	   		$forumlib->adminmode=true;
	   		break;	
	   	}
	   }
	}
}
	
      
if($adminlogin){
$config->forumfile=$config->forumadminfile;
echo "<small>admin <b>$forumuser</b> logged in</small><br><br>";

include($config->forummainpath);


}else{//Not logged in

echo "<h3>",$config->forumname," Admin Login</h3>";

if($loginattempt){
	echo "Login failed, Try again.<bR><br>";
}

echo "
<form method=post action='",$config->forumadminfile,"'>
<input type=text name=forumuser><br>
<input type=password name=forumpass><br>
<input type=hidden name=loginattempt value=true>
<input type=submit value=Login>
</form>
";

}


//Include the page footer
include($config->footerpath);
?>