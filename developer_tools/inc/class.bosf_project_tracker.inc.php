<?php

	class bosf_project_tracker
	{
		var $so;

		function bosf_project_tracker()
		{
			$this->so       = createobject('developer_tools.sosf_project_tracker',7305);
		}

		function display_tracker()
		{
			$last_cache = $this->so->grab_cache_time();
			$data       = $this->so->grab_tracker_from_http();

			return $data;
		}

	}
