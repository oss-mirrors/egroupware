<?php
  /**************************************************************************\
  * phpGroupWare - Info Log: CSV - Import                                    *
  * http://www.phpgroupware.org                                              *
  * Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info['flags']['currentapp'] = 'infolog';
	$phpgw_info['flags']['enable_contacts_class'] = True;
	include("../header.inc.php");

	$phpgw->infolog = createobject('infolog.uiinfolog');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL); // $t->unknows = 'keep'; $t->debug = 1;
	$t->set_file(array('import' => 'csv_import.tpl'));
	$t->set_block('import','filename','filenamehandle');
	$t->set_block('import','fheader','fheaderhandle');
	$t->set_block('import','fields','fieldshandle');
	$t->set_block('import','ffooter','ffooterhandle');
	$t->set_block('import','imported','importedhandle');
	
	// $t->set_var("navbar_bg",$phpgw_info["theme"]["navbar_bg"]);
	// $t->set_var("navbar_text",$phpgw_info["theme"]["navbar_text"]);
	
	if ($action == 'download' && (!$fieldsep || !$csvfile || !($fp=fopen($csvfile,"r")))) {
		$action = '';
	}		
	$t->set_var("action_url",$phpgw->link("/infolog/csv_import.php"));
	$t->set_var( $phpgw->infolog->setStyleSheet( ));
	$t->set_var("lang_info_action",lang("Import CSV-File into Info Log"));

	$PSep = '||'; // Pattern-Separator, separats the pattern-replacement-pairs in trans
	$ASep = '|>'; // Assignment-Separator, separats pattern and replacesment
	$VPre = '|#'; // Value-Prefix, is expanded to \ for ereg_replace
	$CPre = '|['; $CPreReg = '\|\['; // |{csv-fieldname} is expanded to the value of the csv-field
	$CPos = ']';  $CPosReg = '\]';	// if used together with @ (replacement is eval-ed) value gets autom. quoted

function dump_array( $arr ) {
	while (list($key,$val) = each($arr))
		$ret .= ($ret ? ',' : '(') . "'$key' => '$val'";
	return $ret.')';
}

function index( $value,$arr ) {
	while (list ($key,$val) = each($arr))
		if ($value == $val)
			return $key;
	return False;
}

function addr_id( $n_family,$n_given,$org_name ) {		// find in Addressbook, at least n_family AND (n_given OR org_name) have to match
	$contacts = createobject('phpgwapi.contacts');

	$addrs = $contacts->read( 0,0,array('id'),'',"n_family=$n_family,n_given=$n_given,org_name=$org_name" );
	if (!count($addrs))
		$addrs = $contacts->read( 0,0,array('id'),'',"n_family=$n_family,n_given=$n_given" );
	if (!count($addrs))
		$addrs = $contacts->read( 0,0,array('id'),'',"n_family=$n_family,org_name=$org_name" );
	
	if (count($addrs))
		return $addrs[0]['id'];
		
	return False;	
}
	
$cat2id = array( );

function cat_id( $cats )
{
	if (!$cats)
		return '';
		
	$cats = explode(',',$cats);
	
	while (list($k,$cat) = each($cats)) {
		if (isset($cat2id[$cat])) {
			$ids[$cat] = $cat2id[$cat];								// cat is in cache
		} else {
			if (!is_object($phpgw->categories)) {
				$phpgw->categories = createobject('phpgwapi.categories');
			}			
			if ($id = $phpgw->categories->name2id( $cat )) {	// cat exists
				$cat2id[$cat] = $ids[$cat] = $id;
			} else {															// create new cat
				$phpgw->categories->add( $cat,0,$cat,'','public',0);
				$cat2id[$cat] = $ids[$cat] = $phpgw->categories->name2id( $cat );
			}
		}
	}
	return implode( ',',$ids );
}											
	
	switch ($action) {
	case '':	// Start, ask Filename
		$t->set_var('lang_csvfile',lang('CSV-Filename'));
		$t->set_var('lang_fieldsep',lang('Fieldseparator'));
		$t->set_var('fieldsep',$fieldsep ? $fieldsep : ',');
		$t->set_var('submit',lang('Download'));
		$t->set_var('csvfile',$csvfile);
		$t->set_var('enctype','ENCTYPE="multipart/form-data"');
		$hiddenvars .= '<input type="hidden" name="action" value="download">'."\n";

		$t->parse('filenamehandle','filename');
		break;
		
	case 'download':
		$pref_file = '/tmp/csv_import_info_log.php';
		if (is_readable($pref_file) && ($prefs = fopen($pref_file,'r'))) {
			eval(fread($prefs,8000));
			// echo "<p>defaults = array".dump_array($defaults)."</p>\n";
		}	else {
			$defaults = array();
		}		
		$t->set_var('lang_csv_fieldname',lang('CSV-Fieldname'));
		$t->set_var('lang_info_fieldname',lang('Info Log-Fieldname'));
		$t->set_var('lang_translation',lang("Translation").' <a href="#help">'.lang('help').'</a>');
		$t->set_var('submit',lang('Import'));
		$t->set_var('lang_debug',lang('Test Import (show importable records <u>only</u> in browser)'));
		$t->parse('fheaderhandle','fheader');
		$hiddenvars .= '<input type="hidden" name="action" value="import">'."\n".
							'<input type="hidden" name="fieldsep" value="'.$fieldsep."\">\n".
							'<input type="hidden" name="pref_file" value="'.$pref_file."\">\n";

		$info_names = array(	'type' 		=> 'Type: task,phone,note,confirm,reject,email,fax',
									'from' 		=> 'From: text(64) free text if no Addressbook-entry assigned',
									'addr' 		=> 'Addr: text(64) phone-nr/email-address',
									'subject'	=>	'Subject: text(64)',
									'des'			=>	'Description: text long free text',
									'owner'		=>	'Owner: int(11) user-id of owner, if empty current user',
									'responsible' => 'Responsible: int(11) user-id of resp. person',
									'access'		=>	'Access: public,private',
									'cat'			=>	'Cathegory: int(11) cathegory-id',
									'datecreated' => 'Date Created: DateTime if empty = Start Date or now',
									'startdate' => 'Start Date: DateTime',
									'enddate'	=>	'End Date: DateTime',
									'pri'			=> 'Priority: urgent,high,normal,low',
									'time'		=> 'Time: int(11) time used in min',
									'bill_cat'	=> 'Billing Cathegory: int(11)',
									'status'		=> 'Status: offer,ongoing,call,will-call,done,billed',
									'confirm'	=> 'Confirmation: not,accept,finish,both when to confirm',
									'cat_id' 	=> 'Categorie id(s), to set use @cat_id(Cat1,Cat2)',
									'addr_id'	=>	'Addressbook id, to set use @addr_id(nlast,nfirst,org)' );

		$mktime_lotus = "${PSep}0?([0-9]+)[ .:-]+0?([0-9]*)[ .:-]+0?([0-9]*)[ .:-]+0?([0-9]*)[ .:-]+0?([0-9]*)[ .:-]+0?([0-9]*).*$ASep@mktime(${VPre}4,${VPre}5,${VPre}6,${VPre}2,${VPre}3,${VPre}1)";
		
		$defaults += array(	'Land'			=> "addr$PSep.*[(]+([0-9]+)[)]+$ASep+${VPre}1 (${CPre}Ortsvorwahl$CPos) ${CPre}Telefon$CPos$PSep${CPre}Telefon$CPos",
									'Notiz'			=> 'des',
									'Privat'			=> "access${PSep}1${ASep}private${PSep}public",
									'Startdatum'	=>	'startdate'.$mktime_lotus,
									'Enddatum'		=>	'enddate'.$mktime_lotus,
									'Erledigt'		=>	"status${PSep}1${ASep}done${PSep}call",
									'Nachname'		=> "addr_id${PSep}@addr_id(${CPre}Nachname$CPos,${CPre}Vorname$CPos,${CPre}Firma$CPos)",
									'Firma'			=>	"from${PSep}.+$ASep${CPre}Firma$CPos: ${CPre}Nachname$CPos, ${CPre}Vorname$CPos".
																		"${PSep}${CPre}Nachname$CPos, ${CPre}Vorname$CPos",
									'no CSV 1'		=>	"type${PSep}phone",
									'no CSV 2'		=>	"subject${PSep}@substr(${CPre}Notiz$CPos,0,60).' ...'" );
		
		$info_name_options = "<option value=\"\">none\n";
		while (list($field,$name) = each($info_names)) {
			$info_name_options .= "<option value=\"$field\">".$phpgw->strip_html($name)."\n";
		}		
		$csv_fields = fgetcsv($fp,8000,$fieldsep);
		$csv_fields[] = 'no CSV 1'; 						// eg. for static assignments
		$csv_fields[] = 'no CSV 2'; 
		$csv_fields[] = 'no CSV 3'; 
		while (list($csv_idx,$csv_field) = each($csv_fields)) {
			$t->set_var('csv_field',$csv_field);
			$t->set_var('csv_idx',$csv_idx);
			if ($def = $defaults[$csv_field]) {
				list( $info,$trans ) = explode($PSep,$def,2);
				$t->set_var('trans',$trans);
				$t->set_var('info_fields',str_replace('="'.$info.'">','="'.$info.'" selected>',$info_name_options));
			} else {		
				$t->set_var('trans','');
				$t->set_var('info_fields',$info_name_options);
			}			
			$t->parse('fieldshandle','fields',True); 
		}		
		$t->set_var('lang_start',lang('Startrecord'));
		$t->set_var('start',$start);
		$t->set_var('lang_max',lang('Number of records to read (<=200)'));
		$t->set_var('max',200);
		$t->parse('ffooterhandle','ffooter'); 
		fclose($fp);
		$old = $csvfile; $csvfile = $phpgw_info['server']['temp_dir'].'/info_log_import_'.basename($csvfile);
		rename($old,$csvfile); 
		$hiddenvars .= '<input type="hidden" name="csvfile" value="'.$csvfile.'">';
		$help_on_trans = 	"<a name='help'><b>How to use Translation's</b><p>".
								"Translations enable you to change / adapt the content of each CSV field for your needs. <br>".
								"General syntax is: <b>pattern1 ${ASep} replacement1 ${PSep} ... ${PSep} patternN ${ASep} replacementN</b><br>".
								"If the pattern-part of a pair is ommited it will match everything ('^.*$'), which is only ".
								"usefull for the last pair, as they are worked from left to right.<p>".
								"First example: <b>1${ASep}private${PSep}public</b><br>".
								"This will translate a '1' in the CVS field to 'privat' and everything else to 'public'.<p>".
								"Patterns as well as the replacement can be regular expressions (the replacement is done via ereg_replace). ".
								"If, after all replacements, the value starts with an '@' the whole value is eval()'ed, so you ".
								"may use all php, phpgw plus your own functions. This is quiet powerfull, but <u>circumvents all ACL</u>.<p>".
								"Example using regular expressions and '@'-eval(): <br><b>$mktime_lotus</b><br>".
								"It will read a date of the form '2001-05-20 08:00:00.00000000000000000' (and many more, see the regular expr.). ".
								"The&nbsp;[&nbsp;.:-]-separated fields are read and assigned in different order to @mktime(). Please note to use ".
								"${VPre} insted of a backslash (I couldn't get backslash through all the involved templates and forms.) ".
								"plus the field-number of the pattern.<p>".
								"In addintion to the fields assign by the pattern of the reg.exp. you can use all other CSV-fields, with the ".
								"syntax <b>${CPre}CVS-FIELDNAME$CPos</b>. Here is an example: <br>".
								"<b>.+$ASep${CPre}Company$CPos: ${CPre}NFamily$CPos, ${CPre}NGiven$CPos$PSep${CPre}NFamily$CPos, ${CPre}NGiven$CPos</b><br>".
								"It is used on the CVS-field 'Company' and constructs a something like <i>Company: FamilyName, GivenName</i> or ".
								"<i>FamilyName, GivenName</i> if 'Company' is empty.<p>".
								"You can use the 'No CVS #'-fields to assign cvs-values to more than on field, the following example uses the ".
								"cvs-field 'Note' (which gots already assingned to the description) and construct a short subject: ".
								"<b>@substr(${CPre}Note$CPos,0,60).' ...'</b><p>".
								"Their is two important user-function for the Info Log:<br>".
								"<b>@addr_id(${CPre}NFamily$CPos,${CPre}NGiven$CPos,${CPre}Company$CPos)</b> ".
								"searches the addressbook for an address and returns the id if it founds an exact match of at least ".
								"<i>NFamily</i> AND (<i>NGiven</i> OR <i>Company</i>). This is necessary to link your imported InfoLog-entrys ".
								"with the addressbook.<br>".
								"<b>@cat_id(Cat1,...,CatN)</b> returns a (','-separated) list with the cat_id's. If a category isn't found, it ".
								"will be automaticaly added.<p>".
								"I hope that helped to understand the features, if not <a href='mailto:RalfBecker@outdoor-training.de'>ask</a>.";
								
		$t->set_var('help_on_trans',lang($help_on_trans));	// I don't think anyone will translate this
		break;
		
	case 'import':
		$fp=fopen($csvfile,"r");
		$csv_fields = fgetcsv($fp,8000,$fieldsep);
		
		$info_fields = array_diff($info_fields,array( '' ));	// throw away empty / not assigned entrys
		
		if ($pref_file) {
			// echo "writing pref_file ...<p>";
			if (file_exists($pref_file)) rename($pref_file,$pref_file.'.old');
			$pref = fopen($pref_file,'w');
			while (list($csv_idx,$info) = each($info_fields)) {
				$defaults[$csv_fields[$csv_idx]] = $info;
				if ($trans[$csv_idx])
					$defaults[$csv_fields[$csv_idx]] .= $PSep.$trans[$csv_idx];
			}
			fwrite($pref,'$defaults = array'.dump_array( $defaults ).';');
			fclose($pref);
		}		
		$log = "<table border=1>\n\t<tr><td>#</td>\n";
	
		reset($info_fields);
		while (list($csv_idx,$info) = each($info_fields)) {	// convert $trans[$csv_idx] into array of pattern => value
			// if (!$debug) echo "<p>$csv_idx: ".$csv_fields[$csv_idx].": $info".($trans[$csv_idx] ? ': '.$trans[$csv_idx] : '')."</p>";
			$pat_reps = explode($PSep,stripslashes($trans[$csv_idx]));
			$replaces = ''; $values = '';
			if ($pat_reps[0] != '') {
				while (list($k,$pat_rep) = each($pat_reps)) {
					list($pattern,$replace) = explode($ASep,$pat_rep,2);
					if ($replace == '') { $replace = $pattern; $pattern = '^.*$'; }
					$values[$pattern] = $replace;	// replace two with only one, added by the form
					$replaces .= ($replaces != '' ? $PSep : '') . $pattern . $ASep . $replace;
				}
				$trans[$csv_idx] = $values;
			} else
				unset( $trans[$csv_idx] );
						
			$log .= "\t\t<td><b>$info</b></td>\n";		
		}
		if ($start < 1) $start = 1;
		for ($i = 1; $i < $start && fgetcsv($fp,8000,$fieldsep); ++$i) ; 	// overread lines before our start-record
		
		for ($anz = 0; $anz < $max && ($fields = fgetcsv($fp,8000,$fieldsep)); ++$anz) {
			$log .= "\t</tr><tr><td>".($start+$anz)."</td>\n";
			
			reset($info_fields); $values = array();
			while (list($csv_idx,$info) = each($info_fields)) {
				//echo "<p>$csv: $info".($trans[$csv] ? ': '.$trans[$csv] : '')."</p>";
				$val = $fields[$csv_idx];
				if (isset($trans[$csv_idx])) {
					$trans_csv = $trans[$csv_idx];
					while (list($pattern,$replace) = each($trans_csv)) {
						if (ereg((string) $pattern,$val)) {
							// echo "<p>csv_idx='$csv_idx',info='$info',trans_csv=".dump_array($trans_csv).",ereg_replace('$pattern','$replace','$val') = ";
							$val = ereg_replace((string) $pattern,str_replace($VPre,'\\',$replace),(string) $val);
							// echo "'$val'</p>";

							$quote = $val[0] == '@' ? "'" : '';
									
							$reg = $CPreReg.'([a-zA-Z_0-9]+)'.$CPosReg; 
							while (ereg($reg,$val,$vars)) {	// expand all CSV fields
								$val = str_replace($CPre.$vars[1].$CPos,$quote.$fields[index($vars[1],$csv_fields)].$quote,$val);
							}
							if ($val[0] == '@') {
								$val = 'return '.substr($val,1).';';
								// echo "<p>eval('$val')=";
								$val = eval($val);
								// echo "'$val'</p>";
							}								
							if ($pattern[0] != '@' || $val)
								break;
						}
					}											
				}
				$values[$info] = $val;
				
				$log .= "\t\t<td>$val</td>\n";
			}
			if (!isset($values['datecreated'])) $values['datecreated'] = $values['startdate'];
			
			if (!$debug) {
				$phpgw->infolog->write($values);
			}			
		}		
		$log .= "\t</tr>\n</table>\n";
		
		$t->set_var('anz_imported',$debug ? lang( '%1 records read (not yet imported, you may go back and uncheck Test Import)',
																$anz,'<a href="javascript:history.back()">','</a>' ) :
														lang( '%1 records imported',$anz ));
		$t->set_var('log',$log);
		$t->parse('importedhandle','imported');
		break;
	}
	$t->set_var('hiddenvars',$hiddenvars);
	$t->pfp('out','import',True);
	$phpgw->common->phpgw_footer();

?>
