<?php
	/***************************************************************************\
	* eGroupWare - mydms                                                        *
	* http://www.linux-at-work.de                                               *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License.                *
	\***************************************************************************/
	
	/* $Id$ */

	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.DBAccess.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.AccessUtils.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassUser.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassFolder.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassDocument.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.FileUtils.php');

	class bomydms
	{
		function addACL($_documentID, $_userID, $_groupID, $_access)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_ALL)	return false;
			
			if($_userID !== false)
			{
				$document->addAccess($_access, $_userID, true);
			}

			if($_groupID !== false)
			{
				$document->addAccess($_access, $_groupID, false);
			}
		}
	
		function addNotification($_documentID, $_userID, $_groupID)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_READWRITE)	return false;
			
			if($_userID !== false)
			{
				$document->addNotify($_userID, true);
			}

			if($_groupID !== false)
			{
				$document->addNotify($_groupID, false);
			}
		}
	
		function deleteACL($_documentID, $_userID, $_groupID)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_ALL)	return false;
			
			if($_userID !== false)
			{
				$document->removeAccess($_userID, true);
			}

			if($_groupID !== false)
			{
				$document->removeAccess($_groupID, false);
			}
		}

		function deleteDocument($_documentID)
		{
			if(!$_documentID)	return false;
			
			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_ALL)	return false;
			
			if (!$document->remove())
			{
				return false;
			}
			
			return true;
		}

		function deleteFile($_documentID, $_version)
		{
			if(!$_documentID || !$_version)	return false;
			
			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_ALL)	return false;
			
			if(!$version  = $document->getContentByVersion($_version))
			{
				return false;
			}
			
			if (!$version->remove())
			{
				return false;
			}
			
			return true;
		}

		function deleteNotification($_documentID, $_userID, $_groupID)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_READWRITE)	return false;
			
			if($_userID !== false)
			{
				$document->removeNotify($_userID, true);
			}

			if($_groupID !== false)
			{
				$document->removeNotify($_groupID, false);
			}
		}
		
		function inheritACL($_documentID, $_action, $_mode)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_ALL)	return false;
			
			switch($_action)
			{
				case 'notinherit':
					switch($_mode)
					{
						case 'copy':
							$defAccess = $document->getDefaultAccess();
							$document->setInheritAccess(false);
							$document->setDefaultAccess($defAccess);
							
							$folder = $document->getFolder();
							$accessList = $folder->getAccessList();
							
							foreach ($accessList["users"] as $userAccess)
								$document->addAccess($userAccess->getMode(), $userAccess->getUserID(), true);
								
							foreach ($accessList["groups"] as $groupAccess)
								$document->addAccess($groupAccess->getMode(), $groupAccess->getGroupID(), false);
							                                
							return true;
							
							break;
							
						case 'empty':
							$defAccess = $document->getDefaultAccess();
							$document->setInheritAccess(false);
							$document->setDefaultAccess($defAccess);
							                                
							return true;
							
							break;
					}
					break;

				case 'inherit':
					$document->clearAccessList();
					$document->setInheritAccess(true);
					
					return true;
						
					break;
			}
			
			return false;
		}
		
		function moveDocument($_documentID, $_targetID)
		{
			if(!$_documentID || !$_targetID)	return false;
			
			$document	= getDocument($_documentID);
			
			$oldFolder	= $document->getFolder();
			$targetFolder	= getFolder($_targetID);


			$accessModeDocument	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			$accessModeFolder	= $targetFolder->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if (($accessModeDocument < M_READWRITE) || ($accessModeFolder < M_READWRITE))
				return false;
			
			if ($document->setFolder($targetFolder))
			{
				return true;
			}
			
			return false;
		}

		function setDefaultAccess($_documentID, $_mode)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_ALL)	return false;

			$document->setDefaultAccess($_mode);
			
			return true;
		}
	
		function updateACL($_documentID, $_userID, $_groupID, $_access)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
			
			if($accessMode < M_ALL)	return false;
			
			if($_userID !== false)
			{
				$document->changeAccess($_access, $_userID, true);
			}

			if($_groupID !== false)
			{
				$document->changeAccess($_access, $_groupID, false);
			}
		}

		function updateDocument($_documentID, $_fileName, $_comment, $_keywords, $_expireDate)
		{
			if(!$_documentID)		return false;
			
			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));

			if($accessMode < M_READWRITE)	return false;
			
			if ( (($document->getName() == $_fileName) || $document->setName($_fileName))
				&& (($document->getComment() == $_comment) || $document->setComment($_comment))
				&& (($document->getKeywords() == $_keywords) || $document->setKeywords($_keywords))
				&& (($document->getExpires() == $_expireDate) || $document->setExpires($_expireDate))
			#	&& (($sequence == "keep") || $document->setSequence($sequence))
			)
			{
				return true;
			}
			
			return false;
		}

		function updateFile($_documentID, $_userfile, $_comment, $_expireDate)
		{
			if(!$_documentID)		return false;
			
			$user		= getUser($GLOBALS['egw_info']['user']['account_id']);
			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode($user);

			if($accessMode < M_READWRITE)	return false;
			
			if ($document->isLocked())
			{
				$lockingUser = $document->getLockingUser();
				
				if (($lockingUser->getID() != $user->getID()) && ($document->getAccessMode($user) != M_ALL))
				{
					return false;
				}
				else
				{
					$document->setLocked(false);
				}
			}
			
			if(is_uploaded_file($_userfile['tmp_name']))
			{
				$lastDotIndex = strrpos(basename($_userfile['name']), ".");
				if (is_bool($lastDotIndex) && !$lastDotIndex)
					$fileType = ".";
				else
					$fileType = substr($_userfile['name'], $lastDotIndex);

				if (!$document->addContent($_comment, $user, $_userfile['tmp_name'], basename($_userfile['name']), $fileType, $_userfile['type']))
				{
					return false;
				}

				if(!$document->setExpires($_expireDate))
				{
					return false;
				}

				return true;
			}
			
			return false;
		}
		
		function updateLockStatus($_documentID, $_lockStatus)
		{
			if(!$_documentID)	return false;
			
			$user		= getUser($GLOBALS['egw_info']['user']['account_id']);
			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode($user);

			if($accessMode < M_READWRITE)	return false;
			
			if($_lockStatus == 'locked')
			{
				if ($document->isLocked())
				{
					// take over lock
					if (($lockingUser->getID() == $user->getID()) || ($accessMode == M_ALL))
					{
						if ($document->setLocked($user))
						{
							return true;
						}
					}
				}
				else
				{
					if ($document->setLocked($user))
					{
						return true;
					}
				}
			}
			elseif($_lockStatus == 'unlocked')
			{
				$lockingUser = $document->getLockingUser();
				
				if (($lockingUser->getID() == $user->getID()) || ($accessMode == M_ALL))
				{
					if ($document->setLocked("false"))
					{
						return true;
					}
				}
			}
			
			return false;
		}
	}
?>