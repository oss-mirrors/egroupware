<?php
/**
 * eGroupWare API - Authentication baseclass
 * 
 * @link http://www.egroupware.org
 * @author Miles Lott <milos@groupwhere.org>
 * @copyright 2004 by Miles Lott <milos@groupwhere.org> 
 * @license http://opensource.org/licenses/lgpl-license.php LGPL - GNU Lesser General Public License
 * @package api
 * @subpackage authentication
 * @version $Id$
 */

if(empty($GLOBALS['egw_info']['server']['auth_type']))
{
	$GLOBALS['egw_info']['server']['auth_type'] = 'sql';
}
include(EGW_API_INC.'/class.auth_'.$GLOBALS['egw_info']['server']['auth_type'].'.inc.php');

/**
 * eGroupWare API - Authentication baseclass, password auth and crypt functions
 * 
 * Many functions based on code from Frank Thomas <frank@thomas-alfeld.de>
 * which can be seen at http://www.thomas-alfeld.de/frank/
 * 
 * Other functions from class.common.inc.php originally from phpGroupWare
 */
class auth extends auth_
{
	static $error;

	/**
	 * return a random string of size $size
	 *
	 * @param $size int-size of random string to return
	 */
	static function randomstring($size)
	{
		$s = '';
		$random_char = array(
			'0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f',
			'g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v',
			'w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L',
			'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
		);

		for ($i=0; $i<$size; $i++)
		{
			$s .= $random_char[mt_rand(1,61)];
		}
		return $s;
	}

	/**
	 * encrypt password
	 *
	 * uses the encryption type set in setup and calls the appropriate encryption functions
	 *
	 * @param $password password to encrypt
	 */
	static function encrypt_password($password,$sql=False)
	{
		if($sql)
		{
			return self::encrypt_sql($password);
		}
		return self::encrypt_ldap($password);
	}

	/**
	 * compares an encrypted password
	 *
	 * encryption type set in setup and calls the appropriate encryption functions
	 *
	 * @param $cleartext cleartext password
	 * @param $encrypted encrypted password, can have a {hash} prefix, which overrides $type
	 * @param $type type of encryption
	 * @param $username used as optional key of encryption for md5_hmac
	 */
	static function compare_password($cleartext,$encrypted,$type,$username='')
	{
		// allow to specify the hash type to prefix the hash, to easy migrate passwords from ldap
		$saved_enc = $encrypted;
		if (preg_match('/^\\{([a-z_5]+)\\}(.+)$/i',$encrypted,$matches))
		{
			$type = strtolower($matches[1]);
			$encrypted = $matches[2];
			
			switch($type)	// some hashs are specially "packed" in ldap
			{
				case 'md5':
					$encrypted = implode('',unpack('H*',base64_decode($encrypted)));
					break;
				case 'plain':
				case 'crypt':
					// nothing to do
					break;
				default:
					$encrypted = $saved_enc;
				// ToDo: the others ...
			}
		}
		switch($type)
		{
			case 'plain': 
				if(strcmp($cleartext,$encrypted) == 0)
				{
					return True;
				}
				return False;
			case 'smd5':
				return self::smd5_compare($cleartext,$encrypted);
			case 'sha':
				return self::sha_compare($cleartext,$encrypted);
			case 'ssha':
				return self::ssha_compare($cleartext,$encrypted);
			case 'crypt':
			case 'md5_crypt':
			case 'blowfish_crypt':
			case 'ext_crypt':
				return self::crypt_compare($cleartext,$encrypted,$type);
			case 'md5_hmac':
				return self::md5_hmac_compare($cleartext,$encrypted,$username);
			case 'md5':
			default:
				return strcmp(md5($cleartext),$encrypted) == 0 ? true : false;
		}
	}

	/**
	 * encrypt password for ldap
	 *
	 * uses the encryption type set in setup and calls the appropriate encryption functions
	 *
	 * @param $password password to encrypt
	 */
	static function encrypt_ldap($password)
	{
		$type = strtolower($GLOBALS['egw_info']['server']['ldap_encryption_type']);
		$salt = '';
		switch($type)
		{
			default:	// eg. setup >> config never saved
			case 'des':
				$salt       = self::randomstring(2);
				$_password  = crypt($password, $salt);
				$e_password = '{crypt}'.$_password;
				break;
			case 'md5':
				/* New method taken from the openldap-software list as recommended by
				 * Kervin L. Pierre" <kervin@blueprint-tech.com>
				 */
				$e_password = '{md5}' . base64_encode(pack("H*",md5($password)));
				break;
			case 'smd5':
				if(!function_exists('mhash'))
				{
					return False;
				}
				$salt = self::randomstring(8);
				$hash = mhash(MHASH_MD5, $password . $salt);
				$e_password = '{SMD5}' . base64_encode($hash . $salt);
				break;
			case 'sha':
				if(!function_exists('mhash'))
				{
					return False;
				}
				$e_password = '{SHA}' . base64_encode(mhash(MHASH_SHA1, $password));
				break;
			case 'ssha':
				if(!function_exists('mhash'))
				{
					return False;
				}
				$salt = self::randomstring(8);
				$hash = mhash(MHASH_SHA1, $password . $salt);
				$e_password = '{SSHA}' . base64_encode($hash . $salt);
				break;
			case 'plain':
				// if plain no type is prepended
				$e_password =$password;
				break;
		}
		return $e_password;
	}
	
	/**
	 * Create an ldap hash from an sql hash
	 *
	 * @param string $hash
	 */
	static function hash_sql2ldap($hash)
	{
		switch(strtolower($GLOBALS['egw_info']['server']['sql_encryption_type']))
		{
			case '':	// not set sql_encryption_type
			case 'md5':
				$hash = '{md5}' . base64_encode(pack("H*",$hash));
				break;
			case 'crypt':
				$hash = '{crypt}' . $hash;
				break;
			case 'plain':
				$saved_h = $hash;
				if (preg_match('/^\\{([a-z_5]+)\\}(.+)$/i',$hash,$matches))
				{
					$hash= $matches[2];
				} else {
					$hash = $saved_h;
				}
				break;
		}
		return $hash;
	}

	/**
	 * Create a password for storage in the accounts table
	 * 
	 * @param string $password
	 * @return string hash
	 */
	static function encrypt_sql($password)
	{
		/* Grab configured type, or default to md5() (old method) */
		$type = @$GLOBALS['egw_info']['server']['sql_encryption_type']
			? strtolower($GLOBALS['egw_info']['server']['sql_encryption_type'])
			: 'md5';

		switch($type)
		{
			case 'plain':
				// since md5 is the default, type plain must be prepended, for eGroupware to understand
				return '{PLAIN}'.$password;
			case 'crypt':
				if(@defined('CRYPT_STD_DES') && CRYPT_STD_DES == 1)
				{
					$salt = self::randomstring(2);
					return crypt($password,$salt);
				}
				self::$error = 'no std crypt';
				break;
			case 'blowfish_crypt':
				if(@defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH == 1)
				{
					$salt = '$2$' . self::randomstring(13);
					return crypt($password,$salt);
				}
				self::$error = 'no blowfish crypt';
				break;
			case 'md5_crypt':
				if(@defined('CRYPT_MD5') && CRYPT_MD5 == 1)
				{
					$salt = '$1$' . self::randomstring(9);
					return crypt($password,$salt);
				}
				self::$error = 'no md5 crypt';
				break;
			case 'ext_crypt':
				if(@defined('CRYPT_EXT_DES') && CRYPT_EXT_DES == 1)
				{
					$salt = self::randomstring(9);
					return crypt($password,$salt);
				}
				self::$error = 'no ext crypt';
				break;
			case 'smd5':
				if(!function_exists('mhash'))
				{
					return False;
				}
				$salt = self::randomstring(8);
				$hash = mhash(MHASH_MD5, $password . $salt);
				return '{SMD5}' . base64_encode($hash . $salt);
			case 'sha':
				if(!function_exists('mhash'))
				{
					self::$error = 'no sha';
					return False;
				}
				return '{SHA}' . base64_encode(mhash(MHASH_SHA1,$password));
			case 'ssha':
				if(!function_exists('mhash'))
				{
					self::$error = 'no ssha';
					return False;
				}
				$salt = self::randomstring(8);
				$hash = mhash(MHASH_SHA1, $password . $salt);
				return '{SSHA}' . base64_encode($hash . $salt);
			case 'md5':
			default:
				/* This is the old standard for password storage in SQL */
				return md5($password);
		}
		if (!self::$error)
		{
			self::$error = 'no valid encryption available';
		}
		return False;
	}

	/**
	 * Checks if a given password is "safe"
	 *
	 * @param string $login
	 * @abstract atm a simple check in length, #digits, #uppercase and #lowercase
	 * 				could be made more safe using e.g. pecl library cracklib
	 * 				but as pecl dosn't run on any platform and isn't GPL'd
	 * 				i haven't implemented it yet
	 *				Windows compatible check is: 7 char lenth, 1 Up, 1 Low, 1 Num and 1 Special
	 * @author cornelius weiss <egw at von-und-zu-weiss.de>
	 * @return mixed false if password is considered "safe" or a string $message if "unsafe"
	 */
	static function crackcheck($passwd)
	{
		if (!preg_match('/.{'. ($noc=7). ',}/',$passwd))
		{
			$message = lang('Password must have at least %1 characters',$noc). '<br>';
		}
		if(!preg_match('/(.*\d.*){'. ($non=1). ',}/',$passwd))
		{
			$message .= lang('Password must contain at least %1 numbers',$non). '<br>';
		}
		if(!preg_match('/(.*[[:upper:]].*){'. ($nou=1). ',}/',$passwd))
		{
			$message .= lang('Password must contain at least %1 uppercase letters',$nou). '<br>';
		}
		if(!preg_match('/(.*[[:lower:]].*){'. ($nol=1). ',}/',$passwd))
		{
			$message .= lang('Password must contain at least %1 lowercase letters',$nol). '<br>';
		}
		if(!preg_match('/(.*[\\!"#$%&\'()*+,-.\/:;<=>?@\[\]\^_ {|}~`].*){'. ($nol=1). ',}/',$passwd))
		{
			$message .= lang('Password must contain at least %1 special characters',$nol). '<br>';
		}
		return $message ? $message : false;
	}

	/**
	 * compare SMD5-encrypted passwords for authentication
	 * 
	 * @param string $form_val user input value for comparison
	 * @param string $db_val stored value (from database)
	 * @return boolean True on successful comparison
	*/
	static function smd5_compare($form_val,$db_val)
	{
		/* Start with the first char after {SMD5} */
		$hash = base64_decode(substr($db_val,6));

		/* SMD5 hashes are 16 bytes long */
		$orig_hash = substr($hash, 0, 16);
		$salt = substr($hash, 16);

		$new_hash = mhash(MHASH_MD5,$form_val . $salt);
		//echo '<br>  DB: ' . base64_encode($orig_hash) . '<br>FORM: ' . base64_encode($new_hash);

		if(strcmp($orig_hash,$new_hash) == 0)
		{
			return True;
		}
		return False;
	}

	/**
	 * compare SHA-encrypted passwords for authentication
	 * 
	 * @param string $form_val user input value for comparison
	 * @param string $db_val   stored value (from database)
	 * @return boolean True on successful comparison
	*/
	static function sha_compare($form_val,$db_val)
	{
		/* Start with the first char after {SHA} */
		$hash = base64_decode(substr($db_val,5));
		$new_hash = mhash(MHASH_SHA1,$form_val);
		//echo '<br>  DB: ' . base64_encode($orig_hash) . '<br>FORM: ' . base64_encode($new_hash);

		if(strcmp($hash,$new_hash) == 0)
		{
			return True;
		}
		return False;
	}

	/**
	 * compare SSHA-encrypted passwords for authentication
	 * 
	 * @param string $form_val user input value for comparison
	 * @param string $db_val   stored value (from database)
	 * @return boolean	 True on successful comparison
	*/
	static function ssha_compare($form_val,$db_val)
	{
		/* Start with the first char after {SSHA} */
		$hash = base64_decode(substr($db_val, 6));

		// SHA-1 hashes are 160 bits long
		$orig_hash = substr($hash, 0, 20);
		$salt = substr($hash, 20);
		$new_hash = mhash(MHASH_SHA1, $form_val . $salt);

		if(strcmp($orig_hash,$new_hash) == 0)
		{
			return True;
		}
		return False;
	}

	/**
	 * compare crypted passwords for authentication whether des,ext_des,md5, or blowfish crypt
	 * 
	 * @param string $form_val user input value for comparison
	 * @param string $db_val   stored value (from database)
	 * @param string $type     crypt() type
	 * @return boolean	 True on successful comparison
	*/
	static function crypt_compare($form_val,$db_val,$type)
	{
		$saltlen = array(
			'blowfish_crypt' => 16,
			'md5_crypt' => 12,
			'ext_crypt' => 9,
			'crypt' => 2
		);

		// PHP's crypt(): salt + hash
		// notice: "The encryption type is triggered by the salt argument."
		$salt = substr($db_val, 0, (int)$saltlen[$type]);
		$new_hash = crypt($form_val, $salt);

		if(strcmp($db_val,$new_hash) == 0)
		{
			return True;
		}
		return False;
	}

	/**
	 * compare md5_hmac-encrypted passwords for authentication (see RFC2104)
	 * 
	 * @param string $form_val user input value for comparison
	 * @param string $db_val   stored value (from database)
	 * @param string $key       key for md5_hmac-encryption (username for imported smf users)
	 * @return boolean	 True on successful comparison
	 */
	static function md5_hmac_compare($form_val,$db_val,$key)
	{
		$key = str_pad(strlen($key) <= 64 ? $key : pack('H*', md5($key)), 64, chr(0x00));
		$md5_hmac = md5(($key ^ str_repeat(chr(0x5c), 64)) . pack('H*', md5(($key ^ str_repeat(chr(0x36), 64)). $form_val)));
		if(strcmp($md5_hmac,$db_val) == 0)
		{
			return True;
		}
		return False;
	}
}
