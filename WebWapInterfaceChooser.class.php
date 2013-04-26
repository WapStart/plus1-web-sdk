<?php
	/**
	* @author Evgeny Kokovikhin <e.kokovikhin@co.wapstart.ru>
	* @copyright Copyright (c) 2010, Wapstart
	*/
	final class WebWapInterfaceChooser
	{
		public static function isWapRequest()
		{
			$userAgent = self::getUA();
			
			if (!$userAgent)
				return false;

			return
				(
					!self::isDesktopWindows($userAgent)
					&& (
						self::hasUsualPart($userAgent)
						|| self::isWapSupported()
						|| self::hasAdditionalHeaders()
						|| self::isAppleDevice($userAgent)
						|| self::hasUsualHeaderPart($userAgent)
					)
				);
		}
		
		public static function isIphone()
		{
			$ua = self::getUA();
			
			if (!$ua)
				return false;
			
			return self::isAppleDevice($ua);
		}
		
		public static function isNokia()
		{
			$ua = self::getUA();
			
			if (!$ua)
				return false;
			
			return
				(
					strpos($ua, 'nokia') !== false
					|| strpos($ua, 'Nokia') !== false
				);
		}
		
		public static function isAndroid2Higher()
		{
			$ua = self::getUA();
			
			if (!$ua)
				return false;
			
			return preg_match('/android ([2-9]|\d{2})/', $ua);
		}
		
		public static function isOperaMini()
		{
			$ua = self::getUA();
			
			if (!$ua)
				return false;
			
			return
				(
					strpos($ua, 'mini') !== false
					|| isset($_SERVER['X-OperaMini-Features'])
				);
		}
		
		public static function isSymbian9Higher()
		{
			$ua = self::getUA();
			
			if (!$ua)
				return false;
			
			return preg_match('/symbianos\/(9|\d{2})/', $ua);
		}
		
		private static function getUA()
		{
			if (!isset($_SERVER['HTTP_USER_AGENT']))
				return null;

			$userAgent =
				mb_strtolower($_SERVER['HTTP_USER_AGENT']);

			if (!mb_strlen($userAgent))
				return null;

			return $userAgent;
		}

		private static function isDesktopWindows($userAgent)
		{
			return
				(
					strpos($userAgent, 'windows') !== false
					&& strpos($userAgent, 'windows ce') === false
				);
		}

		private static function hasUsualPart($userAgent)
		{
			$partList =
				array(
					'up.browser', 'up.link', 'windows ce', 'iemobile', 'mini',
					'mmp', 'symbian', 'midp', 'wap', 'phone', 'pocket',
					'mobile', 'pda', 'psp', 'android'
				);

			foreach ($partList as $part)
				if (strpos($userAgent, $part) !== false)
					return true;

			return false;
		}

		private  static function isWapSupported()
		{
			if (!isset($_SERVER['HTTP_ACCEPT']))
				return false;
			
			return
				(
					stripos(
						$_SERVER['HTTP_ACCEPT'],
						'text/vnd.wap.wml'
					) !== false
					|| stripos(
						$_SERVER['HTTP_ACCEPT'],
						'application/vnd.wap.xhtml+xml'
					) !== false
				);
		}

		private static function hasAdditionalHeaders()
		{
			return
				(
					isset($_SERVER['HTTP_X_WAP_PROFILE'])
					|| isset($_SERVER['HTTP_PROFILE'])
					|| isset($_SERVER['X-OperaMini-Features'])
					|| isset($_SERVER['UA-pixels'])
				);
		}

		private static function isAppleDevice($userAgent)
		{
			return
				(
					strpos($userAgent, 'iphone') !== false
					|| strpos($userAgent, 'ipod') !== false
				);
		}

		private static function hasUsualHeaderPart($userAgent)
		{
			$agentList =
				array(
					'acs-', 'alav', 'alca', 'amoi', 'audi', 'aste', 'avan',
					'benq', 'bird', 'blac', 'blaz', 'brew', 'cell', 'cldc',
					'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno', 'ipaq',
					'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d',
					'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef',
					'mobi', 'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki',
					'opwv', 'palm', 'pana', 'pant', 'pdxg', 'phil', 'play',
					'pluc', 'port', 'prox', 'qtek', 'qwap', 'sage', 'sams',
					'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
					'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb',
					't-mo', 'teli', 'tim-', 'tosh', 'treo', 'tsm-', 'upg1',
					'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
					'wapr', 'webc', 'winw',  'winw','xda-'
				);

			return
				in_array(substr($userAgent, 0, 4), $agentList);
		}
	}
	
?>