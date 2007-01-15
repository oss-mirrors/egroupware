<form action="<?=$this->report_url?>" method="post" name="report_actie">&nbsp;
   <div style="padding:0px;width:700px;xxbackground-color:#dedede;">
	  <strong><?=lang('Reports')?></strong>
	  <table  cellpadding="0" cellspacing="0" >
		 <tr>
			<td style="">
			   <select name='report' >
				  <?=$this->listoptions?>
			   </select>
			   <input type='button'  class="egwbutton" value="<?=lang('Merge')?>" onClick="parent.window.location.href='<?=$this->report_url2?>&report_id='+document.report_actie.report.value +'&selvalues=' +returnSelectedCheckbox()">

			   <script>
				  function reportdialog(action)
				  {
						if(action=="edit")
						{
							  var openlink='<?=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uireport.edit_report_popup&parent_site_id='.$this->site_id.'&obj_id='.$this->object_id.'&table_name='.$this->table_name.'&report_id=')?>'+document.report_actie.report.value;

						}
						else if(action=="newfrom")
						{
							  var openlink='<?=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uireport.add_report_from_selected&obj_id='.$this->object_id.'&parent_site_id='.$this->site_id.'&table_name='.$this->table_name.'&report_id=')?>'+document.report_actie.report.value;
						}

						parent.window.location.href=openlink;

				  }
			   </script>

			   <?php if($GLOBALS['phpgw_info']['user']['apps']['admin']):?>
			   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			   <?=lang('Design reports')?>&nbsp;<input class="egwbutton" type="button" value="<?=lang('Edit')?>" onClick="reportdialog('edit');">
			   <input class="egwbutton" type="button" value="<?=lang('New from selected')?>" onClick="reportdialog('newfrom');">	
			   <input class="egwbutton" type="button" value="<?=lang('New report')?>" onClick="parent.window.location.href='<?=$this->add_report_url?>'">
			   <?php endif?>
			</td>
		 </tr>
	  </table>
   </div>
</form>	
