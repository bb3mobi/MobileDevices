<?php

/**
*
* @package Mobile Devices
* @copyright (c) bb3.mobi 2014 Anvar
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3mobi\MobileDevices\event;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @bb3mobi.MobileDevices.helper */
	protected $helper;

	public function __construct($helper)
	{
		$this->helper = $helper;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'			=> 'device_manage_link',
			'core.user_setup'					=> 'change_default_style',
			'core.acp_board_config_edit_add'	=> 'acp_board_config',
		);
	}

	public function device_manage_link($event)
	{
		$this->helper->change_mobile_style();
	}

	public function change_default_style($event)
	{
		if ($mobile_style = $this->helper->load_mobile_style())
		{
			$event['style_id'] = $mobile_style;
		}
	}

	// ACP Add new config, phpBB3.1 Anvar && bb3.mobi
	public function acp_board_config($event)
	{
		$mode = $event['mode'];
		if ($mode == 'settings' || $mode == 'server')
		{
			if ($mode == 'settings')
			{
				$new_config = array(
				'mobile_style' => array(
					'lang' => 'MOBILE_STYLE',
					'validate' => 'int',
					'type' => 'select',
					'function' => 'style_select',
					'params' => array('{CONFIG_VALUE}', false),
					'explain' => true),
				);
				$search_slice = 'override_user_style';
			}
			else
			{
				$new_config = array(
				'mobile_user_agent' => array(
					'lang' => 'MOBILE_STYLE_UAGENT',
					'validate' => 'string',
					'type' => 'textarea:5:40',
					'explain' => true),
				);
				$search_slice = 'use_system_cron';
			}
			$display_vars = $event['display_vars'];
			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $new_config, array('after' => $search_slice));
			$event['display_vars'] = array('title' => $display_vars['title'], 'vars' => $display_vars['vars']);
		}
	}
}
