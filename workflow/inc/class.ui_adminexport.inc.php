<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_adminexport extends workflow
	{
		var $public_functions = array(
			'form'	=> true
		);

		var $process_manager;

		var $activity_manager;

		function ui_adminexport()
		{
			parent::workflow();
			$this->process_manager	= CreateObject('phpgwapi.workflow_processmanager');
			$this->activity_manager	= CreateObject('phpgwapi.workflow_activitymanager');
			$this->role_manager		= CreateObject('phpgwapi.workflow_rolemanager');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Export');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('admin_export', 'admin_export.tpl');

			if (!$this->wf_p_id) die(lang('No process indicated'));
			
			// retrieve process info
			$proc_info = $this->process_manager->get_process($this->wf_p_id);

			// check process validity and show errors if necessary
			$proc_info['isValid'] = $this->show_errors($this->activity_manager, $error_str);

						// fill proc_bar
			$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));

			$out = '';
			$normalized_name = $proc_info['wf_normalized_name'];
			$filename = GALAXIA_PROCESSES.SEP.$normalized_name.SEP.$normalized_name.".xml";
			if (isset($_POST['save'])) {
				$filename = get_var('exportfile', 'any', $filename);
				$out = $this->process_manager->serialize_process($this->wf_p_id);
				
				//echo "out = $out";
				
    			$fp = fopen($filename,"w");
				
				fwrite($fp, $out);
    			fclose($fp);
			}
			$this->t->set_var('value_initial_filename', $filename);	
			
			
			$this->t->set_var(array(
				'message'				=> implode('<br>', $this->message),
				'errors'				=> $error_str,
				'lang_export_a_process'	=>lang('Export a process'),
				'lang_select_export_file'	=> lang('Export File'),
				'form_action_adminexport'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminexport.form'),
				'p_id'					=> $this->wf_p_id,
			));

			$this->translate_template('admin_export');
			$this->t->pparse('output', 'admin_export');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

	}
?>
