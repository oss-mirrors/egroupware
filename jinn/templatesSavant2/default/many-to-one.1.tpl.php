<input type="hidden" id="m2o_enc_rule_<?=$this->m2o_arr[id]?>" value="<?=$this->m2o_rule_arr_enc?>" />
<input type="hidden" id="m2o_localkey_<?=$this->m2o_arr[id]?>" value="<?=$this->localkey?>" />
<table style="background-color:white;padding:0px;border-bottom:solid 1px #006699" cellspacing="0" cellpadding="0" width="100%">
<!--<tr>
   <td style="padding:5px 0px 0px 0px;margin:0px;font-weight:bold;"><?=$this->field_label?></td>
   			<td style="font-size:10px;font-weight:normal" align="center">{pager}</td>
<td style="font-size:10px;font-weight:normal" align="right">{total_records} - {rec_per_page}</td>
</tr>
-->
</table>
<div id="div_m2o_list<?=$this->m2o_arr[id]?>">
   <?=$this->initial_list?>
</div>

<!--<input type="button" class="egwbutton" onclick="m2o_edit_record('<?=$this->m2o_arr[id]?>','<?=$this->m2o_arr[object_conf]?>','<?=$linked_rec[where_string]?>','<?=$this->xmlhttp_get_m2o_link?>&obj_conf=<?=$this->m2o_arr[object_conf]?>','<?=$this->xmlhttp_save_m2o_link?>&obj_conf=<?=$this->m2o_arr[object_conf]?>')" value="<?=lang('New sub entry')?>">-->

<input type="button" class="egwbutton" onclick="ajax2_m2o_edit_frm('<?=$this->m2o_arr[id]?>','<?=$this->m2o_arr[object_conf]?>','<?=$linked_rec[where_string]?>','<?=$this->xmlhttp_get_m2o_link2?>&obj_conf=<?=$this->m2o_arr[object_conf]?>','<?=$this->xmlhttp_save_m2o_link2?>&obj_conf=<?=$this->m2o_arr[object_conf]?>')" value="<?=lang('New sub entry')?>">

<div id="div_m2o<?=$this->m2o_arr[id]?>">

</div>
<div id="div_m2o_debug<?=$this->m2o_arr[id]?>">

</div>


<!-- many_to_one -->

