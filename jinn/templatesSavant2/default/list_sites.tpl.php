<script type="text/javascript" >
   function submit_multi_del()
   {
		 if(countSelectedCheckbox()==0)
		 {
			   alert('<?=lang('You must select one or more sites for this function.')?>');
		 }
		 else
		 {

			   if(window.confirm('<?=lang('Are you sure you want to delete these selected Sites?')?>'))
			   {
					 document.frm.action.value='delete_mult_sites';
					 document.frm.submit();
			   }
			   else
			   {
					 document.frm.action.value='none';
			   }
		 }

   }

   function openhelp()
   {
		 window.open('<?=$this->helplink?>?referer='+encodeURI(location),this.target,'width=800,height=600,scrollbars=yes,resizable=yes'); 
		 return false; 
   }

</script>
<form action="" method="post" name="frm">
   <div>
	  <input style="float:left;" type="button" value="<?=lang('New Site')?>" class="egwbutton" onclick="location='<?=$this->link_add_site?>'" />
	  <input style="float:left;" type="button" value="<?=lang('Import Site from File')?>" class="egwbutton" onclick="location='<?=$this->link_import_site?>'" />
	  <input style="float:right;" type="button" value="<?=lang('Help')?>" class="egwbutton" onclick="openhelp()" />
   </div>
   <div style="clear:both;">
	  <input type="hidden" name="submitted" value="true">
	  <input type="hidden" name="action" value="true">
	  <table border="0" cellspacing="1" cellpadding="0" style="background-color:#ffffff;border:solid 1px #cccccc;margin:3px 0px 3px 0px;">
		 <tr>
			<td colspan="7" style="font-size:12px;font-weight:bold;padding:2px;border-bottom:solid 1px #006699" align="left"><?=lang('Sites');?></td>
		 </tr>
		 <tr>
			<td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Actions')?></td>
			<td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Name');?></td>
			<td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Production Database');?></td>
			<td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Development Database');?></td>
			<td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Objects');?></td>
			<td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('Site Version');?></td>
			<td style="background-color:#d3dce3;font-weight:bold;padding:3px;"><?=lang('JiNN Version');?></td>
		 </tr>

		 <?php if(count($this->site_records)>0):?>
		 <?php foreach($this->site_records as $site_row):?>
		 <?php 
			if($rowbg=='#e8f0f0') $rowbg='white';
			else $rowbg='#e8f0f0';
		 ?>
		 <tr valign="top">
			<td style="padding:0px 4px 0px 2px;width:90px;background-color:<?=$rowbg?>" align="left">
			   <input style="border-style: none;" name="sitedel<?=$site_row[site_id]?>" value="<?=$site_row[site_id]?>" type="checkbox">
			   <a href="<?=$site_row[link_edit]?>" title="<?=lang('edit')?>"><img src=<?=$this->icon_edit?> alt="<?=lang('edit')?>" /></a>
			   <a href="<?=$site_row[link_del]?>" onClick="return window.confirm('<?=lang('Do you really want to delete this site?')?>');" title="<?=lang('delete')?>"><img src=<?=$this->icon_del?> alt="<?=lang('delete')?>"  /></a>
			   <a href="<?=$site_row[link_export]?>" title="<?=lang('export')?>" ><img src=<?=$this->icon_export?> alt="<?=lang('export')?>" /></a>
			</td>
			<td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$site_row[site_name]?></td>
			<td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$site_row[site_db_name]?></td>
			<td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$site_row[dev_site_db_name]?></td>
			<td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$site_row[num_objects]?></td>
			<td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$site_row[site_version]?></td>
			<td style="padding:0px 4px 0px 2px;background-color:<?=$rowbg?>" align="left"><?=$site_row[jinn_version]?></td>
		 </tr>
		 <?php endforeach?>
		 <?php endif?>
		 <tr>
			<td colspan="7" style="border-top:solid 1px #006699;height:1px;" ></td>
		 </tr>
		 <tr>
			<td align="left" style="width:90px;padding:0px 4px 0px 2px;background-color:#d3dce3">
			   <input title="toggle all above checkboxes" name="CHECKALL" id="CHECKALL" value="TRUE" onclick="doCheckAll(this)" type="checkbox" />
			   <a title="delete all selected records" href="javascript:submit_multi_del()"><img src="<?=$this->icon_del?>" alt="delete all selected records" width="16"></a></td>
			<td colspan="6" style="padding:0px 4px 0px 2px;background-color:#d3dce3"><?=lang('Actions to apply on all selected sites')?></td>

		 </tr>
	  </table>
   </form>
</div>
