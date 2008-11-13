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
			//tim   delete links
			$linksDoc = $document->getLinksDocument();
			foreach ($linksDoc as $links)
			{
				$document->removeDocumentLink($links->getID());
			}
			//----
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

		//tim
		function deleteLink($_documentID, $_linkid)
		{
			if(!$_documentID || !$_linkid)	return false;
			if(!is_numeric($_linkid)) return false;

			$document		= getDocument($_documentID);
			$link 			= getDocumentLink($_linkid);
			$responsibleUser	= $link->getUser();
			$userID			= $GLOBALS['egw_info']['user']['account_id'];
			$accessMode		= $document->getAccessMode(getUser($userID));

			if (
				($accessMode < M_READ)
				|| (($accessMode == M_READ) && ($responsibleUser->getID() != $userID))
				|| (($accessMode > M_READ) && (!getUser($userID)->isAdmin()) && ($responsibleUser->getID() != $userID) && !$link->isPublic())
   			   ) return false;

			if (!$document->removeDocumentLink($_linkid)) 	return false;

			return true;
		}
		//---

		//tim
		function addLink($_documentID, $_docid, $_public)
		{
			if(!$_documentID || !$_docid)	return false;
			if(!is_numeric($_docid)) return false;

			$docid		= $_docid;
			$document	= getDocument($_documentID);
			$userID		= $GLOBALS['egw_info']['user']['account_id'];
			$accessMode	= $document->getAccessMode(getUser($userID));
			$public 	= (isset($_public) && $_public == "true") ? true : false;

			if ($accessMode < M_READ) return false;
			if ($public && ($accessMode == M_READ)) $public = false;
			if (!$document->addDocumentLink($docid, $userID, $public)) return false;

			return true;
		}
		//---

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

		function setOwner($_documentID, $_owner)
		{
			if(!$_documentID)       return false;

			$document	= getDocument($_documentID);
			$accessMode	= $document->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));

			if($accessMode < M_ALL)	return false;

			$document->setOwner(getUser($_owner));

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
				$lastDotIndex = strrpos(_basename($_userfile['name']), ".");
				if (is_bool($lastDotIndex) && !$lastDotIndex)
					$fileType = ".";
				else
					$fileType = substr($_userfile['name'], $lastDotIndex);

				if (!$document->addContent($_comment, $user, $_userfile['tmp_name'], _basename($_userfile['name']), $fileType, $_userfile['type']))
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


		//tim
		//hooks search_link----------------------------------------------------------------------------
		/**
		* @author	Tim Usach
		* @param string $mode = "and","or"
		**/

		function searchFilter($document,$query,$mode="or")
		{
			$str = "";
			$str .= $document->getKeywords() . " ";
			$str .= $document->getName() . " ";
			$str .= $document->getComment();

			$querywords = split(" ", strtolower($query));
			$keywords = split(" ", strtolower($str));

			$hitsCount = 0;
			foreach ($querywords as $queryword)
			{
				$found = false;

				foreach ($keywords as $keyword)
				{
					if ((substr_count($keyword, $queryword) > 0) || ($queryword == "%"))
					{
						$found = true;
						if ($mode == "or") return true;
					}
				}
				if ($mode == "and" && !$found)
					return false;
			}
			if ($mode == "and")
				return true;
			else
				return false;
		}


		function searchInFolder($query, $folder)
		{
			 $results = array();
			//GLOBAl $search_results;
			$user = getUser($GLOBALS['egw_info']['user']['account_id']);

			$documents = $folder->getDocuments();
			$documents = filterAccess($documents, $user, M_READ);
			$subFolders = $folder->getSubFolders();
			$subFolders = filterAccess($subFolders, $user, M_READ);

			foreach ($documents as $document)
			{
				if ($this->searchFilter($document, $query, "and")) array_push($results, $document);

			}

			foreach ($subFolders as $subFolder)
			{
				$results = array_merge($results,$this->searchInFolder($query, $subFolder));
			}

			return $results;
		}


		/**
	 	* Hook called by link-class to include infolog in the appregistry of the linkage
		*
		* @param array/string $location location and other parameters (not used)
		* @return array with method-names
		*/

		function search_link($location)
		{
			return array(
			'query'      => 'mydms.bomydms.link_query',
			'title'      => 'mydms.bomydms.link_title',
			'view'       => array('menuaction' => 'mydms.uimydms.viewDocument',),
			'view_id'    => 'documentid',
			);
		}

		/**
		 * get title  identified by $page
		 *
		* Is called as hook to participate in the linking
		*
		* @param string/object $page string with page-name or sowikipage object
		* @return string/boolean string with title, null if page not found or false if not view perms
		*/
		function link_title($id)
		{
			if (!is_array($id))
			{
			$doc = getDocument($id);
			}
			if(!$id) return $id;
			if(!$doc)
				return $doc;
			else
				return $doc->getName();
		}


		/**
		* query for pages matching $pattern
		*
		* Is called as hook to participate in the linking
		*
		* @param string $pattern pattern to search
		* @return array with info_id - title pairs of the matching entries
		*/
		function link_query($pattern)
		{
			$content = array();
			$docs = $this->searchInFolder($pattern,getFolder(1));
			foreach($docs  as $doc)
			{
				$content[$doc->getID()] = strip_tags($doc->getName());
			}
			return $content;
		}

	}
?>