<?php

	class sosf_project_tracker
	{
		var $db;
		var $group_id;

		function sosf_project_tracker($group_id)
		{
			global $phpgw;

			$this->group_id = $group_id;
			$this->db       = $phpgw->db;
		}

		function insert_cache($data)
		{
//			$this->db->local('phpgw_devtools_sf_cache');
			$this->db->query("delete from phpgw_devtools_sf_cache where cache_id='" . $this->group_id . "'",__LINE__,__FILE__);
			$this->db->query("insert into phpgw_devtools_sf_cache values ('" . $this->group_id . "','" . time() . "','" . addslashes($data) . "')",__LINE__,__FILE__);
//			$this->db->unlock();
		}

		function grab_cache_time()
		{
			$this->db->query("select cache_timestamp from phpgw_devtools_sf_cache where cache_id='" . $this->group_id . "'",__LINE__,__FILE__);
			$this->db->next_record();

			return $this->db->f('cache_timestamp');
		}

		function grab_tracker_from_db()
		{
			$this->db->query("select cache_content from phpgw_devtools_sf_cache where cache_id='" . $this->group_id . "'",__LINE__,__FILE__);
			$this->db->next_record();

			return $this->db->f('cache_content');
		}

		function grab_tracker_from_http()
		{
			global $HTTP_SERVER_VARS, $phpgw_info;

			$network = createobject('phpgwapi.network');
			$lines   = $network->gethttpsocketfile('http://sourceforge.net/export/projhtml.php?group_id=' . $this->group_id . '&mode=compat&no_table=0');
	
			while (list(,$line) = each($lines))
			{
				if ($html_found)
				{
					$data .= $line;
				}
			
				if (ereg('Connection: close',$line))
				{
					$html_found = True;
				}
			}

			// If there running in SSL mode, replace the icons with local ones
			// This might not work with the CGI binary or webservers other then apache.
			// I might move this part over to bo or ui, I am not sure yet. (jengo)
			if ($HTTP_SERVER_VARS['HTTPS'])
			{
				$data = ereg_replace('http://a248.e.akamai.net/7/248/1710/949111342/sourceforge.net/images/ic',PHPGW_IMAGES,$data);
			}
			$data = ereg_replace('#EAECEF',$phpgw_info['theme']['row_off'],$data);

			$this->insert_cache($data);

			return $data;
		}
	}
