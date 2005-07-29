<?php
	require_once(dirname(__FILE__) . SEP . 'class.bo_user_forms.inc.php');

	class ui_userviewinstance extends bo_user_forms
	{
		var $public_functions = array(
			'form'	=> true
		);

		var $GUI;
		var $instance_manager;

		function ui_userviewinstance()
		{
			parent::bo_user_forms('user_viewinstance');
			
			$this->GUI		=& CreateObject('workflow.workflow_gui');
			$this->instance_manager	=& CreateObject('workflow.workflow_instancemanager');
		}

		function form()
		{
			$iid = get_var('iid', 'any', 0);
			$instance =& CreateObject('workflow.workflow_instance');
			if($iid != 0)
			{
				$instance->getInstance($iid);
				$inst_parser	=& CreateObject('workflow.bo_uiinstance', $this->t);
				//this is necessary the CreateObject did not use ref parameters
				$inst_parser->t =& $this->t;
				
				//$parser->parse_history($instance);
				$inst_parser->parse_instance($instance);
				$inst_parser->parse_instance_history($instance->workitems);
				
				$this->t->set_var(array(
					'instance'	=> $this->t->parse('output', 'instance_tpl'),
					'history'	=> $this->t->parse('output', 'history_tpl'),
				));
			}
			else
			{
				//echo lang('no instance given, nothing to show');
				//$GLOBALS['phpgw']->common->phpgw_exit();
				$this->message[] = lang('no instance given, nothing to show');
			}
			
			// fill the table
			//$this->fill_table($processes['data'],$processes['cant']);
			$this->show_user_tabs($this->class_name);
			$this->fill_form_variables();
			$this->finish();
		}
		
	}
?>
