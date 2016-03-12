<?php

/**
*
* @package Mobile Devices
* @copyright (c) bb3.mobi 2014 Anvar
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace bb3mobi\MobileDevices\core;

class helper
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** Get type */
	const VER = 'ver';
	/** Get web version */
	const WEB = 'web';
	/** Get mobile version */
	const MOBI = 'mobi';

	public function __construct(\phpbb\config\config $config, \phpbb\request\request_interface $request, \phpbb\path_helper $path_helper, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->request = $request;
		$this->phpbb_path_helper = $path_helper;
		$this->template = $template;
		$this->user = $user;
	}

	public function change_mobile_style()
	{
		$request_var = $this->request->variable(self::VER, '');

		$user_style = $this->request->variable($this->config['cookie_name'] . '_' . self::VER, $request_var, false, 3);

		// Detect user devices
		$detect_mobile_device = $this->detect_mobile_device();

		// Delete cookies
		if (($user_style == self::MOBI && $detect_mobile_device) || ($user_style == self::WEB && !$detect_mobile_device))
		{
			$this->user->set_cookie(self::VER, false, time());
		}

		if ($request_var)
		{
			$this->user->set_cookie(self::VER, $request_var, time() + 360000);
			// redirect page
			//redirect(append_sid(build_url('ver'), false, false, false, true));//, false, true); // urldecode($redirect)
			redirect(build_url('ver'));
		}

		$s_web_device = true;
		$url_style = build_url('style');
		$url_params = array(self::VER => self::MOBI);
		if (($detect_mobile_device && !$user_style) || $user_style == self::MOBI)
		{
			$url_params = array(self::VER => self::WEB);
			$s_web_device = false;
		}
		$url_style = $this->phpbb_path_helper->append_url_params($url_style, $url_params);

		$this->user->add_lang_ext('bb3mobi/MobileDevices', 'device_style');

		$this->template->assign_vars(array(
			'U_DEVICE_LINK'		=> $url_style,
			'L_DEVICE_NAME'		=> ($s_web_device) ? $this->user->lang('MOBILE_STYLE') : $this->user->lang('WEB_STYLE'),
			'S_MOBILE_DEVICE'	=> $detect_mobile_device,
			'S_WEB_DEVICE'		=> $s_web_device)
		);
	}

	/** Detect and default style mobile */
	public function load_mobile_style()
	{
		if ($this->mobile_style()) // && !$this->config['override_user_style']
		{
			// FIX Demo ACP and Quick Style Ext
			if ($this->request->variable($this->config['cookie_name'] . '_style', false, false, 3))
			{
				return false;
			}

			$user_style = $this->request->variable($this->config['cookie_name'] . '_' . self::VER, '', false, 3);
			if ($user_style == self::MOBI || ($user_style != self::WEB && $this->detect_mobile_device()))
			{
				// Set the style to display
				return $this->config['mobile_style'];
			}
		}
	}

	/** Detect Mobile Devices */
	private function detect_mobile_device()
	{
		$mobile_user_agent = explode(',', chop($this->config['mobile_user_agent'], ' ,'));
		$user_browser = $this->request->server('HTTP_USER_AGENT');
		foreach ($mobile_user_agent as $mobile_browser)
		{
			if (stripos($user_browser, $mobile_browser) !== false)
			{
				return true;
			}
		}
	}

	/** Default User Style */
	private function default_style()
	{
		if ($this->user->data['is_registered'] || !$this->config['guest_style'])
		{
			return $this->config['default_style'];
		}
		else
		{
			return $this->config['guest_style'];
		}
	}

	/** Mobile Style Default */
	private function mobile_style()
	{
		if ($this->default_style() != $this->config['mobile_style'])
		{
			return $this->config['mobile_style'];
		}
	}
}
