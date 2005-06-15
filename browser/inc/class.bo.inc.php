<?php
	/**************************************************************************\
	* eGroupWare - browsereton Application                                        *
	* http://www.egroupware.org                                                *
	* -----------------------------------------------                          *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class bo
	{
		var $so;

		var $debug = false;

		var $public_functions = array(
			'somebusinessfunc'	=> true,
		);

		function bo($session = false)
		{
			//$this->so = createobject('browser.so');

            if($session)
            {
                $this->read_sessiondata();
                $this->use_session = true;
            }
		}

        function save_sessiondata($data = '')
        {
            if ($this->use_session)
            {
				if(empty($data) || !is_array($data))
				{
					$data = array();
				}
                if($this->debug) { echo '<br>Save:'; _debug_array($data); }
                $GLOBALS['phpgw']->session->appsession('session_data','browser_info',$data);
            }
        }

        function read_sessiondata()
        {
            $data = $GLOBALS['phpgw']->session->appsession('session_data','browser_info');
            if($this->debug) { echo '<br>Read:'; _debug_array($data); }
        }

		function somebusinessfunc()
		{
			// get some data through $so, then apply some business logic to it
		}

	}
?>
