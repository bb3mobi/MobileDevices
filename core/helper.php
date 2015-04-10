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
		$url_style = '';
		$s_mobile_device = false;

		$request_var = $this->request->variable(self::VER, '');

		$style_cookie = $this->request_cookie(self::VER, $request_var);
		$detect_mobile_device = $this->detect_mobile_device();

		if (($style_cookie == self::MOBI && $detect_mobile_device) || ($style_cookie == self::WEB && !$detect_mobile_device))
		{
			$this->redirect_cookie(self::VER, '');
		}

		if (($detect_mobile_device && !$style_cookie) || $style_cookie == self::MOBI)
		{
			$url_style = $this->request_url(self::VER . '=' . self::WEB);
			if ($request_var)
			{
				$url_style = $this->request_url(self::VER . '=' . self::MOBI, $url_style);
				// cookie add and redirect page
				redirect($this->redirect_cookie(self::VER, $request_var));
			}
			$s_mobile_device = true;
		}
		else if (!$detect_mobile_device || $style_cookie == self::WEB)
		{
			$url_style = $this->request_url(self::VER . '=' . self::MOBI);
			if ($request_var)
			{
				$url_style = $this->request_url(self::VER . '=' . self::WEB, $url_style);
				// cookie add and redirect page
				redirect($this->redirect_cookie(self::VER, $request_var));
			}
		}

		$this->user->add_lang_ext('bb3mobi/MobileDevices', 'device_style');

		$this->template->assign_vars(array(
			'U_DEVICE_LINK'		=> $url_style,
			'L_DEVICE_NAME'		=> (!$s_mobile_device) ? $this->user->lang('MOBILE_STYLE') : $this->user->lang('WEB_STYLE'),
			'S_MOBILE_DEVICE'	=> $detect_mobile_device,
			'S_WEB_DEVICE'		=> (!$s_mobile_device) ? true : false)
		);
	}

	/** Detect and default style mobile */
	public function load_mobile_style()
	{
		if (!$this->config['override_user_style'] && $this->mobile_style())
		{
			// FIX Demo ACP and Quick Style Ext
			$style_fix = $this->request_cookie('style');
			if (($this->config['default_style'] == $this->user->data['user_style']) || ($this->user->data['user_id'] == ANONYMOUS && !$style_fix))
			{
				$style_cookie = $this->request_cookie(self::VER, '');
				if (($this->detect_mobile_device() && $style_cookie != self::WEB) || $style_cookie == self::MOBI)
				{
					// Set the style to display
					return $this->config['mobile_style'];
				}
			}
		}
		return false;
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
		return false;
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

	/** Cookie request */
	private function request_cookie($name, $default = false)
	{
		$name = $this->config['cookie_name'] . '_' . $name;
		return $this->request->variable($name, $default, false, 3);
	}

	/** Cookie set and return to session page */
	private function redirect_cookie($name, $request)
	{
		$time = ($request) ? 0 : time() + 31536000;
		$this->user->set_cookie($name, $request, $time);

		if ($request)
		{
			$redirect = $this->request->variable('redirect', $this->user->data['session_page']);
			//$redirect = $this->request_url($name . '=' . $request, build_url());
			if (strrpos($this->user->data['session_page'], 'app.') === 0)
			{
				$redirect = generate_board_url();
			}
			return reapply_sid(urldecode($redirect));
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
				$url_delim = array('&amp;' . $get_path, '&' . $get_path, '?' . $get_path, $get_path . '&amp;', $get_path . '&');
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
