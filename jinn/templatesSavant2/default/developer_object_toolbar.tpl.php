<div style="background-color:#ffdbb3;padding:3px;">
   <input type="button" onclick="javascript:location.href='<?=$this->normal_mode_link?>'" style="float:right;" class="egwbutton"  value="<?=lang('Back to normal mode')?>" />

   <input type="button" value="<?=lang('general options')?>" class="egwbutton" onclick="parent.window.open('<?=$this->gen_obj_options_link?>' , 'genobjoptions', 'width=780,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />

   <input type="button" value="<?=lang('object event plugins')?>" class="egwbutton" onclick="parent.window.open('<?=$this->obj_event_plugins_link?>' , 'genobjoptions', 'width=980,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />

   <input type="button" value="<?=lang('relation widgets')?>" class="egwbutton" onclick="parent.window.open('<?=$this->relation_link?>' , 'relwidget', 'width=980,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')" />
</div>

