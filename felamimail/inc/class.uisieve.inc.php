<?php
	/***************************************************************************\
	* eGroupWare - FeLaMiMail                                                   *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.egroupware.org                                                 *
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
			'activateScript'	=> True,
			'addScript'		=> True,
			'deactivateScript'	=> True,
			'decreaseFilter'	=> True,
			'deleteScript'		=> True,
			'editRule'		=> True,
			'editScript'		=> True,
			'increaseFilter'	=> True,
			'listScripts'		=> True,
			'updateRules'		=> True,
			'updateVacation'	=> True,
			'saveVacation'		=> True,
			'selectFolder'		=> True,
		);

		function uisieve()
		{
			$this->displayCharset	= $GLOBALS['egw']->translation->charset();

			$this->t 		=& CreateObject('phpgwapi.Template',EGW_APP_TPL);
 			$this->botranslation	=& CreateObject('phpgwapi.translation');

			$this->bopreferences    =& CreateObject('felamimail.bopreferences');
			$this->mailPreferences  = $this->bopreferences->getPreferences();
			
			$config 		=& CreateObject('phpgwapi.config','felamimail');
			$config->read_repository();
			$this->felamimailConfig	= $config->config_data;
			unset($config);
			
			$this->restoreSessionData();

			$sieveHost		= $this->mailPreferences["imapSieveServer"];
			$sievePort		= $this->mailPreferences["imapSievePort"];
			$username		= $this->mailPreferences['username'];
			$password		= $this->mailPreferences['key'];
			$this->sieve		=& CreateObject('felamimail.SieveSession',$sieveHost, $sievePort, $username, $password);
			if(!$this->sieve->start())
			{
				print "bad thing!!<br>";
			}
			
			$this->rowColor[0] = $GLOBALS['egw_info']["theme"]["bg01"];
			$this->rowColor[1] = $GLOBALS['egw_info']["theme"]["bg02"];

		}
		
		function addScript()
		{
			if($scriptName = get_var('newScriptName',Array('POST')))
			{
				$script	=& CreateObject('felamimail.Script',$scriptName);
				$script->updateScript($this->sieve);
			}
			
			$this->listScripts();
		}

		function activateScript()
		{
			$scriptName = get_var('scriptname',array('GET'));
			if(!empty($scriptName))
			{
				if($this->sieve->activatescript($scriptName))
				{
					#print "Successfully changed active script!<br>";
				}
				else
				{
					#print "Unable to change active script!<br>";
					/* we could display the full output here */
				}
			}
										
			$this->listScripts();
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
		
		function buildVacationString($_vacation)
		{
#			global $script;
#			$vacation = $script->vacation;
			$vacation_str = '';
			if (!is_array($_vacation))
			{ 
				return @htmlspecialchars($vacation_str); 
			}
			
			$vacation_str .= lang('Respond');
			if (is_array($_vacation['addresses']) && $_vacation['addresses'][0])
			{
				$vacation_str .= ' ' . lang('to mail sent to') . ' ';
				$first = true;
				foreach ($_vacation['addresses'] as $addr)
				{
					if (!$first) $vacation_str .= ', ';
					$vacation_str .= $addr;
					$first = false;
				}
			}
			if (!empty($_vacation['days']))
			{
				$vacation_str .= ' ' . lang("every %1 days",$_vacation['days']);
			}
			$vacation_str .= ' ' . lang('with message "%1"',$_vacation['text']);
			return @htmlspecialchars($vacation_str);
		}
		
		function checkRule($_vacation)
		{
			$this->errorStack = array();
			
			if (!$_vacation['text'])
			{
				$this->errorStack['text'] = lang('Please supply the message to send with auto-responses'.'!	');
			}

			if (!$_vacation['days'])
			{
				$this->errorStack['days'] = lang('Please select the number of days to wait between responses'.'!');
			}
			
			if(is_array($_vacation['addresses']))
			{
				$regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
				foreach ($_vacation['addresses'] as $addr)
				{
					if (!preg_match($regexp,$addr)) 
					{
						$this->errorStack['addresses'] = lang('One address is not valid'.'!');
					}
				}
			}
			else
			{
				$this->errorStack['addresses'] = lang('Please select a address'.'!');
			}
			
			if(count($this->errorStack) == 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function deactivateScript()
		{
			$scriptName = get_var('scriptname',array('GET'));
			if(!empty($scriptName))
			{
				#if($this->sieve->activatescript($scriptName))
				#{
				#	#print "Successfully changed active script!<br>";
				#}
				#else
				#{
				#	#print "Unable to change active script!<br>";
				#	/* we could display the full output here */
				#}
			}
										
			$this->listScripts();
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
			
			$this->editScript();
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
			
			$this->listScripts();
		}

		function display_app_header()
		{
			if(preg_match('/^(vacation|filter)$/',get_var('editmode',array('GET'))))
				$editMode	= get_var('editmode',array('GET'));
			else
				$editMode	= 'filter';

			if(!@is_object($GLOBALS['egw']->js))
			{
				$GLOBALS['egw']->js =& CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['egw']->js->validate_file('tabs','tabs');
			$GLOBALS['egw']->js->validate_file('jscode','editProfile','felamimail');
			$GLOBALS['egw']->js->set_onload("javascript:initAll('$editMode');");
			$GLOBALS['egw_info']['flags']['include_xajax'] = True;
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();
		}
		
		function displayRule($_scriptName, $_ruleID, $_ruleData)
		{
			#_debug_array($_ruleData);
			// display the header
			$this->display_app_header();

			// initialize the template
			$this->t->set_file(array("filterForm" => "sieveEditForm.tpl"));
			$this->t->set_block('filterForm','main');
			$this->t->set_block('filterForm','folder');

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.editRule',
				'scriptname'	=> $_scriptName
			);
			$this->t->set_var('action_url',$GLOBALS['egw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.editScript',
				'scriptname'	=> $_scriptName
			);
			$this->t->set_var('url_back',$GLOBALS['egw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.selectFolder',
				'scriptname'	=> $_scriptName
			);
			$this->t->set_var('folder_select_url',$GLOBALS['egw']->link('/index.php',$linkData));

			
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
				if($_ruleData['action'] == 'folder')
				{
					$this->t->set_var('folderName',$_ruleData['action_arg']);
				}
			}
			$this->t->set_var('value_ruleID',$_ruleID);
			
			#$bofelamimail		=& CreateObject('felamimail.bofelamimail',$this->displayCharset);
			#$uiwidgets		=& CreateObject('felamimail.uiwidgets');
			#$connectionStatus	= $bofelamimail->openConnection();
			#$folders = $bofelamimail->getFolderObjects(false);
			
			#foreach($folders as $folderName => $folderDisplayName)
			#{
			#	$this->t->set_var('folderName',$folderName);
			#	$this->t->set_var('folderDisplayName',$folderDisplayName);
			#	$this->t->parse("folder_rows", 'folder', true); 
			#}
			
			// translate most of the parts
			$this->translate();
			$this->t->pfp("out","main");
		}
		
		function editRule()
		{
			$scriptName = get_var('scriptname',array('GET'));
			$ruleType = get_var('ruletype',array('GET'));
			
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
			
				$this->editScript();
			}
			else
			{
				if(isset($_GET['ruleID']))
				{
					$ruleID = get_var('ruleID',Array('GET'));
					$ruleData = $this->rules[$ruleID];
					$this->displayRule($scriptName, $ruleID, $ruleData);
				}
				else
				{
					$this->displayRule($scriptName, 'unset', false);
				}
				$this->sieve->close();
			}
		}
		
		function editScript()
		{
			$scriptName	= get_var('scriptname',array('GET'));
			if(empty($scriptName))
			{
				$this->sieve->listscripts();
				if(!empty($this->sieve->activescript))
				{
					$scriptName = $this->sieve->activescript;
				}
				else
				{
					$this->listScripts();
					$GLOBALS['egw']->common->egw_exit();
				}
			}

			$uiwidgets	=& CreateObject('felamimail.uiwidgets',EGW_APP_TPL);
			$script		=& CreateObject('felamimail.Script',$scriptName);
			$boemailadmin	=& CreateObject('emailadmin.bo');
			

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
				$this->listScripts();
				$GLOBALS['egw']->common->egw_exit();
			}

											$this->saveSessionData();

			// display the header
			$this->display_app_header();
			
			// initialize the template
			$this->t->set_file(array("filterForm" => "sieveForm.tpl"));
			$this->t->set_block('filterForm','header');
			$this->t->set_block('filterForm','filterrow');
			
			// translate most of the parts
			$this->translate();
			
			if(!empty($this->scriptToEdit))
			{
				$listOfImages = array(
					'up',
					'down'
				);
				foreach ($listOfImages as $image)
				{
					$this->t->set_var('url_'.$image,$GLOBALS['egw']->common->image('felamimail',$image));
				}
			
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uisieve.editRule',
					'scriptname'	=> $scriptName,
					'ruletype'	=> 'filter'
				);
				$this->t->set_var('url_add_rule',$GLOBALS['egw']->link('/index.php',$linkData));

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uisieve.editRule',
					'scriptname'	=> $scriptName,
					'ruletype'	=> 'vacation'
				);
				$this->t->set_var('url_add_vacation_rule',$GLOBALS['egw']->link('/index.php',$linkData));

				foreach ($this->rules as $ruleID => $rule)
				{
					$this->t->set_var('filter_status',lang($rule[status]));
					if($rule[status] == 'ENABLED')
					{
						$this->t->set_var('ruleCSS','sieveRowActive');
					}
					else
					{
						$this->t->set_var('ruleCSS','sieveRowInActive');
					}
					
					$this->t->set_var('filter_text',htmlspecialchars($this->buildRule($rule),ENT_QUOTES,$GLOBALS['egw']->translation->charset()));
					$this->t->set_var('ruleID',$ruleID);

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.editRule',
						'ruleID'	=> $ruleID,
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('url_edit_rule',$GLOBALS['egw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.increaseFilter',
						'ruleID'	=> $ruleID,
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('url_increase',$GLOBALS['egw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.decreaseFilter',
						'ruleID'	=> $ruleID,
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('url_decrease',$GLOBALS['egw']->link('/index.php',$linkData));
					
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.updateRules',
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('action_rulelist',$GLOBALS['egw']->link('/index.php',$linkData));

					$this->t->parse('filterrows','filterrow',true);
				}

				// vacation settings
				
				// vacation status
				if($this->vacation[status] == 'on')
				{
					$this->t->set_var('ruleCSS','sieveRowActive');
					$this->t->set_var('lang_vacation_status',lang('enabled'));
					$this->t->set_var('css_enabled','sieveRowInActive');
					$this->t->set_var('css_disabled','sieveRowActive');
				}
				else
				{
					$this->t->set_var('ruleCSS','sieveRowInActive');
					$this->t->set_var('lang_vacation_status',lang('disabled'));
					$this->t->set_var('css_enabled','sieveRowActive');
					$this->t->set_var('css_disabled','sieveRowInActive');
				}
				
				// vacation text
				$this->t->set_var('vacation_text',$this->botranslation->convert($this->vacation['text'],'UTF-8'));
				
				//vacation days
				$this->t->set_var('selected_'.$this->vacation['days'],'selected="selected"');
					
				// vacation addresses
				if(is_array($this->vacation['addresses']))
				{
					foreach($this->vacation['addresses'] as $address)
					{
						$selectedAddresses[$address] = $address;
					}
					asort($selectedAddresses);
				}

				// all local addresses
				if($emailAddresses = $boemailadmin->getAccountEmailAddress($GLOBALS['egw_info']['user']['userid'], $this->felamimailConfig['profileID']))
				{
					foreach($emailAddresses as $addressData)
					{
						$predefinedAddresses[$addressData['address']] = $addressData['address'];
					}
					asort($predefinedAddresses);
				}

				$this->t->set_var('multiSelectBox',$uiwidgets->multiSelectBox(
						$selectedAddresses,
						$predefinedAddresses,
						'vacationAddresses',
						'400px'
					)
				);

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uisieve.updateVacation',
					'editmode'	=> 'vacation',
					'scriptname'	=> $scriptName
				);
				$this->t->set_var('vacation_action_url',$GLOBALS['egw']->link('/index.php',$linkData));

			}

									$linkData = array
									(
													'menuaction'    => 'felamimail.uisieve.saveScript'
									);
			$this->t->set_var('formAction',$GLOBALS['egw']->link('/index.php',$linkData));
									$linkData = array
									(
													'menuaction'    => 'felamimail.uisieve.mainScreen'
									);
			$this->t->set_var('link_newScript',$GLOBALS['egw']->link('/index.php',$linkData));
			
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.listScripts',
				'scriptname'	=> $scriptName
			);
			$this->t->set_var('url_back',$GLOBALS['egw']->link('/index.php',$linkData));

			$this->t->pfp("out","header");
			
			$this->sieve->close();
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
			
			$this->editScript();
		}
		
		function listScripts()
		{
			$this->display_app_header();

			$this->t->set_file(array("filterForm" => "sieveScriptList.tpl"));
			$this->t->set_block('filterForm','header');
			$this->t->set_block('filterForm','scriptrow');

			// translate most of the parts
			$this->translate();

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.addScript'
			);
			$this->t->set_var('action_add_script',$GLOBALS['egw']->link('/index.php',$linkData));

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
					$this->t->set_var('link_deleteScript',$GLOBALS['egw']->link('/index.php',$linkData));
					
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uisieve.editScript',
						'scriptname'	=> $scriptName
					);
					$this->t->set_var('link_editScript',$GLOBALS['egw']->link('/index.php',$linkData));
					
					if($this->sieve->activescript == $scriptName)
					{
						$linkData = array
						(
							'menuaction'	=> 'felamimail.uisieve.deactivateScript',
							'scriptname'	=> $scriptName
						);
						$this->t->set_var('lang_activate',lang('deactivate script'));
						$this->t->set_var('ruleCSS','sieveRowActive');
					}
					else
					{
						$linkData = array
						(
							'menuaction'	=> 'felamimail.uisieve.activateScript',
							'scriptname'	=> $scriptName
						);
						$this->t->set_var('lang_activate',lang('activate script'));
						$this->t->set_var('ruleCSS','sieveRowInActive');
					}
					$this->t->set_var('link_activateScript',$GLOBALS['egw']->link('/index.php',$linkData));

					$this->t->parse('scriptrows','scriptrow',true);
				}
			}
			#else
			#{
			#	$this->t->set_var("scriptrows",'');
			#}

			$this->t->pfp("out","header");
			
			$this->sieve->close();
		}
		
		function restoreSessionData()
		{
			$sessionData = $GLOBALS['egw']->session->appsession('sieve_session_data');
			
			$this->rules		= $sessionData['sieve_rules'];
			$this->scriptToEdit	= $sessionData['sieve_scriptToEdit'];
		}
		
		function selectFolder()
		{
			if(!@is_object($GLOBALS['egw']->js))
			{
				$GLOBALS['egw']->js =& CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['egw']->js->validate_file('foldertree','foldertree');
			$GLOBALS['egw']->js->validate_file('jscode','editSieveRule','felamimail');
			$GLOBALS['egw']->common->egw_header();

			$bofelamimail		=& CreateObject('felamimail.bofelamimail',$this->displayCharset);
			$uiwidgets		=& CreateObject('felamimail.uiwidgets');
			$connectionStatus	= $bofelamimail->openConnection();

			$folderObjects = $bofelamimail->getFolderObjects(false);
			$folderTree = $uiwidgets->createHTMLFolderJS
			(
				$folderObjects,
				$this->mailbox,
				lang('IMAP Server'),
				$mailPreferences['username'].'@'.$mailPreferences['imapServerAddress'],
				'setMoveToFolderName'
												);
			print $folderTree;
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
		
		function saveVacation()
		{
			
		}
		
		function saveScript()
		{
			$scriptName 	= $_POST['scriptName'];
			$scriptContent	= $_POST['scriptContent'];
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
			
			$GLOBALS['egw']->session->appsession('sieve_session_data','',$sessionData);
		}
		
		function translate()
		{
			$this->t->set_var("lang_message_list",lang('Message List'));
			$this->t->set_var("lang_from",lang('from'));
			$this->t->set_var("lang_to",lang('to'));
			$this->t->set_var("lang_save",lang('save'));
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
			$this->t->set_var("lang_add_rule",lang('add rule'));
			$this->t->set_var("lang_add_script",lang('add script'));
			$this->t->set_var("lang_back",lang('back'));
			$this->t->set_var("lang_days",lang('days'));
			$this->t->set_var("lang_save_changes",lang('save changes'));
			$this->t->set_var("lang_edit_rule",lang('edit rule'));
			$this->t->set_var("lang_edit_vacation_settings",lang('edit vacation settings'));
			$this->t->set_var("lang_every",lang('every'));
			$this->t->set_var('lang_respond_to_mail_sent_to',lang('respond to mail sent to'));
			$this->t->set_var('lang_filter_rules',lang('filter rules'));
			$this->t->set_var('lang_vacation_notice',lang('vacation notice'));
			$this->t->set_var("lang_with_message",lang('with message'));
			$this->t->set_var("lang_script_name",lang('script name'));
			$this->t->set_var("lang_script_status",lang('script status'));
			$this->t->set_var("lang_delete_script",lang('delete script'));
			$this->t->set_var("lang_check_message_against_next_rule_also",lang('check message against next rule also'));
			$this->t->set_var("lang_keep_a_copy_of_the_message_in_your_inbox",lang('keep a copy of the message in your inbox'));
			$this->t->set_var("lang_use_regular_expressions",lang('use regular expressions'));
			$this->t->set_var("lang_match",lang('match'));
			$this->t->set_var("lang_all_of",lang('all of'));
			$this->t->set_var("lang_any_of",lang('any of'));
			$this->t->set_var("lang_if_from_contains",lang('if from contains'));
			$this->t->set_var("lang_if_to_contains",lang('if to contains'));
			$this->t->set_var("lang_if_subject_contains",lang('if subject contains'));
			$this->t->set_var("lang_if_message_size",lang('if message size'));
			$this->t->set_var("lang_less_than",lang('less than'));
			$this->t->set_var("lang_greater_than",lang('greater than'));
			$this->t->set_var("lang_kilobytes",lang('kilobytes'));
			$this->t->set_var("lang_if_mail_header",lang('if mail header'));
			$this->t->set_var("lang_file_into",lang('file into'));
			$this->t->set_var("lang_forward_to_address",lang('forward to address'));
			$this->t->set_var("lang_send_reject_message",lang('send a reject message'));
			$this->t->set_var("lang_discard_message",lang('discard message'));
			$this->t->set_var("lang_select_folder",lang('select folder'));

			$this->t->set_var("bg01",$GLOBALS['egw_info']["theme"]["bg01"]);
			$this->t->set_var("bg02",$GLOBALS['egw_info']["theme"]["bg02"]);
			$this->t->set_var("bg03",$GLOBALS['egw_info']["theme"]["bg03"]);
		}
		
		function updateRules()
		{
			$action 	= get_var('rulelist_action',array('POST'));
			$ruleIDs	= get_var('ruleID',array('POST'));
			$scriptName 	= get_var('scriptname',array('GET'));
			
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
			
			$this->editScript();
		}

		function updateScript()
		{
			$scriptName		= $this->scriptToEdit;
			$script			=& CreateObject('felamimail.Script',$this->scriptToEdit);

			if(!empty($scriptName))
			{
				if($this->sieve->getscript($scriptName))
				{
					// fetch the rules to the internal structure inside
					// the $script object
					if (!$script->retrieveRules($this->sieve))
					{
						#print "can't receive script<br>";
						$this->editScript();
					}
				}
				else
				{
					#print "Unable to change active script!<br>";
					/* we could display the full output here */
					$this->listScripts();
					$GLOBALS['egw']->common->egw_exit();
				}
			}

			$script->rules		= $this->rules;
			if (!$script->updateScript($this->sieve)) 
			{
				print "update failed<br>";
				print $script->errstr."<br>";
			}
		}
		
		function updateVacation()
		{
			#phpinfo();exit;

			$scriptName = get_var('scriptname',array('GET'));
 			$script =& CreateObject('felamimail.Script',$scriptName);

			if(!empty($scriptName))
			{
				if($this->sieve->getscript($scriptName))
				{
					// fetch the rules to the internal structure inside
					// the $script object
					if (!$script->retrieveRules($this->sieve))
					{
						#print "can't receive script<br>";
						$this->editScript();
					}
				}
				else
				{
					#print "Unable to change active script!<br>";
					/* we could display the full output here */
					$this->listScripts();
					$GLOBALS['egw']->common->egw_exit();
				}
			}

			switch(get_var('vacationRule_action',array('POST')))
			{
				case 'enable':
				case 'save':
					$vacation['text']	= get_var('vacation_text',array('POST'));
					$vacation['text']	= $this->botranslation->convert($vacation['text'],$this->displayCharset,'UTF-8');
					$vacation['days']	= get_var('days',array('POST'));
					$vacation['addresses']	= get_var('vacationAddresses',array('POST'));
					$vacation['status']	= 'on';
					if($this->checkRule($vacation))
					{
						$script->vacation	= $vacation;
						if (!$script->updateScript($this->sieve)) 
						{
							print "update failed<br>";
							print $script->errstr."<br>";
						}
					}
					break;
				
				case 'disable':
					$vacation['text']	= get_var('vacation_text',array('POST'));
					$vacation['days']	= get_var('days',array('POST'));
					$vacation['addresses']	= get_var('vacationAddresses',array('POST'));
					$vacation['status']	= 'off';
					$script->vacation	= $vacation;
					if($this->checkRule($vacation))
					{
						if (!$script->updateScript($this->sieve)) 
						{
							print "update failed<br>";
							print $script->errstr."<br>";
						}
					}
					break;
				
				case 'delete':
					$script->vacation	= array();
					if (!$script->updateScript($this->sieve)) 
					{
						print "update failed<br>";
						print $script->errstr."<br>";
					}
					break;

				default:
					print "unhandeld vacationRule_action:". get_var('vacationRule_action',array('POST')) ."<br>";
					break;
			}
			
			$this->editScript();
		}
	}
?>
