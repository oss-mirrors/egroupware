<?php
// $Header$

/**
 * Takes the results of clicking "Save" in edit.php and determines which
 * attributes need to be updated (ie, which ones actually changed). Then,
 * we present a confirmation table to the user outlining the changes they
 * are about to make. That form submits directly to update.php, which
 * makes the change.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
include './header.php';
include 'templates/template_config.php';

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );

$dn = $_POST['dn'];
$old_values = $_POST['old_values'];
$new_values = $_POST['new_values'];
$encoded_dn = rawurlencode( $dn );
$rdn = get_rdn( $dn );
$mkntPassword = NULL;
$samba_password_step = 0;

?>

<body>
<h3 class="title"><?php echo htmlspecialchars( ( $rdn ) ); ?></h3>
<h3 class="subtitle">Server: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']; ?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>
<?php

run_hook ( 'pre_update_array_processing', array ( 'server_id' => $ldapserver->server_id,
	'dn' => $dn, 'old_values' => $old_values, 'new_values' => $new_values ) );

$update_array = array();
foreach( $old_values as $attr => $old_val ) {
	// Did the user delete the field?
	if( ! isset( $new_values[ $attr ] ) )
		$update_array[ $attr ] = '';

	// did the user change the field?
	elseif( $old_val != $new_values[ $attr ] ) {

		$new_val = $new_values[ $attr ];

		// special case for userPassword attributes
		if( 0 == strcasecmp( $attr, 'userPassword' ) && $new_val != '' ) {
			$new_val = password_hash( $new_val, $_POST['enc_type'] );
			$password_already_hashed = true;
		}

		// special case for samba password
		else if (( 0 == strcasecmp($attr,'sambaNTPassword') || 0 == strcasecmp($attr,'sambaLMPassword'))
			&& trim($new_val[0]) != '' ) {

			$mkntPassword = new MkntPasswdUtil();
			$mkntPassword->createSambaPasswords( $new_val[0] ) or pla_error($lang['unable_create_samba_pass']);
			$new_val = $mkntPassword->valueOf($attr);
		}

		$update_array[ $attr ] = $new_val;
	}
}

// special case check for a new enc_type for userPassword (not otherwise detected)
if( isset( $_POST['enc_type'] ) &&
	! isset( $password_already_hashed ) &&
	$_POST['enc_type'] != $_POST['old_enc_type'] &&
	$_POST['enc_type'] != 'clear' &&
	array_key_exists ( 'userpassword', $_POST['new_values'] ) &&
	$_POST['new_values']['userpassword'] != '' ) {

	$new_password = password_hash( $_POST['new_values']['userpassword'], $_POST['enc_type'] );
	$update_array[ 'userpassword' ] = $new_password;
}

// strip empty vals from update_array and ensure consecutive indices for each attribute
foreach( $update_array as $attr => $val ) {
	if( is_array( $val ) ) {
		foreach( $val as $i => $v )
			if( null == $v || 0 == strlen( $v ) )
				unset( $update_array[$attr][$i] );
		$update_array[$attr] = array_values( $update_array[$attr] );
	}
}

// at this point, the update_array should look like this (example):
// Array (
//    cn => Array(
//           [0] => 'Dave',
//           [1] => 'Bob' )
//    sn => 'Smith',
//    telephoneNumber => '555-1234' )
//  This array should be ready to be passed to ldap_modify()

run_hook ( 'post_update_array_processing', array ( 'server_id' => $ldapserver->server_id,
	'dn' => $dn, 'update_array' => $update_array ) );

if( count( $update_array ) > 0 ) { ?>

	<br />
	<center>
	<?php echo $lang['do_you_want_to_make_these_changes']; ?>
	<br />
	<br />

	<!-- Commit button and acompanying form -->
	<form action="update.php" method="post">

	<table class="confirm">
	<tr>
		<th><?php echo $lang['attribute']; ?></th>
		<th><?php echo $lang['old_value']; ?></th>
		<th><?php echo $lang['new_value']; ?></th>
	</tr>

	<?php
	$counter=0;

	run_hook ( 'pre_display_update_array', array ( 'server_id' => $ldapserver->server_id, 'dn' => $dn,
		'update_array' => $update_array ) );

	foreach( $update_array as $attr => $new_val ) {
		$counter++;

		if ( ! array_key_exists ( $attr, $old_values ) or ! array_key_exists ( $attr, $new_values ) )
			continue;
		?>

		<tr class="<?php echo $counter%2 ? 'even' : 'odd'; ?>">
		<td><b><?php echo htmlspecialchars( $attr ); ?></b></td>
		<td><nobr>

		<?php
		if( is_array( $old_values[ $attr ] ) )
			foreach( $old_values[ $attr ] as $v )
				echo nl2br( htmlspecialchars( $v ) ) . "<br />";
		else
			if( 0 == strcasecmp( $attr, 'userPassword' )
				&& ( $config->GetValue('appearance','obfuscate_password_display') || is_null( get_enc_type( $old_values[ $attr ] ) ) ) )

				echo preg_replace( '/./', '*', $old_values[ $attr ] ) . "<br />";

			else
				echo nl2br( htmlspecialchars( $old_values[ $attr ] ) ) . "<br />";

		echo "</nobr></td><td><nobr>";

		// is this a multi-valued attribute?
		if( is_array( $new_val ) ) {
			foreach( $new_val as $i => $v ) {
				if( $v == '' ) {
					// remove it from the update array if it's empty
					unset( $update_array[ $attr ][ $i ] );
					$update_array[ $attr ] = array_values( $update_array[ $attr ] );

				} else {
					echo nl2br( htmlspecialchars( $v ) ) . "<br />";
				}
			}

			// was this a multi-valued attribute deletion? If so,
			// fix the $update_array to reflect that per update_confirm.php's
			// expectations
			if( $update_array[ $attr ] == array( 0 => '' ) || $update_array[ $attr ] == array() ) {
				$update_array[ $attr ] = '';
				echo '<span style="color: red">' . $lang['attr_deleted'] . '</span>';
			}
		}

		elseif( $new_val != '' )
			if( 0 == strcasecmp( $attr, 'userPassword' ) &&
				( $config->GetValue('appearance','obfuscate_password_display') || is_null( get_enc_type( $new_values[ $attr ] ) ) ) )

				echo preg_replace( '/./', '*', $new_val ) . "<br />";

				else
					echo htmlspecialchars( $new_val ) . "<br />";

			else
				echo '<span style="color: red">' . $lang['attr_deleted'] . '</span>';

			echo "</nobr></td></tr>\n\n";
		}

		run_hook ( 'post_display_update_array', array ( 'server_id' => $ldapserver->server_id, 'dn' => $dn,
			'update_array' => $update_array, 'index' => $counter ) );
	?>

	</table>
	<br />

	<table class="form">
	<tr>
		<td>
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="hidden" name="dn" value="<?php echo $dn; ?>" />

		<?php foreach( $update_array as $attr => $val ) {
			if( is_array( $val ) ) {
				foreach( $val as $i => $v ) { ?>

			<input type="hidden" name="update_array[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]" value="<?php echo htmlspecialchars( $v ); ?>" />
				<?php }

			} else { ?>

			<input type="hidden" name="update_array[<?php echo htmlspecialchars( $attr ); ?>]" value="<?php echo htmlspecialchars( $val ); ?>" />

			<?php }
		} ?>

			<input type="submit" value="<?php echo $lang['commit']; ?>" class="happy" />
			</form>
		</td>
		<td>
			<!-- Cancel button -->
			<form action="edit.php" method="get">
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
			<input type="submit" value="<?php echo $lang['cancel']; ?>" class="scary" />
			</form>
		</td>
	</tr>
	</table>
	</center>
	</body>

	<?php

} else { ?>

	<center>
	<?php echo $lang['you_made_no_changes']; ?>
	<a href="edit.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"><?php echo $lang['go_back']; ?></a>.
	</center>

<?php } ?>

</form>
