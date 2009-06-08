<?php

include_once 'Horde/SyncML/State.php';

/**
 * The Horde_SyncML_Command class provides a super class fo SyncBody commands.
 *
 * $Horde: framework/SyncML/SyncML/Command.php,v 1.4 2004/07/03 15:26:46 chuck Exp $
 *
 * Copyright 2003-2004 Anthony Mills <amills@pyramid6.com>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Anthony Mills <amills@pyramid6.com>
 * @version $Revision$
 * @since   Horde 3.0
 * @package Horde_SyncML
 */
class Horde_SyncML_Command {

    var $_cmdID;

    var $_xmlStack;

    var $_chars;

    function &factory($command, $params = null)
    {
        include_once 'Horde/SyncML/Command/' . $command . '.php';
        $class = 'Horde_SyncML_Command_' . $command;
        if (class_exists($class)) {
            return $cmd = new $class($params);
        } else {
            Horde::logMessage('SyncML: Class definition of ' . $class . ' not found.', __FILE__, __LINE__, PEAR_LOG_ERR);
            require_once 'PEAR.php';
            return PEAR::raiseError('Class definition of ' . $class . ' not found.');
        }
    }

    function output($currentCmdID, $output)
    {
    }

    function startElement($uri, $localName, $attrs)
    {
        $this->_xmlStack++;
    }

    function endElement($uri, $element)
    {
        switch ($this->_xmlStack) {
        case 2:
            if ($element == 'CmdID') {
                $this->_cmdID = intval(trim($this->_chars));
            }
            break;
        }

        if (isset($this->_chars)) {
            unset($this->_chars);
        }

        $this->_xmlStack--;
    }

    function characters($str)
    {
    	$tempValue = trim($str);
    	
    	if(empty($tempValue)) return;
    	
        if (isset($this->_chars)) {
            $this->_chars = $this->_chars . $str;
        } else {
            $this->_chars = $str;
        }
    }

}
