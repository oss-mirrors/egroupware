<?

if($page=="")$page=1;//default forum page is 1
if($config->forumnum==""){ //defaults to forumnum 1, if you had many forums
  $forumnum=1;
}else{
  $forumnum=$config->forumnum;
}

if($return){	
	$postmessage=0;
	$postid=0;
}

  //Run Message Post Code Block, if needed
if($postmessage){//error checks a post
	if($author==""){
		$ErrorMessage=$config->authorError."<br><br>";	
	}
	if($subject==""){
		$ErrorMessage.=$config->subjectError."<br><br>";	
	}
	
	if($ErrorMessage){
		echo "<a href=\"javascript:history.back()\">".$config->backonimg."</a>";
	}else{
		if(!$host)
		    $host = getenv('REMOTE_HOST');
		  
		if(!$host)
		    $host = getenv('REMOTE_ADDR');    
	  
		//Sends Message Posting to Database
		if($forumlib->AddPost($parent,$author,$subject,$messagebody,$host,$config,$forumnum)){
		    echo "Message added, thanks $author<br><br>";
		}else{
		    echo "Message not added, database Error!<br><Br>";
		}
	}
}

//If 
if($ErrorMessage==""){
	if($postid){
		if($returntopost)$editbutton=false;
		
		if($forumlib->adminmode AND $deletebutton){
			if($confirmdelete){
				$forumlib->deletePost($postid,true,$config);
			}else{
				$forumlib->deletePost($postid,false,$config);
			}
		}elseif($forumlib->adminmode AND $editbutton){
			if($confirmupdate){
				$forumlib->editPost($postid,true,$config);
			}else{
				$forumlib->editPost($postid,false,$config);
			}
		}else{
		//Show Post
		$forumlib->ShowPost($postid, $config,$forumnum);
		$forumlib->ShowForm($postid,$forumnum,$page,$config);
		}
	}else{
		//Show MAIN PAGE
	
		echo "<font size=".($config->fontsizemain)." face='".$config->fontfacemain."'>";
		echo "<a href='#formbegin'>". lang_forum("Post Message") ."</a> | <a href='",$phpgw->link("$config->forumfile") ,"'>" . lang_forum("refresh forum") ."</a>";

		if($forumlib->adminmode){
			echo " | <a href='",$config->forumadminfile,"?logout=true'>Logout</a></font>";
		}else{
			echo " | <a href='",$config->forumadminfile,"'>Admin Login</a></font>";
		}
		echo "<br><table width=",$config->forumwidth,"><tr valign=top><td><hr><br><font size=".$config->fontsizemain." face='".$config->fontfacemain."'>";
		$forumlib->ShowThreads($page, $config->maxthreads, -1, $forumnum,$config,0,-999);
		echo "</font></td></tr></table>";
		$forumlib->ShowForm(-1,$forumnum,$page,$config);
	}
}

?>