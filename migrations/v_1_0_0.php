<?php

/**
*
* @package Mobile Devices
* @copyright (c) bb3.mobi 2014 Anvar
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3mobi\MobileDevices\migrations;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

class v_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['mobile_version']) && version_compare($this->config['mobile_version'], '1.0.0', '>=');
	}
	
	public function update_data()
	{
		return array(
			/* Based configuration Nekstati, http://www.phpbbguru.net/community/topic29334.html */
			array('config.add', array('mobile_style', '')),
			array('config.add', array('mobile_user_agent', 'alcatel, ericsson, motorola, nokia, panasonic, philips, samsung, sanyo, sharp, sony, j2me, midp, wap, pda, series60, symbian, android, vodafone, 240x320, 320x240, mobile, opera mini, opera mobi, phone, up.browser, up.link, xiino/i')),

			// Current version
			array('config.add', array('mobile_version', '1.0.0')),
		);
	}
}
