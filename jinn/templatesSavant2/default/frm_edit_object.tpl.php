<?
if(is_array($this->fields))
{
?>
<form method="post" name="frm" action="<?= $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.boadmin.update_egw_jinn_object");?>" enctype="multipart/form-data" {form_attributes}>
   <?
}
   else
   {
   ?>
 	<form method="post" name="frm" action="<?= $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.boadmin.insert_egw_jinn_object");?>" enctype="multipart/form-data" {form_attributes}>
  

   <?
   }
?>
   <input type="hidden" name="where_key" value="<?= $this->where_key;?>">
   <input type="hidden" name="where_value" value="<?= $this->where_value;?>">
   <table align="" cellspacing="2" cellpadding="2" style="background-color:#ffffff;border:solid 1px #cccccc;">
	  <tr>
		 <td bgcolor=#DDDDDD valign="top"><?= lang('Object id');?></td>
		 <td bgcolor=#DDDDDD><input type="hidden" name="FLDobject_id" value="<?=$this->where_value;?>"><?=$this->where_value;?></td>
	  </tr>
	  <tr>
		 <td bgcolor=#E8F0F0 valign="top"><?= lang('Parent site id');?></td>
		 <td bgcolor=#E8F0F0><input type=hidden name="FLDparent_site_id" value="<?=$this->global_values['parent_site_id'];?>"><?=$this->global_values['parent_site_id'];?></td>
	  </tr>
	  <tr>
		 <td bgcolor=#DDDDDD valign="top"><?= lang('Name');?></td>
		 <td bgcolor=#DDDDDD><input type="text" name="FLDname" size="40" input_max_length value="<?= $this->global_values['name']?>"></td>
	  </tr>
	  <tr>
		 <td bgcolor=#E8F0F0 valign="top"><?= lang('Table name');?></td>
		 <td bgcolor=#E8F0F0>
			<select name="FLDtable_name">
			   <?
			   foreach($this->tables as $table)
			   {
				  if($table['table_name']==$this->global_values[table_name])
				  {
				  ?>
				  <option value="<?=$table['table_name'];?>" selected="selected"><?=$table['table_name'];?></option>
				  <?
				  }
				  else
				  {
				  ?>
			   			<option value="<?=$table['table_name'];?>"><?=$table['table_name'];?></option>
				  <?
			   }
			}
		 ?>
	  </select>
   </td>
</tr>
<tr>
   <td bgcolor=#DDDDDD valign="top"><?= lang('Upload path');?></td>
   <td bgcolor=#DDDDDD><input type="text" name="FLDupload_path" size="40" $input_max_length value="<?=$this->global_values['upload_path']?>"></td>
</tr>
<tr>
   <td bgcolor=#E8F0F0 valign="top"><?= lang('Development (Test) site upload path');?></td>
   <td bgcolor=#E8F0F0><input type="text" name="FLDdev_upload_path" size="40" input_max_length value="<?=$this->global_values['dev_upload_path']?>"></td>
</tr>
<tr>
   <td bgcolor=#DDDDDD valign="top"><?= lang('Max. records');?></td>
   <td bgcolor=#DDDDDD>
	  <select name="FLDmax_records">
		 <?
		 if($this->global_values['max_records'] == 1)
		 {
	  	 ?>
		 	<option value=""><?= lang('unlimited');?></option>
			<option  value="1"selected="selected"><?= lang('only one');?></option>
	  	 <?	 
	  	 }
		 else
		 {	
		 ?>
		 	<option value=""  selected="selected"><?= lang('unlimited');?></option>
			<option  value="1"><?= lang('only one');?></option>			 
		 <?
		 }
	  	 ?>
	  </select>
   </td>
</tr>
<tr>
   <td bgcolor=#E8F0F0 valign="top"><?= lang('Object serialnumber');?></td>
   <td bgcolor=#E8F0F0><input type="hidden" name="FLDserialnumber" value="<?=$this->global_values['serialnumber']?>"><?=$this->global_values['serialnumber']?></td>
</tr>
<tr>
   <td bgcolor=#DDDDDD valign="top"><?= lang('Hide from object menu');?></td>
   <td bgcolor=#DDDDDD>
	  <select name="FLDhide_from_menu">
		 <?
		 if($this->global_values['hide_from_menu'] == 1)
		 {
		 ?>
		 <option value=""><?= lang('No');?></option>
		 <option  value="1" selected="selected"><?= lang('Yes, hide from menu');?></option>
		 <?
	  }
	  else
	  {

	  ?>
	  <option value=""selected="selected"><?= lang('No');?></option>
		 <option  value="1" ><?= lang('Yes, hide from menu');?></option>

	  <?
   }
?>
	  </select>
   </td>
</tr>
<tr>
   <td bgcolor=#E8F0F0 valign="top"><?= lang('Upload url');?></td>
   <td bgcolor=#E8F0F0><input type="text" name="FLDupload_url" size="40" input_max_length value="<?=$this->global_values['upload_url']?>"></td>
</tr>
<tr>
   <td bgcolor=#DDDDDD valign="top"><?= lang('Dev upload url');?></td>
   <td bgcolor=#DDDDDD><input type="text" name="FLDdev_upload_url" size="40" input_max_length value="<?=$this->global_values['dev_upload_url']?>"></td>
</tr>
<tr>
   <td bgcolor=#E8F0F0 valign="top"><?= lang('Extra where sql filter');?></td>
   <td bgcolor=#E8F0F0><textarea name="FLDextra_where_sql_filter" cols="60" rows="2"><?=$this->global_values['extra_where_sql_filter']?></textarea></td>
</tr>
<tr>
   <td bgcolor=#DDDDDD valign="top"><?= lang('Events config');?></td>
   <td bgcolor=#DDDDDD><input type="button" value="Object Events Configuration" onClick="parent.window.open('<?=$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','screen=objeventsconf').'&object_id='.$this->global_values['object_id']?>', 'pop', 'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"/></td>
</tr>
<tr>
   <td bgcolor=#E8F0F0 valign="top"><?= lang('Reports');?></td>
   <td bgcolor=#E8F0F0 >
	  <select id='rapport_list'>
		 <?=$this->rapport_list;?>
	  </select>
	  <input type='button' value='<?=lang('Edit');?>' onClick="parent.window.open('<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.edit_report_popup&parent_site_id='.$this->global_values['parent_site_id'].'&table_name='.$this->global_values['table_name'].'&report_id=');?>'+document.frm.rapport_list.value, 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')">
	  
	  <input type='button' value='<?=lang('Delete');?>' onClick="if(window.confirm('<?=lang('Are you sure?');?>'))location='<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boreport.delete_report').'&report_id=';?>'+document.frm.rapport_list.value;">
	 
	  <input type="button" value="<?=lang('Add');?>" onClick="parent.window.open('<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.add_report_popup').'&parent_site_id='.$this->global_values['parent_site_id'].'&table_name='.$this->global_values['table_name'].'&obj_id='.$this->global_values['unique_id'];?>', 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')"/></td>
</tr>

<tr>
   <td bgcolor=#DDDDDD valign="top"><?= lang('Unique id');?></td>
   <td bgcolor=#DDDDDD><input readonly type="text" name="FLDunique_id" value="<?=$this->global_values['unique_id']?>"/></td>
</tr>
<?
	   if(is_array($this->fields))
			   {

?>
<tr>
   <td colspan="2" style="background-color:#E8F0F0"  valign="top"><strong><?= lang('Field configuration');?></strong>
	 		 <div id="divPlugins">
			<table border="0" style="background-color:#ffffff;border:solid 1px #cccccc;">
			   <tr>
				  <td><?=lang('field');?></td>
				  <td><?=lang('Field Plugin');?></td>			
				  <td><?=lang('configure field plugin');?></td>			
				  <td><?=lang('information');?></td>			
				  <td><?=lang('mandatory');?></td>			
				  <td><?=lang('show by default in listview');?></td>			
				  <td><?=lang('Show in form')?></td>
				  <td><?=lang('field position');?></td>			
			   </tr>
			   <?
			   $i=0;
					   foreach($this->fields as $obj){
			   ?>
			   <tr><td ><?=$obj['name'];?></td>
				  <td>
					 <select name="FIELD_<?=$obj['name']?>_PLG">
						<?
							echo($this->plugins[$i]);
							
					 	?>
					 </select>

				  </td>
				  <td>
					 <input type="hidden" name="FIELD_<?=$obj['name']?>_PLC" value="<?=$this->hidden_value;?>">
					 <input type="button" onClick="<?=$this->pop_plug[$i];?>" value="<?=lang('configure field plugin');?>">
				  </td>
				  <td>
					 <input type="button" onClick="<?=$this->pop_name[$i];?>" value="<?=lang('name and help info');?>">
				  </td>
				  <td>

					 <input type="hidden" name="FIELD_<?=$obj['name']?>_MAN" value="0">
					 <input type="checkbox" name="FIELD_<?=$obj['name']?>_MAN" style="color:red" value="1" <?=$this->mandatory[$i];?>>
				  </td>
				  <td>
					 <input type="hidden" name="FIELD_<?=$obj['name']?>_DEF" value="0">
					 <input type="checkbox" name="FIELD_<?=$obj['name']?>_DEF" style="color:red" value="1" <?=$this->default[$i]?>>
				  </td>
				  					<td>
						<input type="hidden" name="FIELD_<?=$obj['name']?>_SHW" value="0">
						<input type="checkbox" name="FIELD_<?=$obj['name']?>_SHW" style="color:red" value="1" <?=$this->show_frm[$i]?>>
								  </td>
				  <td>
					 <input type="text" name="FIELD_<?=$obj['name']?>_POS" value="<?=$this->position[$i]?>">

				  </td>
			   </tr>
			   <?
				$i++;
			 
		  }
				?>
		</table>
		 </div>
	  
	  </div>

</td></tr>
 <tr><td style="background-color:#DDDDDD" bgcolor=#DDDDDD valign="top"><?= lang('relations');?></td><td bgcolor=#DDDDDD>
	  		   <?=$this->code?>
				   <?
				   $relations= $this->relations;
				   $i =1;
				   if(count($relations)>0)
				   {
				   foreach($relations as $rel_cat)
				   {
					  foreach($rel_cat as $rel)
					  {
						 echo $rel
					  ?>
					  <input type=checkbox name="DELrelation<?=$i?>" value="<?=$i;?>"><?=lang('delete')?><br/><br/>
					  
					  <?
					  $i++;
				   }}
			  }
			   ?>
			   <b><?=lang('Add new ONE TO MANY');?></b><br/>
			 <table>
				<tr>
				   <td colspan="2"><?=lang('field');?><br/>
					  <select name="1_relation_org_field">
						  <?=$this->rel1_options1;?>		
				   </select></td>
				</tr>
				<tr>
				   <td><?=lang('has a ONE TO MANY relation with:');?><br/>

					  <select name="1_relation_table_field">
						 <?=$this->rel1_options2;?>
						
				   </select>&nbsp;&nbsp;<?=lang('default value:');?><input type="text" name="1_default"/></td>
				</tr>
				<tr>
				   <td colspan="2"><?=lang('field to display:');?><br/>

					  <select name="1_display_field">
							 <?=$this->rel1_options2;?>
					 						
					  </select>
					  <select name="1_display_field_2">
								 <?=$this->rel1_options2;?>
				 						 					  </select>

					  <select name="1_display_field_3">
									 <?=$this->rel1_options2;?>
			 											  </select>
				   </td>
				</tr>
			 </table>
			 <br/>
			 <b><?=lang('Add new MANY TO MANY relation');?></b><br/>

			 <table>
				<tr>
				   <td colspan="2">
					  <?=lang(' The identifyer from this table (tp_categories.id) represented by:');?><br/>
					  <select name="2_relation_via_primary_key">
						 <?=$this->rel2_options1;?>
						 
				   </select></td>
				</tr>
				<tr>
				   <td><?=lang('has a MANY TO MANY relation with:');?><br/>
					  <select name="2_relation_foreign_key">
						 <?=$this->rel2_options2;?>
						
				   </select></td>
				</tr>
				<tr>
				   <td colspan="2"><?=lang('represented by:');?><br/>
					  <select name="2_relation_via_foreign_key">
						 <?=$this->rel2_options3;?>
						
				   </select></td>

				</tr>
				<tr>
				   <td><?=lang('showing:');?><br/>
					  <select name="2_display_field">
							 <?=$this->rel2_options3;?>
						  </select>
					  <select name="2_display_field_2">
						 <?=$this->rel2_options3;?>
						  </select>
					  <select name="2_display_field_3">
							 <?=$this->rel2_options3;?>
						  </select>
				   </td>

				</tr>
			 </table>
			 <b><?=lang('Add new one-to-one relation');?></b><br/>
			 <table>
				<tr>
				   <td colspan="2"><?=lang('field:');?><br/>
					  <select name="3_relation_org_field">
						 <?=$this->rel3_options1;?>
						
				</select></td></tr>

				<tr><td><?=lang('has a ONE-TO-ONE relation with:');?><br/>
					  <select name="3_relation_table_field">
						 <?=$this->rel3_options2;?>
							
				</select></td></tr>

				<tr><td colspan="2"><?=lang('Using object configuration:');?><br/>
					  <select name="3_relation_object_conf">
						 <?=$this->rel3_options3;?>
						
   </select></td>

				</tr>
			 </table>
			 <br/>
	   </td></tr>
 </td></tr>
 <?}?>
<tr>

	<td align="center" colspan="2">


	   <input type="submit" name="continue" value="<?= lang('save and contiue');?>" />
	   <input type="submit" name="add" value="<?= lang('save and finish');?>" />
	   <input type="button" onClick="location='<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.add_edit_site&cancel=true&where_key=site_id&where_value='.$this->global_values['parent_site_id']);?>
	   '" value="<?= lang('Cancel');?>" />
	   <input type="button" onClick="if(window.confirm('<?= lang('Are you sure?');?>'))location='<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boadmin.del_egw_jinn_object&where_key=object_id&where_value='.$this->where_value);?>
	   '" value="<?= lang('Delete');?>" />
	</td>
 </tr>
  </table>
  </form>




