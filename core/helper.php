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

	public function __construct(\phpbb\config\config $config, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	public function change_mobile_style()
	{
		$request_var = $this->request->variable(self::VER, '');

		$user_style = $this->request->variable($this->config['cookie_name'] . '_' . self::VER, $request_var, false, 3);

		// Detect user devices
		$detect_mobile_device = $this->detect_mobile_device();

		// User registered invert style
		if ($this->user->data['is_registered'])
		{
			$user_style_id = ($user_style == self::MOBI) ? $this->config['mobile_style'] : $this->config['default_style'];

			if ($user_style && $user_style_id != $this->user->data['user_style'])
			{
				$this->styles_change(($user_style == self::MOBI) ? $this->config['mobile_style'] : $this->config['default_style']);
			}

			$user_style = ($this->user->data['user_style'] == $this->config['mobile_style']) ? self::MOBI : self::WEB;
		}

		// Delete cookies
		if (($user_style == self::MOBI && $detect_mobile_device) || ($user_style == self::WEB && !$detect_mobile_device))
		{
			$this->user->set_cookie(self::VER, false, time());
		}

		if ($request_var)
		{
			if ($this->user->data['is_registered'])
			{
				// change user style
				$this->styles_change(($request_var == self::MOBI) ? $this->config['mobile_style'] : $this->config['default_style']);
			}
			else
			{
				// cookie guest style
				$this->user->set_cookie(self::VER, $request_var, time() + 360000);
			}
			// redirect page
			$redirect = $this->request_url(self::VER . '=' . $request_var, build_url());
			redirect(urldecode($redirect), false, true);
		}

		$s_web_device = true;
		$url_style = $this->request_url(self::VER . '=' . self::MOBI);
		if (($detect_mobile_device && !$user_style) || $user_style == self::MOBI)
		{
			$url_style = $this->request_url(self::VER . '=' . self::WEB);
			$s_web_device = false;
		}

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
			if ($user_style == self::MOBI || ($user_style != self::WEB && !$this->user->data['is_registered'] && $this->detect_mobile_device()))
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

	/** Change User Style */
	private function styles_change($style)
	{
		global $db;

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_style = " . (int) $style . "
			WHERE user_id = {$this->user->data['user_id']}";
		$db->sql_query($sql);
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

	/** Url get param add and clear */
	private function request_url($get_path = false, $url_clear = false)
	{
		$url = ($url_clear) ? $url_clear : build_url();
		if ($get_path)
		{
			if ($url_clear)
			{
				$url_delim = array('&amp;' . $get_path, '&' . $get_path, $get_path . '&amp;', $get_path . '&', '?' . $get_path);
				$url = str_replace($url_delim, "", $url);
			}
			else
			{
				$url_delim = (strpos($url, '?') === false) ? '?' : ((strpos($url, '?') === strlen($url) - 1) ? '' : '&amp;');
				$url .= $url_delim . $get_path;
			}
		}
		return $url;
	}
}
