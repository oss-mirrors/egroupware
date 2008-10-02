<?php
/**
 * Setup
 *
 * @link http://www.egroupware.org
 * @package setup
 * @author Miles Lott <milos@groupwhere.org>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Get the options for vfs_storage_mode, select the right one depending on vfs_fstab
 *
 * @param array $config
 * @return string
 */
function vfs_storage_mode_options($config)
{
	if (!isset($config['vfs_fstab']) || $config['vfs_fstab'] == serialize(array(
		'/' => 'sqlfs://$host/',
		'/apps' => 'links://$host/apps',
	)))
	{
		$config['vfs_storage_mode'] = 'fs';
	}
	elseif($config['vfs_fstab'] == serialize(array(
		'/' => 'sqlfs://$host/?storage=db',
		'/apps' => 'links://$host/apps?storage=db',
	)))
	{
		$config['vfs_storage_mode'] = 'db';
	}
	else
	{
		$config['vfs_storage_mode'] = 'custom';
	}
	//_debug_array(array_intersect_key($config,array('vfs_fstab'=>1,'vfs_storage_mode'=>1)));
	foreach(array(
		'fs' => lang('Filesystem (default)'),
		'db' => lang('Database').' (problems with files > 1MB)',
		'custom' => lang('Custom set via %1','filemanager/cli.php mount'),
	) as $name => $label)
	{
		$options .= '<option value="'.$name.($name === $config['vfs_storage_mode'] ? '" selected="selected' : '').
			'">'.htmlspecialchars($label)."</options>\n";
	}
	//echo "<pre>".htmlspecialchars($options)."</pre>\n";
	return $options;
}

function encryptalgo($config)
{
	if(@function_exists('mcrypt_list_algorithms'))
	{
		$listed = array();
		if(!isset($config['mcrypt_algo']))
		{
			$config['mcrypt_algo'] = 'tripledes';  /* MCRYPT_TRIPLEDES */
		}
		$algos = @mcrypt_list_algorithms();
		$found = False;

		$out = '';
		while(list($key,$value) = each($algos))
		{
			$found = True;
			/* Only show each once - seems this is a problem in some installs */
			if(!in_array($value,$listed))
			{
				if($config['mcrypt_algo'] == $value)
				{
					$selected = ' selected="selected"';
				}
				else
				{
					$selected = '';
				}
				$descr = strtoupper($value);

				$out .= '<option value="' . $value . '"' . $selected . '>' . $descr . '</option>' . "\n";
				$listed[] = $value;
			}
		}
		if(!$found)
		{
			/* Something is wrong with their mcrypt install or php.ini */
			$out = '<option value="">' . lang('no algorithms available') . '</option>' . "\n";;
		}
	}
	else
	{
		$out = '<option value="tripledes">TRIPLEDES</option>' . "\n";;
	}
	return $out;
}

function encryptmode($config)
{
	if(@function_exists('mcrypt_list_modes'))
	{
		$listed = array();
		if(!isset($config['mcrypt_mode']))
		{
			$config['mcrypt_mode'] = 'cbc'; /* MCRYPT_MODE_CBC */
		}
		$modes = @mcrypt_list_modes();
		$found = False;

		$out = '';
		while(list($key,$value) = each($modes))
		{
			$found = True;
			/* Only show each once - seems this is a problem in some installs */
			if(!in_array($value,$listed))
			{
				if($config['mcrypt_mode'] == $value)
				{
					$selected = ' selected="selected"';
				}
				else
				{
					$selected = '';
				}
				$descr = strtoupper($value);

				$out .= '<option value="' . $value . '"' . $selected . '>' . $descr . '</option>' . "\n";
				$listed[] = $value;
			}
		}
		if(!$found)
		{
			/* Something is wrong with their mcrypt install or php.ini */
			$out = '<option value="" selected="selected">' . lang('no modes available') . '</option>' . "\n";
		}
	}
	else
	{
		$out = '<option value="cbc" selected="selected">CBC</option>' . "\n";
	}
	return $out;
}

function passwdhashes($config)
{
	$hashes = array(
		'des' => 'des',
		'md5' => 'md5'
	);
	if(@function_exists('mhash'))
	{
		$hashes += array(
			'smd5' => 'smd5',
			'sha'  => 'sha',
			'ssha' => 'ssha'
		);
	}
	$hashes += array(
		'plain' => 'plain',
	);
	/* Check for available crypt methods based on what is defined by php */
	if(@defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH == 1)
	{
		$hashes['blowish_crypt'] = 'blowish_crypt';
	}
	if(@defined('CRYPT_MD5') && CRYPT_MD5 == 1)
	{
		$hashes['md5_crypt'] = 'md5_crypt';
	}
	if(@defined('CRYPT_EXT_DES') && CRYPT_EXT_DES == 1)
	{
		$hashes['ext_crypt'] = 'ext_crypt';
	}

	while(list($key, $value) = each($hashes))
	{
		if($config['ldap_encryption_type'] == $value)
		{
			$selected = ' selected="selected"';
		}
		else
		{
			$selected = '';
		}
		$descr = strtoupper($value);

		$out .= '<option value="' . $value . '"' . $selected . '>' . $descr . '</option>' . "\n";
	}
	return $out;
}

function sql_passwdhashes($config)
{
	$hashes = array(
		'md5' => 'md5'
	);

	/* Check for available crypt methods based on what is defined by php */
	if(@defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH == 1)
	{
		$hashes['blowish_crypt'] = 'blowish_crypt';
	}
	if(@defined('CRYPT_MD5') && CRYPT_MD5 == 1)
	{
		$hashes['md5_crypt'] = 'md5_crypt';
	}
	if(@defined('CRYPT_EXT_DES') && CRYPT_EXT_DES == 1)
	{
		$hashes['ext_crypt'] = 'ext_crypt';
	}
	if(@defined('CRYPT_STD_DES') && CRYPT_STD_DES == 1)
	{
		$hashes['crypt'] = 'crypt';
	}

	if(@function_exists('mhash'))
	{
		$hashes += array(
			'smd5' => 'smd5',
			'sha'  => 'sha',
			'ssha' => 'ssha'
		);
	}
	$hashes += array(
		'plain' => 'plain',
	);

	while(list($key, $value) = each($hashes))
	{
		if($config['sql_encryption_type'] == $value)
		{
			$selected = ' selected="selected"';
		}
		else
		{
			$selected = '';
		}
		$descr = strtoupper($value);

		$out .= '<option value="' . $value . '"' . $selected . '>' . $descr . '</option>' . "\n";
	}
	return $out;
}
