<?php
	/**************************************************************************\
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier   oliviert@maphilo.com       *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
class chatty{

	var $chatty_info;
	var $so;

	var $public_functions = array
	(
	'syncData'=>true,
	'restoreWindows'=>true,
	'sendMsg'=>true,
	'showInfo'=>false
	);

	function chatty(){
		$this->chatty_info = array();
		$this->so = CreateObject("chatty.chatty_so");
	}

	function syncData($args){
		
		$this->saveWindowSize($args);
		//$windowsSize = $this->chatty_info["listofwindows"];
		//$wmsize = $args["wmanager"];
		
		$users = $this->getConnectedUsers();		
		$docChatty = array(
				'status'=>"ok", 
                'data'=>array('chatty_info' => array(
                    							'userlists'=> array(
                    										'user'=> $users),
                    							'com'=>$this->checkNewMsgs()
                    							)
				)
            );
        return $docChatty;
		
	}

	function saveWindowSize($args){

		$windowsSize = $args["listofchildwindows"];
		$wmsize = $args["wmanager"];
		
		$this->chatty_info = $GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"]);		

		/* unset all windows size and save the current opened window size and position */		
		unset($this->chatty_info["listofwindows"]);
				
		/*save window manager size and posittion */
		$this->chatty_info["wmanager"]= $wmsize;
		/* save all chat windows size and position */
		$this->chatty_info["listofwindows"] = array();
		foreach($windowsSize as $key=>$value){
		$winid = $key;	
		$Xwin = $windowsSize[$key]["position"]["x"];
		$Ywin = $windowsSize[$key]["position"]["y"];
		$Wwin = $windowsSize[$key]["size"]["w"];
		$Hwin = $windowsSize[$key]["size"]["h"];
		$minmax = $windowsSize[$key]["minmax"];
		$this->chatty_info["listofwindows"][$winid]["position"]["x"] = $Xwin;
		$this->chatty_info["listofwindows"][$winid]["position"]["y"] = $Ywin;
		$this->chatty_info["listofwindows"][$winid]["size"]["w"] = $Wwin;
		$this->chatty_info["listofwindows"][$winid]["size"]["h"] = $Hwin;
		$this->chatty_info["listofwindows"][$winid]["minmax"] = $minmax;
		}
		
		$GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"],$this->chatty_info);

	}
	
	function restoreWindows(){
			$windows = array();
			$this->chatty_info = $GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"]);
			if(!isset($this->chatty_info["listofwindows"])){
				$this->chatty_info["listofwindows"] = array();
			}
			else{
				foreach ($this->chatty_info["listofwindows"] as $winid=>$windowsAttributes){
						$windows[$winid] = $windowsAttributes;
				}
			}
			$wmsize = $this->chatty_info["wmanager"];
			$users = $this->getConnectedUsers();	
			$docChatty = array(
								'chatty_info' => array(
                    									'listofusers'=> $users,
                    									'listofchatwindows'=>$windows,
                    									'wmanager' =>$wmsize,
                    									'com'=>$this->getAllMsgs()
                    							)
							);
		return $docChatty;
	}

	function getConnectedUsers(){
		$alluserstab = $GLOBALS['egw']->session->list_sessions(0,'asc','session_dla');
		$usercon = array();
		foreach ($alluserstab as $key=>$value){
			if($value["session_lid"]!= $this->so->moi){
				$usercon[$value["session_lid"]]+=1;
			}
		}

		$users = array_keys($usercon);
		return $users;
	}

	function sendMsg($args) {
		$this->chatty_info = $GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"]);		
		$txttosend = $args["msg"];
		$txttosend = nl2br("&nbsp;".$txttosend);
		$chatto = $args["rcpt"];
		$letemps = CreateObject("phpgwapi.datetime");
		$lheure = $letemps->gmtdate($letemps->users_localtime);
		$lheure = $lheure['hour'].':'.$lheure['minute'].':'.$lheure['second'];
		
		$this->chatty_info["messages"][$chatto][] = array("msg"=>$txttosend, "sender"=>$this->so->moi, "time"=>$lheure );
		$tmptab = array($GLOBALS['egw_info']['user']['userid'].'@'.$GLOBALS['egw_info']['user']['domain'], $chatto);
		sort($tmptab);
		$sessionId = "chatty".$tmptab[0].'-'.$tmptab[1];
		$msgtodb = array( "session_id"=>$sessionId, "texte"=>$txttosend, "userid"=>$chatto);
		$this->so->add($msgtodb);
		$GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"],$this->chatty_info);
		
		$toreturn = array('chatty_info' => array('msg'=>$txttosend,
												 'chatwindow' => $chatto,
												 'sender'=>	$this->so->moi	
												)
						); 
		return($toreturn);
	}


	function checkNewMsgs() {

		$this->chatty_info = $GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"]);


		$newmsgs=$this->so->getNewMsgs();
		$messages = array();
		if (is_array($newmsgs)) {
			$toreturn = array('status'=>"ok");
			foreach ($newmsgs as $winid=>$value){
				foreach ($value as $msg){
					$letemps = CreateObject("phpgwapi.datetime");
					$lheure = $letemps->gmtdate($letemps->users_localtime);
					$lheure = $lheure['hour'].':'.$lheure['minute'].':'.$lheure['second'];
					$this->chatty_info["messages"][$msg["sender"]][] = array("msg"=>$msg['txt'], "sender"=>$msg["sender"], "time"=>$lheure);
					$messages[]=array('msg'=>$msg['txt'], 'chatwindow' => $msg["sender"], 'sender'=>$msg["sender"]);
				}
			}
			$toreturn['messages'] = $messages;
			$GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"],$this->chatty_info);
		}
		else {
			$toreturn = array('status'=>"noanswer");
		}
	return $toreturn;		
	}
	
	function getAllMsgs() {
		$this->chatty_info = $GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"]);
		$messages = array();
		$toreturn = array('status'=>"noanswer");
		if(isset($this->chatty_info["messages"])){
				$toreturn['status'] = "ok";
			//	reset($this->chatty_info["messages"]);
			foreach ($this->chatty_info["messages"] as $msgsender=>$listmsg){
					foreach ($listmsg as $msg){
						$messages[]=array('msg'=>$msg["msg"], 'sender' => $msg["sender"], 'chatwindow'=>$msgsender, 'time'=>$msg["time"]);
					}
				}
				$toreturn['messages'] = $messages;
		}
			return $toreturn;
		}
		
		
		function showInfo(){
			$this->chatty_info = $GLOBALS['egw']->session->appsession("chatty_info", $GLOBALS['egw_info']["flags"]["currentapp"]);
			echo (var_dump($this->chatty_info));
			
		}
	
	
}
?>