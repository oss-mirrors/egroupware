   <tr bgcolor="FFFFFF">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr bgcolor="486591">
    <td colspan="2"><font color="fefefe">&nbsp;<b>Preferences</b></font></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter the title for your site.</td>
    <td><input name="newsettings[site_title]" value="<?php echo $current_config["site_title"]; ?>"></td>
   </tr>

   <?php $selected[$current_config["showpoweredbyon"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>Show 'powered by' logo on:</td>
    <td>
     <select name="newsettings[showpoweredbyon]">
      <option value="bottom"<?php echo $selected["bottom"]; ?>>bottom</option>
      <option value="top"<?php echo $selected["top"]; ?>>top</option>
     </select>
    </td>
   </tr>
   <?php $selected = array(); ?>

   <?php $selected[$current_config["countrylist"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>Country Selection (Text Entry/SelectBox):</td>
    <td>
     <select name="newsettings[countrylist]">
    <?php
    echo '<option value="user_choice"'  . $selected['user_choice']  . '>Users Choice</option>';
    echo '<option value="force_select"' . $selected['force_select'] . '>Force Selectbox</option>';
    ?>
     </select>
    </td>
   </tr>
   <?php $selected = array(); ?>

   <?php $selected[$current_config["template_set"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>Interface/Template Selection:<br> <!---(if user choice, and they dont make a selection, then classic will be used)---></td>
    <td>
     <select name="newsettings[template_set]">
    <?php
	if ($phpgw_setup)
	{
		$templates = $phpgw_setup->get_template_list();
	}
	else
	{
		$templates = $phpgw->common->list_templates();
	}
	echo '<option value="user_choice"' . $selected['user_choice'] . '>Users Choice</option>';
	while (list ($key, $value) = each ($templates))
	{
		echo '<option value="'.$key.'" '.$selected[$key].'>'.$templates[$key]["title"].'</option>';
	}
    ?>
     </select>
    </td>
   </tr>
   <?php $selected = array(); ?>

   <?php $selected[$current_config["force_theme"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>Use theme:<br></td>
    <td>
     <select name="newsettings[force_theme]">
    <?php
	if ($phpgw_setup)
	{
		$themes = $phpgw_setup->list_themes();
	}
	else
	{
		$themes = $phpgw->common->list_themes();
	}
	echo '<option value="user_choice"' . $selected['user_choice'] . '>Users Choice</option>';
	while (list ($key, $value) = each ($themes))
	{
		echo '<option value="'.$value.'" '.$selected[$value].'>'.$value.'</option>';
	}
    ?>
     </select>
    </td>
   </tr>
   <?php $selected = array(); ?>

   <?php
   /* $selected[$current_config["useframes"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>Frame support:</td>
    <td>
     <select name="newsettings[useframes]">
      <option value="allowed"<?php echo $selected["allowed"]; ?>>Allow frames</option>
      <option value="always"<?php echo $selected["always"]; ?>>Force frames</option>
      <option value="never"<?php echo $selected["never"]; ?>>Disable frames</option>
     </select>
    </td>
   </tr>
   <?php $selected = array(); */
   ?>

   <?php $selected = array(); ?>
   <?php $selected[$current_config['htmlcompliant']] = ' selected'; ?>
   <tr bgcolor="e6e6e6">
     <td>Use pure HTML compliant code (not fully working yet):</td>
     <td>
      <select name="newsettings[htmlcompliant]">
       <option value="">No</option>
       <option value="True"<?php echo $selected['True']?>>Yes</option>
      </select>
     </td>
    </tr>

   <?php $selected = array(); ?>
   <?php $selected[$current_config['usecookies']] = ' selected'; ?>
   <tr bgcolor="e6e6e6">
     <td>Use cookies to pass sessionid:</td>
     <td>
      <select name="newsettings[usecookies]">
       <option value="">No</option>
       <option value="True"<?php echo $selected['True']?>>Yes</option>
      </select>
     </td>
    </tr>

   <?php $selected = array(); ?>
   <?php $selected[$current_config['checkfornewversion']] = ' selected'; ?>
   <tr bgcolor="e6e6e6">
     <td>Would you like phpGroupWare to check for new version<br>when admins login ?:</td>
     <td>
      <select name="newsettings[checkfornewversion]">
       <option value="">No</option>
       <option value="True"<?php echo $selected['True']?>>Yes</option>
      </select>
     </td>
    </tr>

   <?php $selected = array(); ?>
   <?php $selected[$current_config['cache_phpgw_info']] = ' selected'; ?>
   <tr bgcolor="e6e6e6">
     <td>Would you like phpGroupWare to cache the phpgw_info array ?:</td>
     <td>
      <select name="newsettings[cache_phpgw_info]">
       <option value="">No</option>
       <option value="True"<?php echo $selected['True']?>>Yes</option>
      </select>
     </td>
    </tr>

   <?php $selected = array(); ?>
   <tr bgcolor="e6e6e6">
     <td>Default file system space per user/group ?:</td>
     <td>
      <input type="text" name="newsettings[vfs_default_account_size_number]" size="7" value="<?php echo $current_config['vfs_default_account_size_number']; ?> ">
	&nbsp;&nbsp;
   <?php $selected[$current_config['vfs_default_account_size_type']] = ' selected'; ?>
      <select name="newsettings[vfs_default_account_size_type]">
       <option value="gb" <?php echo $selected['gb']?>>GB</option>
       <option value="mb" <?php echo $selected['mb']?>>MB</option>
       <option value="kb" <?php echo $selected['mb']?>>KB</option>
       <option value="b" <?php echo $selected['mb']?>>B</option>
      </select>
     </td>
    </tr>
