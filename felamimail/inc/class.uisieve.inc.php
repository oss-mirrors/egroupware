<?php
	/***************************************************************************\
	* phpGroupWare - FeLaMiMail                                                 *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.phpgroupware.org                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class uisieve
	{

		var $public_functions = array
		(
			'addScript'		=> True,
			'decreaseFilter'	=> True,
			'deleteScript'		=> True,
			'editRule'		=> True,
			'editScript'		=> True,
			'increaseFilter'	=> True,
			'mainScreen'		=> True,
			'updateRules'		=> True
		);

		function uisieve()
		{
			
			$this->t 		= CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			
			$config 		= CreateObject('phpgwapi.config','felamimail');
			$config->read_repository();
			$felamimailConfig 	= $config->config_data;
			unset($config);
			
			$this->restoreSessionData();
			
			$sieveHost		= $felamimailConfig["sieveServer"];
			$sievePort		= $felamimailConfig["sievePort"];
			$username		= $GLOBALS['phpgw_info']['user']['userid'];
			$password		= $GLOBALS['phpgw_info']['user']['passwd'];
			$this->sieve		= CreateObject('felamimail.SieveSession',$sieveHost, $sievePort, $username, $password);
			if(!$this->sieve->start())
			{
				print "bad thing!!<br>";
			}
			
			$this->rowColor[0] = $GLOBALS['phpgw_info']["theme"]["bg01"];
			$this->rowColor[1] = $GLOBALS['phpgw_info']["theme"]["bg02"];

		}
		
/*		function addRule()
		{
			if(isset($_POST[anyof]))
			{
				$newRule[prioritiy]	= count($this->rules)*2+1;
				$newRule[status]	= 'ENABLED';
				$newRule[from]		= get_var('from',array('POST'));
				$newRule[to]		= get_var('to',array('POST'));
				$newRule[subject]	= get_var('subject',array('POST'));
				//$newRule[flg]		= get_var('???',array('POST'));
				$newRule[field]		= get_var('field',array('POST'));
				$newRule[field_val]	= get_var('field_val',array('POST'));
				$newRule[size]		= intval(get_var('size',array('POST')));
				$newRule['continue']	= get_var('continue',array('POST'));
				$newRule[gthan]		= intval(get_var('gthan',array('POST')));
				$newRule[anyof]		= intval(get_var('anyof',array('POST')));
				$newRule[keep]		= get_var('keep',array('POST'));
				$newRule[regexp]	= get_var('regexp',array('POST'));
				$newRule[unconditional]	= '0';		// what's this???
				
				switch(get_var('action',array('POST')))
				{
					case 'reject':
						$newRule[action]	= 'reject';
						$newRule[action_arg]	= get_var('reject',array('POST'));
						break;
						
					case 'folder':
						$newRule[action]	= 'folder';
						$newRule[action_arg]	= get_var('folder',array('POST'));
						break;

					case 'address':
						$newRule[action]	= 'address';
						$newRule[action_arg]	= get_var('address',array('POST'));
						break;

					case 'discard':
						$newRule[action]	= 'discard';
						break;
				}
				
				if($newRule[action])
				{
					$this->rules[] = $newRule;
				
					$this->updateScript();
					
					$this->saveSessionData();
				}
			
				$this->mainScreen();
			}
			else
			{
				// display the header
				$this->display_app_header();
			
				// initialize the template
				$this->t->set_file(array("filterForm" => "sieveEditForm.tpl"));
				$this->t->set_block('filterForm','main');
#				$this->t->set_block('filterForm','scriptrow');
#				$this->t->set_block('filterForm','filterrow');
			
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uisieve.addRule',
					'scriptname'	=> $scriptName
				);
				$this->t->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$linkData));
	
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uisieve.mainScreen',
					'scriptname'	=> $scriptName
				);
				$this->t->set_var('url_back',$GLOBALS['phpgw']->link('/index.php',$linkData));

				// translate most of the parts
				$this->translate();
				$this->t->pfp("out","main");
				
				$this->sieve->close();
			}
		}
*/		
		function addScript()
		{
			if($scriptName = get_var('newScriptName',Array('POST')))
			{
				$script	= CreateObject('felamimail.Script',$scriptName);
				$script->updateScript($this->sieve);
			}
			
			$this->mainScreen();
		}

		function activateScript()
		{
			$scriptName = $GLOBALS['HTTP_GET_VARS']['script'];
			if(!empty($scriptName))
			{
				if($this->sieve->sieve_setactivescript($scriptName))
				{
					#print "Successfully changed active script!<br>";
				}
				else
				{
					#print "Unable to change active script!<br>";
					/* we could display the full output here */
				}
			}
                    
			$this->mainScreen();
		}
		
		function buildRule($rule) 
		{
			$andor = " AND ";
			$started = 0;
			if ($rule['anyof']) $andor = " OR ";
			$complete = lang('IF').' ';
			if ($rule['unconditional']) $complete = "[Unconditional] ";
			
			if ($rule['from']) 
			{
				$match = $this->setMatchType($rule['from'],$rule['regexp']);
				$complete .= "'From:' " . $match . " '" . $rule['from'] . "'";
				$started = 1;
			}
			if ($rule['to']) 
			{
				if ($started) $complete .= $andor;
				$match = $this->setMatchType($rule['to'],$rule['regexp']);
				$complete .= "'To:' " . $match . " '" . $rule['to'] . "'";
				$started = 1;
			}
			if ($rule['subject']) 
			{
				if ($started) $complete .= $andor;
				$match = $this->setMatchType($rule['subject'],$rule['regexp']);
				$complete .= "'Subject:' " . $match . " '" . $rule['subject'] . "'";
				$started = 1;
			}
			if ($rule['field'] && $rule['field_val']) 
			{
				if ($started) $complete .= $andor;
				$match = $this->setMatchType($rule['field_val'],$rule['regexp']);
				$complete .= "'" . $rule['field'] . "' " . $match . " '" . $rule['field_val'] . "'";
				$started = 1;
			}
			if ($rule['size']) 
			{
				$xthan = " less than '";
				if ($rule['gthan']) $xthan = " greater than '";
				if ($started) $complete .= $andor;
				$complete .= "message " . $xthan . $rule['size'] . "KB'";
				$started = 1;
			}
			if (!$rule['unconditional']) $complete .= ' '.lang('THEN').' ';
			if (preg_match("/folder/i",$rule['action']))
				$complete .= lang('file into')." '" . $rule['action_arg'] . "';";
			if (preg_match("/reject/i",$rule['action']))
				$complete .= "reject '" . $rule['action_arg'] . "';";
			if (preg_match("/address/i",$rule['action']))
				$complete .= "forward to '" . $rule['action_arg'] . "';";
			if (preg_match("/discard/i",$rule['action']))
				$complete .= "discard;";
			if ($rule['continue']) $complete .= " [Continue]";
			if ($rule['keep']) $complete .= " [Keep a copy]";

			return $complete;
		}
		
		function decreaseFilter()
		{
			$ruleID = get_var('ruleID',array('GET'));
			if ($this->rules[$ruleID] && $this->rules[$ruleID+1]) 
			{
				$tmp = $this->rules[$ruleID+1];
				$this->rules[$ruleID+1] = $this->rules[$ruleID];
				$this->rules[$ruleID] = $tmp;
			}
			
			$this->updateScript();
			
			$this->saveSessionData();
			
			$this->mainScreen();
		}

		function deleteScript()
		{
			$scriptName = get_var('scriptname',array('GET'));
			if(!empty($scriptName))
			{
				if($this->sieve->deletescript($scriptName))
				{
					# alles ok!
				}
			}
			
			$this->mainScreen();
		}

		function display_app_header()
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}
		
		function displayRule($_ruleID, $_ruleData)
		{
			// display the header
			$this->display_app_header();

			// initialize the template
			$this->t->set_file(array("filterForm" => "sieveEditForm.tpl"));
			$this->t->set_block('filterForm','main');
#			$this->t->set_block('filterForm','scriptrow');
#			$this->t->set_block('filterForm','filterrow');

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.editRule',
				'scriptname'	=> $scriptName
			);
			$this->t->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.mainScreen',
				'scriptname'	=> $scriptName
			);
			$this->t->set_var('url_back',$GLOBALS['phpgw']->link('/index.php',$linkData));
			
			if(is_array($_ruleData))
			{
			if($_ruleData['continue']) 
				$this->t->set_var('continue_checked','checked');
			if($_ruleData['keep']) 
				$this->t->set_var('keep_checked','checked');
			if($_ruleData['regexp']) 
				$this->t->set_var('regexp_checked','checked');
			$this->t->set_var('anyof_selected'.intval($_ruleData['anyof']),'selected');
			$this->t->set_var('value_from',$_ruleData['from']);
			$this->t->set_var('value_to',$_ruleData['to']);
			$this->t->set_var('value_subject',$_ruleData['subject']);
			$this->t->set_var('gthan_selected'.intval($_ruleData['gthan']),'selected');
			$this->t->set_var('value_size',$_ruleData['size']);
			$this->t->set_var('value_field',$_ruleData['field']);
			$this->t->set_var('value_field_val',$_ruleData['field_val']);
			$this->t->set_var('checked_action_'.$_ruleData['action'],'checked');
			$this->t->set_var('value_'.$_ruleData['action'],$_ruleData['action_arg']);
			}
			$this->t->set_var('value_ruleID',$_ruleID);
			
			// translate most of the parts
			$this->translate();
			$this->t->pfp("out","main");
		}
		
		function editRule()
		{
			if(isset($_POST[anyof]))
			{
				if(get_var('priority',array('POST')) != 'unset')
				{
					$newRule[prioritiy]	= get_var('priority',array('POST'));
				}
				$ruleID 		= get_var('ruleID',array('POST'));
				if($ruleID == 'unset')
					$ruleID = count($this->rules);
				$newRule[prioritiy]	= $ruleID*2+1;
				$newRule[status]	= 'ENABLED';
				$newRule[from]		= get_var('from',array('POST'));
				$newRule[to]		= get_var('to',array('POST'));
				$newRule[subject]	= get_var('subject',array('POST'));
				//$newRule[flg]		= get_var('???',array('POST'));
				$newRule[field]		= get_var('field',array('POST'));
				$newRule[field_val]	= get_var('field_val',array('POST'));
				$newRule[size]		= intval(get_var('size',array('POST')));
				$newRule['continue']	= get_var('continue',array('POST'));
				$newRule[gthan]		= intval(get_var('gthan',array('POST')));
				$newRule[anyof]		= intval(get_var('anyof',array('POST')));
				$newRule[keep]		= get_var('keep',array('POST'));
				$newRule[regexp]	= get_var('regexp',array('POST'));
				$newRule[unconditional]	= '0';		// what's this???
				
				switch(get_var('action',array('POST')))
				{
					case 'reject':
						$newRule[action]	= 'reject';
						$newRule[action_arg]	= get_var('reject',array('POST'));
						break;
						
					case 'folder':
						$newRule[action]	= 'folder';
						$newRule[action_arg]	= get_var('folder',array('POST'));
						break;

					case 'address':
						$newRule[action]	= 'address';
						$newRule[action_arg]	= get_var('address',array('POST'));
						break;

					case 'discard':
						$newRule[action]	= 'discard';
						break;
				}
				
				if($newRule[action])
				{
					$this->rules[$ruleID] = $newRule;
				
					$this->updateScript();
					
					$this->saveSessionData();
				}
			
				$this->mainScreen();
			}
			else
			{
				if(isset($_GET['ruleID']))
				{
					$ruleID = get_var('ruleID',Array('GET'));
					$ruleData = $this->rules[$ruleID];
					$this->displayRule($ruleID, $ruleData);
				}
				else
				{
					$this->displayRule('unset', false);
				}
				$this->sieve->close();
			}
		}
		
		function editScript()
		{
			$scriptName = get_var('scriptname',array('GET'));
			$script	= CreateObject('felamimail.Script',$scriptName);
			if(!empty($scriptName))
			{
				if($this->sieve->getscript($scriptName))
				{
					$this->scriptToEdit 	= $scriptName;
					if (!$script->retrieveRules($this->sieve))
					{
						print "can't receive script<br>";
					}
					else
					{
						$this->rules	= $script->rules;
						$this->vacation	= $script->vacation;
					}
				}
				else
				{
					#print "Unable to change active script!<br>";
					/* we could display the full output here */
				}
			}
                    	$this->saveSessionData();
			$this->mainScreen();
		}

		function increaseFilter()
		{
			$ruleID = get_var('ruleID',array('GET'));
			if ($this->rules[$ruleID] && $this->rules[$ruleID-1]) 
			{
				$tmp = $this->rules[$ruleID-1];
				$this->rules[$ruleID-1] = $this->rules[$ruleID];
				$this->rules[$ruleID] = $tmp;
			}
			
			$this->updateScript();
			
			$this->saveSessionData();
			
			$this->mainScreen();
		}

		function mainScreen()
		{
			// display the header
			$this->display_app_header();
			
			// initialize the template
			$this->t->set_file(array("filterForm" => "sieveForm.tpl"));
			$this->t->set_block('filterForm','header');
			$this->t->set_block('filterForm','scriptrow');
			$this->t->set_block('filterForm','filterrow');
			
			// translate most of the parts
			$this->translate();
			
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.addScript'
			);
			$this->t->set_var('action_add_script',$GLOBALS['phpgw']->link('/index.php',$linkData));

			if($this->sieve->listscripts())
			{
				foreach($this->sieve->scriptlist as $scriptID => $scriptName)
				{
					$this->t->set_var("scriptnumber",$scriptID);
					$this->t->set_var("scriptname",$scriptName);

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.deleteScript',
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('link_deleteScript',$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.editScript',
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('link_editScript',$GLOBALS['phpgw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.activateScript',
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('link_activateScript',$GLOBALS['phpgw']->link('/index.php',$linkData));

					if($this->sieve->activescript == $scriptID)
					{
						$this->t->set_var('active','*');
					}
					else
					{
						$this->t->set_var('active','');
					}
					                
					$this->t->parse('scriptrows','scriptrow',true);
				}
			}
			else
			{
				$this->t->set_var("scriptrows",'');
			}
			if(!empty($this->scriptToEdit))
			{
				#$this->t->set_var("editScriptName",$this->scriptToEdit);
				#$this->t->set_var("scriptContent",$this->scriptContent);
				$listOfImages = array(
					'up',
					'down'
				);
				foreach ($listOfImages as $image)
				{
					$this->t->set_var('url_'.$image,$GLOBALS['phpgw']->common->image('felamimail',$image));
				}
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uisieve.editRule'
				);
				$this->t->set_var('url_add_rule',$GLOBALS['phpgw']->link('/index.php',$linkData));

				foreach ($this->rules as $ruleID => $rule)
				{
					$this->t->set_var('filter_status',$rule[status]);
					$this->t->set_var('filter_text',$this->buildRule($rule));
					$this->t->set_var('ruleID',$ruleID);

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.editRule',
						'ruleID'	=> $ruleID
					);
					$this->t->set_var('url_edit_rule',$GLOBALS['phpgw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.increaseFilter',
						'ruleID'	=> $ruleID
					);
					$this->t->set_var('url_increase',$GLOBALS['phpgw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.decreaseFilter',
						'ruleID'	=> $ruleID
					);
					$this->t->set_var('url_decrease',$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.updateRules'
					);
					$this->t->set_var('action_rulelist',$GLOBALS['phpgw']->link('/index.php',$linkData));

					$this->t->parse('filterrows','filterrow',true);
				}
			}
			else
			{
				$this->t->set_var("editScriptName",'');
				$this->t->set_var("scriptContent",'');
			}
	                $linkData = array
	                (
	                        'menuaction'    => 'felamimail.uisieve.saveScript'
	                );
			$this->t->set_var('formAction',$GLOBALS['phpgw']->link('/index.php',$linkData));
	                $linkData = array
	                (
	                        'menuaction'    => 'felamimail.uisieve.mainScreen'
	                );
			$this->t->set_var('link_newScript',$GLOBALS['phpgw']->link('/index.php',$linkData));
			
			$this->t->pfp("out","header");
			
			$this->sieve->close();
		}
		
		function restoreSessionData()
		{
			$sessionData = $GLOBALS['phpgw']->session->appsession('sieve_session_data');
			
			$this->rules		= $sessionData['sieve_rules'];
			$this->scriptToEdit	= $sessionData['sieve_scriptToEdit'];
		}
		
		function setMatchType (&$matchstr, $regex = false)
		{
			$match = lang('contains');
			if (preg_match("/\s*!/", $matchstr))
				$match = lang('does not contain');
			if (preg_match("/\*|\?/", $matchstr))
			{
				$match = lang('matches');
				if (preg_match("/\s*!/", $matchstr))
					$match = lang('does not match');
			}
			if ($regex)
			{
				$match = lang('matches regexp');
				if (preg_match("/\s*!/", $matchstr))
					$match = lang('does not match regexp');
			}
			$matchstr = preg_replace("/^\s*!/","",$matchstr);
			
			return $match;
		}
		
		function saveScript()
		{
			$scriptName = $GLOBALS[HTTP_POST_VARS]['scriptName'];
			$scriptContent = $GLOBALS[HTTP_POST_VARS]['scriptContent'];
			if(isset($scriptName) and isset($scriptContent))
			{
				if($this->sieve->sieve_sendscript($scriptName, stripslashes($scriptContent)))
				{
					#print "Successfully loaded script onto server. (Remember to set it active!)<br>";
				}
				else
				{
/*					print "Unable to load script to server.  See server response below:<br><blockquote><font color=#aa0000>";
					if(is_array($sieve->error_raw))
					foreach($sieve->error_raw as $error_raw)
						print $error_raw."<br>";
					else
						print $sieve->error_raw."<br>";
						print "</font></blockquote>";
						$textarea=stripslashes($script);
						$textname=$scriptname;
						$titleline="Try editing the script again! <a href=$PHP_SELF>Create new script</a>";*/
				}
			}
			$this->mainScreen();
		}

		function saveSessionData()
		{
			$sessionData['sieve_rules']		= $this->rules;
			$sessionData['sieve_scriptToEdit']	= $this->scriptToEdit;
			
			$GLOBALS['phpgw']->session->appsession('sieve_session_data','',$sessionData);
		}
		
		function translate()
		{
			$this->t->set_var("lang_message_list",lang('Message List'));
			$this->t->set_var("lang_from",lang('from'));
			$this->t->set_var("lang_to",lang('to'));
			$this->t->set_var("lang_edit",lang('edit'));
			$this->t->set_var("lang_delete",lang('delete'));
			$this->t->set_var("lang_enable",lang('enable'));
			$this->t->set_var("lang_rule",lang('rule'));
			$this->t->set_var("lang_disable",lang('disable'));
			$this->t->set_var("lang_subject",lang('subject'));
			$this->t->set_var("lang_filter_active",lang('filter active'));
			$this->t->set_var("lang_filter_name",lang('filter name'));
			$this->t->set_var("lang_new_filter",lang('new filter'));
			$this->t->set_var("lang_no_filter",lang('no filter'));
			$this->t->set_var("lang_activate",lang('activate'));
			$this->t->set_var("lang_add_rule",lang('add rule'));
			$this->t->set_var("lang_add_script",lang('add script'));
			$this->t->set_var("lang_back",lang('back'));

			$this->t->set_var("bg01",$GLOBALS['phpgw_info']["theme"]["bg01"]);
			$this->t->set_var("bg02",$GLOBALS['phpgw_info']["theme"]["bg02"]);
			$this->t->set_var("bg03",$GLOBALS['phpgw_info']["theme"]["bg03"]);
		}
		
		function updateRules()
		{
			$action 	= get_var('rulelist_action',array('POST'));
			$ruleIDs	= get_var('ruleID',array('POST'));
			
			switch($action)
			{
				case 'enable':
					if(is_array($ruleIDs))
					{
						foreach($ruleIDs as $ruleID)
						{
							$this->rules[$ruleID][status] = 'ENABLED';
						}
					}
					break;
					
				case 'disable':
					if(is_array($ruleIDs))
					{
						foreach($ruleIDs as $ruleID)
						{
							$this->rules[$ruleID][status] = 'DISABLED';
						}
					}
					break;
					
				case 'delete':
					if(is_array($ruleIDs))
					{
						foreach($ruleIDs as $ruleID)
						{
							unset($this->rules[$ruleID]);
						}
					}
					$this->rules = array_values($this->rules);
					break;
			}  
			
			$this->updateScript();
			
			$this->saveSessionData();
			
			$this->mainScreen();
		}

		function updateScript()
		{
			$script			= CreateObject('felamimail.Script',$this->scriptToEdit);
			$script->rules		= $this->rules;
			$script->vacation	= $this->vacation;
			if (!$script->updateScript($this->sieve)) 
			{
				print "update failed<br>";
				print $script->errstr."<br>";
			}
		}
	}
?>
