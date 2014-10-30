<?php
/**
 * EGroupware: Postfix with Univention mailAccount schema
 *
 * @link http://www.egroupware.org
 * @package emailadmin
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2014 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id4$
 */

/**
 * Postfix with Univention mailAccount schema
 */
class emailadmin_smtp_univention extends emailadmin_smtp_ldap
{
	/**
	 * Capabilities of this class (pipe-separated): default, forward
	 */
	const CAPABILITIES = 'default|forward';

	/**
	 * Name of schema, has to be in the right case!
	 */
	const SCHEMA = 'univentionMail';

	/**
	 * Attribute to enable mail for an account, OR false if existence of ALIAS_ATTR is enough for mail delivery
	 */
	const MAIL_ENABLE_ATTR = 'mailprimaryaddress';

	/**
	 * Attribute value to enable mail for an account, OR false if existense of attribute is enough to enable account
	 */
	const MAIL_ENABLED = self::MAIL_ENABLED_USE_MAIL;

	/**
	 * Attribute for aliases OR false to use mail
	 */
	const ALIAS_ATTR = 'mailalternateaddress';

	/**
	 * Primary mail address required as an alias too: true or false
	 */
	const REQUIRE_MAIL_AS_ALIAS=false;

	/**
	 * Attribute for forwards OR false if not possible
	 */
	const FORWARD_ATTR = false;

	/**
	 * Attribute to only forward mail, OR false if not available
	 */
	const FORWARD_ONLY_ATTR = false;
	/**
	 * Attribute value to only forward mail
	 */
	const FORWARD_ONLY = false;

	/**
	 * Attribute for mailbox, to which mail gets delivered OR false if not supported
	 */
	const MAILBOX_ATTR = false;

	/**
	 * Attribute for quota limit of user in MB
	 */
	const QUOTA_ATTR = 'univentionmailuserquota';

	/**
	 * Log all LDAP writes / actions to error_log
	 */
	var $debug = false;
}
