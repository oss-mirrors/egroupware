<?php
	/**************************************************************************\
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier       oliviert@maphilo.com   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
class chatty_so {

	var $moi;
	var $user;
	var $db;
	var $table;

	function chatty_so(){
		define("NEWMSG", true);
		define("OLDMSG", false);
		$this->moi = $GLOBALS['egw_info']['user']['userid'].'@'.$GLOBALS['egw_info']['user']['domain'];
		$this->db = $GLOBALS['phpgw']->db;
		$this->db->set_app('chatty');
		$this->table = 'chatty_msgs';
		$this->user = $GLOBALS['egw_info']['user']['account_id'];
	}

	function add($values)
	{
		$columns = $this->_msg2db($values,$values['timestamps'] ? $values['timestamps'] : time() . ',0,0');
		if (!$this->db->insert($this->table,$columns,False,__LINE__,__FILE__))
		{
			return False;
		}
		return $this->db->get_last_insert_id($this->table,'id');
	}

	function _msg2db($values,$timestamps)
	{
		foreach(array('session_id','texte','userid') as $name)
		{
			$columns[$name] = $values[$name];
		}
		$columns['heure'] = $timestamps;
		$columns['statut'] = NEWMSG;
		$columns['sender'] = $this->moi;

		return $columns;
	}


	function getNewMsgs()
	{
		//$query = "SELECT * FROM $this->table WHERE userid='".$this->moi."'AND statut=".NEWMSG;
		$this->db->select($this->table,'*',array('userid'=>$this->moi, "statut"=>NEWMSG),__LINE__,__FILE__, false, "ORDER BY id");

		$newmsgtab = array();
		$i = 0; //if there's more than 1 msg to get, indexed the msg with an int
		$ifrow = false;
		$idtolog = array();
		while ($this->db->next_record()) {

			if(is_array($newmsgtab[$this->db->f('session_id')])) $i++;
			$newmsgtab[$this->db->f('session_id')][$i] = array(
			"heure"=>strtotime($this->db->f('heure')),
			"txt"=>$this->db->f('texte'),
			"sender"=>$this->db->f('sender')
			);
		$ifrow = true;
		$idtolog[]=$this->db->f('id');
		}

		if(!$ifrow){
			return false;
		}
		else{
			foreach ($idtolog as $id){
				$this->db->update($this->table, array('statut'=>OLDMSG), array('id'=>$id), __LINE__, __FILE__);
			}
			return $newmsgtab;
		}
	}
}

?>