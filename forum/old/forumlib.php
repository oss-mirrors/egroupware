<?
//Forum Library
//Contains critical functions for the forum.

//SET THIS PATH to point to the forumconfig.php script!
include("./forumconfig.php"); //creates the $config object, and connects to the database


class forumlib {
	
	var $adminmode=false;
	
	//constructor
	function forumlib(){
	
	}

	
	//Recursive function that displays the threads in the forum
	function ShowThreads($page, $threadlimit, $parent, $forumnum, $config,$showtop,$current){
		
		if($showtop){
		$tophandler=mysql($config->DBname,"select * from forumthreads where id=$parent and forum=$forumnum");	
			$postid=mysql_result($tophandler,$i,"id");
			$date=mysql_result($tophandler,$i,"date");
			$author=StripSlashes(mysql_result($tophandler,$i,"author"));
			$subject=StripSlashes(mysql_result($tophandler,$i,"subject"));
			$host=mysql_result($tophandler,$i,"host");
			
			
		 	$bodyhandler=mysql($config->DBname,"select body from forumbodies where id=$postid");
		 	if($postid==$current)$subject="<b>".$subject."</b>";
		 	if(StripSlashes(mysql_result($bodyhandler,0,"body"))==""){ //handles the auto no text
		 		echo "
					<ul>
					<li>$subject - $author NT <i>$date</i>
				";
		 	}else{
				echo "
					<ul>
					<li><a href=\"",$config->forumfile,"?postid=$postid\">$subject</a> - $author <i>$date</i>
				";
			}
			mysql_freeresult($bodyhandler);
		}
		
		if($parent==-1){//show next and back buttons on main page...
			$hi=$page*$threadlimit;
			$lo=$hi-$threadlimit;
			
			$mainhandler=mysql($config->DBname,"select * from forumthreads where parent=$parent AND forum=$forumnum ORDER BY date DESC LIMIT $lo,$hi");
			$numthreads=mysql_numrows($mainhandler);
			
			$numposthan=mysql($config->DBname,"select count(*) as num from forumthreads where parent=-1 AND forum=$forumnum");
			$numthreadstotal=mysql_result($numposthan,0,"num");
			
			echo "<b>Page: $page</b><Br><br>";
			if($page>1){
				$buttoncode.="<a href=\"".$config->forumfile."?forumnum=$forumnum&page=".($page-1)."\"><img src=\"".$config->backonimg."\" border=0></a>";
			}else{
				$buttoncode.="<img src=\"".$config->backoffimg."\" border=0>";
			}
			if($numthreadstotal>$hi){
				$buttoncode.="<a href=\"".$config->forumfile."?forumnum=$forumnum&page=".($page+1)."\"><img src=\"".$config->nextonimg."\" border=0></a>";
			}else{
				$buttoncode.="<img src=\"".$config->nextoffimg."\" border=0>";
			}
			
			echo $buttoncode;
			
			
		}else{

			$mainhandler=mysql($config->DBname,"select * from forumthreads where parent=$parent");	
			$numthreads=mysql_numrows($mainhandler);
			
		}
		
		for($i=0;$i<$numthreads;$i++){
			
			$postid=mysql_result($mainhandler,$i,"id");
			$date=mysql_result($mainhandler,$i,"date");
			$mainthread=mysql_result($mainhandler,$i,"mainthread");
			$parent=mysql_result($mainhandler,$i,"parent");
			$author=StripSlashes(mysql_result($mainhandler,$i,"author"));
			$subject=StripSlashes(mysql_result($mainhandler,$i,"subject"));
			$host=mysql_result($mainhandler,$i,"host");
			
			
		 	$bodyhandler=mysql($config->DBname,"select body from forumbodies where id=$postid");
		 	
		 	if($postid==$current)$subject="<b>".$subject."</b>";
		 	if(StripSlashes(mysql_result($bodyhandler,0,"body"))==""){ //handles the auto no text
		 		echo "
					<ul>
					<li>$subject - $author NT <i>$date</i><a href=\"",$config->forumfile,"?postid=$postid\">(reply)</a>
				";
		 	}else{
				echo "
					<ul>
					<li><a href=\"",$config->forumfile,"?postid=$postid\">$subject</a> - $author <i>$date</i>
				";
			}
			mysql_freeresult($bodyhandler);
			
			$this->ShowThreads(-1,999,$postid,$forumnum, $config,0,$current);
			
			echo "</ul>";
			
		}
		
		if($tophandler){
			echo "</ul>";	
		}
		mysql_freeresult($mainhandler);
		
		//Sticks Next and Back buttons at the bottom..
		if($parent==-1){
			echo $buttoncode;
		}
	
		
	}
	
	//Function used to display a single message.  Edit/Delete buttons displayed for admins
	
	function ShowPost($postid,$config,$forumnum){
		
		$handler=mysql($config->DBname,"Select t.host as host,t.author as author,t.subject as subject,t.date as date,t.mainthread as mainthread,t.parent as parent, b.body as body from forumthreads t, forumbodies b where t.id=$postid AND t.id=b.id");
		
		//echo "SQL=mysql(".$config->DBname.", Select t.author as author,t.subject as subject,t.date as date,t.mainthread as mainthread, b.body as body from forumthreads t, forumbodies b where t.id=$postid AND t.id=b.id";
		
		$author=StripSlashes(mysql_result($handler,0,"author"));
		$subject=StripSlashes(mysql_result($handler,0,"subject"));
		$date=StripSlashes(mysql_result($handler,0,"date"));
		$mainthread=StripSlashes(mysql_result($handler,0,"mainthread"));
		$parent=StripSlashes(mysql_result($handler,0,"parent"));
		$host=StripSlashes(mysql_result($handler,0,"host"));
		$body=StripSlashes(mysql_result($handler,0,"body"));
		$body=nl2br($body);
		
		echo "<br><table 
		cellpadding=",$config->messagepadding," 
		border=",$config->messageborder," 
		width=",$config->forumwidth,">";
		
		echo "<tr><td bgcolor=",$config->authorcolor,">";
		echo "<font face='",$config->fontfacemain,"'> $author</font></td></tr>";
		echo "<tr><td bgcolor=",$config->subjectcolor,"><font face='",$config->fontfacemain,"'><i>$subject</i></font></td></tr>";
		echo "<tr><td bgcolor=",$config->datecolor,"><font face='",$config->fontfacemain,"'>$date</font></td></tr>";
		
		if($this->adminmode){
		echo "<tr><td bgcolor=",$config->datecolor,"><font face='",$config->fontfacemain,"'>$host</font></td></tr>";
		}
		echo "<tr><td bgcolor=",$config->messagecolor,"><font face='",$config->fontfacemain,"'>$body</font></td></tr></table><br>";
		
		
		if($this->adminmode){echo "<form method=post action='",$config->forumfile,"'>
					   <input type=hidden name=postid value='$postid'>
					   <input type=submit name=deletebutton value='Delete Thread'>
					   <input type=submit name=editbutton value='Edit Post'>
					   </form>";
					   }
		
		echo "<hr><font face='",$config->fontfacemain,"' size=",$config->fontsizemain,">Thread:<br><br>";
		$this->ShowThreads(1, 999, $mainthread, $forumnum,$config,1,$postid);
		echo "</font>";
	}
	
	//this function runs when the delete button is pressed
	function deletePost($postid,$confirm,$config){
		
		if($confirm){
			
			mysql($config->DBname,"delete from forumthreads where id=$postid");
			mysql($config->DBname,"delete from forumbodies where id=$postid");
			echo "Post $postid deleted.<br><Br>";
			echo "<form method=post action='",$config->forumfile,"'>
					   <input type=submit name=returntoforum value='Return to Forum'>
					   </form>";
		}else{
			echo "Delete this post and all its replies?<br><Br>";
			echo "<form method=post action='",$config->forumfile,"'>
					   <input type=hidden name=postid value='$postid'>
					    <input type=hidden name=deletebutton value=true>
					   <input type=submit name=confirmdelete value='Delete'>
					   <input type=submit name=returntopost value='Return to Post'>
					   </form>";
		}	
			
	}
	
	//Function to edit messages that have been submitted
	function editPost($postid,$updated,$config){
		global $newauthor;
		global $newsubject;
		global $newbody;
		
		if($updated){
			if(mysql($config->DBname,"update forumthreads set author='$newauthor', subject='$newsubject' where id=$postid")
			   AND 
			   mysql($config->DBname,"update forumbodies set body='$newbody' where id=$postid")
			   			
			){
				echo "Post updated.<br><Br>";
			}else{
				echo "Post not updated.<Br><Br>";
			}
		}
	
		$mainhandler=mysql($config->DBname,"select * from forumthreads where id=$postid");	
		$author=StripSlashes(mysql_result($mainhandler,0,"author"));
		$subject=StripSlashes(mysql_result($mainhandler,0,"subject"));
	 	$bodyhandler=mysql($config->DBname,"select body from forumbodies where id=$postid");
	 	$body=StripSlashes(mysql_result($bodyhandler,0,"body"));
	 	
		echo "<form method=post action='",$config->forumfile,"'>
		   Author:   <input type=text size=40 name=newauthor value='$author'><br>
		   Subject: <input type=text size=40 name=newsubject value='$subject'><br><br>
		   Message:<bR>
		   <textarea rows=8 cols=60 name=newbody>$body</textarea><br>
		   <input type=hidden name=postid value='$postid'>
		   <input type=hidden name=editbutton value=true>
		   <input type=submit name=confirmupdate value='Update'>
		   <input type=submit name=returntopost value='Return to Post'>
		   </form>";	
			
	}
	
	
	//Function used to show message submission form
	function ShowForm($parent,$forummnum, $page,$config){
		global $usernameforumcookie;
		
		
		echo "<a name=formbegin></a><hr><br><Br><form name=postreply method=post action=",$config->forumfile,">
			<input type=hidden name=parent value=$parent>
			<input type=hidden name=mainthread value=$mainthread>
			<input type=hidden name=page value=$page>
			<input type=hidden name=forumnum value=$forumnum>
			<table>
			<tr><td><font face='",$config->fontfacemain,"'>Author:</font></td><td><input type=text name=author size=35 value='$usernameforumcookie'></td><tr>
			<tr><td><font face='",$config->fontfacemain,"'>Subject:</font></td><td><input type=text name=subject size=35></td><tr>
			<tr><td colspan=2>
				<font face='",$config->fontfacemain,"'>Message:<br>
				<textarea name=messagebody cols=60 rows=8></textarea>
			</font>
			</td></tr>
			
			<tr><td><input type=submit class=Button name=postmessage value=Submit></td><td>";
			
			if($parent==-1){
				echo " </td></tr>\n";
			}else{
				echo "<input type=submit class=Button name=return value=\"Return to Forum\"></td></tr>\n";
			}
			
		echo "</table></form>";
	}
	
	//this function adds a message to the forum
	function AddPost($parent,$author,$subject,$messagebody,$host,$config,$forumnum){
		$ip=$this->get_domain($host);
		$author=strip_tags($author);
		$subject=strip_tags($subject);
		
		if($this->isAdmin($config,$author)){
			if($this->adminmode){
				$author=$author." <b>[Admin]</b>";	
			}else{
				$author=$author." (Poser)";
			}	
		}
		
		
		if(!$config->htmlallowed){
			$messagebody=strip_tags($messagebody);	
		}
			
		
		if($parent!=-1){
			$handler=mysql($config->DBname,"Select mainthread from forumthreads where id=$parent");
			$mainthread=mysql_result($handler,0,"mainthread");
		}else{
			$mainthread=-1;
		}
	
		$goodinsert=mysql($config->DBname,"insert into forumthreads (date,mainthread,parent,author,subject,host,forum) values (now(),$mainthread,$parent,'$author','$subject','$host',$forumnum)");
		$postid=mysql_insert_id();
		//echo "SQL=insert into forumthreads (date,mainthread,parent,author,subject,host,forum) values (now(),$mainthread,$parent,'$author','$subject','$host',$forum)<BR>";
		$goodinsert2=mysql($config->DBname,"insert into forumbodies (id,body) values ($postid,'$messagebody')");
		
		if($parent==-1){
			mysql($config->DBname,"update forumthreads set mainthread=$postid where id=$postid");
		}
		
		
		return ($goodinsert AND $goodinsert2);
	}
	
	
	//function used to get hostname
	function get_domain($ip){

		exec("nslookup $ip",$returnarr);
		
		//echo "($returnarr[3])";
	
		if(ereg("Name:",$returnarr[3])){
	
		$output=ereg_replace("Name:    ","",$returnarr[3]);
	
		}else{
		
			$output=$ip;
		}
	
		return $output;

	}

	//simply checks to see if a username matches an admin username
	function isAdmin($config,$username){
		
		while (list($adminusername, $pass) = each($config->adminuser)) {
			if(strtolower($username)==strtolower($adminusername))return true;
		}
		return false;
	}



} 


$forumlib=new forumlib();

?>