<?php
/* $Id: Plus1BannerAskerMin.class.php 85718 2012-07-30 12:32:48Z n.konstantinov $ */
	
	class Plus1BannerAsker
	{
		const COOKIE_NAME			= 'ws_user_id';
		const MARKUP_WML		= 2;
		const MARKUP_XHTML		= 3;
		const TOP_POSITION		= 1;
		const BOTTOM_POSITION	= 2;
		private $rotatorUrl	= 'http://ro.plus1.wapstart.ru/';
		private $socketTimeout	= 5;
		private $streamTimeout	= 5;
		private static $pageId;
		private $site			= null;
		private $wml			= false;
		private $lastFetchTime	= null;
		private $tplVersion 		= 2;
		private static $userAgent	= null;
		private $sendClientSession	= true;
		private $login				= null;

		public static function create()
		{
			return new self();
		}

		public static function setCookie()
		{
			if (isset($_COOKIE[self::COOKIE_NAME]))
				return;

			setcookie(self::COOKIE_NAME, sha1(microtime(true).self::getSpecialHeaders()), time() + 30000000, '/');
		}

		public function setSite($site)
		{
			$this->site = $site;
			return $this;
		}

		public function setSocketTimeout($socketTimeout)
		{
			$this->socketTimeout = $socketTimeout;
			return $this;
		}

		public function setStreamTimeout($streamTimeout)
		{
			$this->streamTimeout = $streamTimeout;
			return $this;
		}

		public function setPageId($pageId)
		{
			self::$pageId = $pageId;
			return $this;
		}

		public function setWml()
		{
			$this->wml = true;
			return $this;
		}

		public function setXhtml()
		{
			$this->wml = false;
			return $this;
		}

		public function setTplVersion($version = 2)
		{
			$this->tplVersion = $version;
			return $this;
		}
		
		public function setSendClientSession($orly = true)
		{
			$this->sendClientSession = ($orly === true);
			return $this;
		}

		public function setLogin($login)
		{
			$this->login = $login;
			return $this;
		}

		public function fetchCode($position = null)
		{
			if (!$position)
				$position = self::TOP_POSITION;
			
			$plus1Url = $this->rotatorUrl.'?'.$this->makeUri($position);
			$rawResponse = null;
			
			if (($rawResponse = $this->fetch($plus1Url)) && strpos($rawResponse, '<!-- i4jgij4pfd4ssd -->') === false) {
					$rawResponse = null;
			}
			return $rawResponse;
		}
		
		protected function getRotatorUrl()
		{
			return $this->rotatorUrl;
		}
		
		protected function makeUri($position)
		{
			if (!self::$pageId)
				$this->setPageId(self::generatePageId());
			
			return
				$this->getPlace().'&position='.$position.'&markup='. ($this->wml ? self::MARKUP_WML : self::MARKUP_XHTML)
				.self::getSpecialHeaders().(($phoneNumber = self::findPhone()) ? '&phoneNumber='.$phoneNumber : null)
				.(isset($_SERVER['REMOTE_ADDR']) ? '&ip='.$_SERVER['REMOTE_ADDR'] : null)
				.(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? '&xfip='.urldecode($_SERVER['HTTP_X_FORWARDED_FOR']) : null)
				.($this->tplVersion ? '&tplVersion='.$this->tplVersion : null) .'&pageId='.self::$pageId
				.($this->sendClientSession ? '&clientSession='.$this->getSession() : null)
				.($this->login ? '&login='.urlencode($this->login) : null);
		}
		
		private function fetch($url)
		{
			$startTime = microtime(true);
			$header = self::makeHeader($url);
			$bannerHost = parse_url($url, PHP_URL_HOST);
			$headerBufferSize = 1024;
			$bufferSize = 65536;
			$rawResponse = null;
			
			try {
				$errorId = 0;
				$errorString = null;
				$fp = @fsockopen($bannerHost, 80, $errorId, $errorString, $this->socketTimeout);
				
				if ($fp && $errorId == 0) {
					$timeoutSeconds = floor($this->streamTimeout);
					$timeoutMicroseconds = floor(($this->streamTimeout - $timeoutSeconds) * 1000000);
					stream_set_timeout($fp, $timeoutSeconds, $timeoutMicroseconds);
					@fputs($fp, $header, strlen($header));
					$bannerData = @fgets($fp, $headerBufferSize);
					
					if (preg_match('~^HTTP\S+\s200\D~i', $bannerData)) {
						$rawResponse = @fread($fp, $bufferSize);
						$rawResponse = trim(strstr($rawResponse, '<'));
					}
				}
			} catch (Exception $e) {/*_*/}
			
			$this->lastFetchTime = microtime(true) - $startTime;
			return $rawResponse;
		}
		
		private static function makeHeader($url)
		{
			$bannerHost = parse_url($url, PHP_URL_HOST);
			
			return
				'GET '.$url." HTTP/1.0\r\n".'Host: '.$bannerHost."\r\n"
				.((isset($_SERVER['SERVER_NAME']) && isset($_SERVER['DOCUMENT_URI'])) ? "Referer: http://{$_SERVER['SERVER_NAME']}{$_SERVER['DOCUMENT_URI']}\r\n" : null)
				.(self::$userAgent ? "User-Agent:".self::$userAgent."\r\n" : null)
				.(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? "X-Forwarded-For: {$_SERVER['HTTP_X_FORWARDED_FOR']}\r\n" : null)
				.(isset($_SERVER['HTTP_VIA']) ? "Http-Via: {$_SERVER['HTTP_VIA']}\r\n" : null)
				."Connection: Close\r\n"."\r\n";
		}
		
		private function getPlace()
		{
			if ($this->site)
				return 'site='.$this->site;
			return null;
		}
		
		private static function findPhone()
		{
			$phoneHeaders = array('HTTP_MSISDN', 'HTTP_X_MSISDN', 'HTTP_X_NOKIA_MSISDN', 'HTTP_X_WAP_NETWORK_CLIENT_MSISDN', 'HTTP_X_UP_CALLING_LINE_ID', 'HTTP_X_NETWORK_INFO');
			$phoneList = array();
			
			foreach ($phoneHeaders as $key) {
				if (isset($_SERVER[$key]) && preg_match('/\d{10,16}/', $_SERVER[$key], $matches))
					$phoneList[] = $matches[0];
			}
			return (count(array_unique($phoneList)) == 1) ? $phoneList[0] : null;
		}
		
		private static function generatePageId()
		{
			return sha1((isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null).(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null).(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null).(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null).mt_rand(1, 10000000).microtime(true));
		}
		
		private static function getSpecialHeaders()
		{
			$specialHeadersResult = null;
			$specialHeaders = array('HTTP_X_WAP_PROFILE', 'HTTP_PROFILE', 'HTTP_X_OS_PREFS', 'HTTP_MSISDN', 'HTTP_X_MSISDN', 'HTTP_X_NOKIA_MSISDN', 'HTTP_X_WAP_NETWORK_CLIENT_MSISDN', 'HTTP_X_UP_CALLING_LINE_ID', 'HTTP_X_NETWORK_INFO', 'HTTP_UA_PIXELS', 'HTTP_UA_COLOR', 'HTTP_UA_OS', 'HTTP_UA_CPU', 'HTTP_UA_VOICE', 'HTTP_X_NOKIA_BEARER', 'HTTP_X_NOKIA_GATEWAY_ID', 'HTTP_X_NOKIA_WIA_ACCEPT_ORIGINAL', 'HTTP_X_NOKIA_CONNECTION_MODE', 'HTTP_X_NOKIA_WTLS', 'HTTP_X_WAP_PROXY_COOKIE', 'HTTP_X_WAP_TOD_CODED', 'HTTP_X_WAP_TOD', 'HTTP_X_UNIQUEWCID', 'HTTP_WAP_CONNECTION', 'HTTP_X_WAP_GATEWAY', 'HTTP_X_WAP_SESSION_ID', 'HTTP_X_WAP_NETWORK_CLIENT_IP', 'HTTP_X_WAP_CLIENT_SDU_SIZE', 'HTTP_ACCEPT_APPLICATION',	'HTTP_BEARER_INDICATION');
			
			if (isset($_SERVER['HTTP_X_OPERAMINI_FEATURES'])) {
				$specialHeadersResult = '&operaMini=1';
					
				if (isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])) {
					self::$userAgent = $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'];
					$specialHeadersResult .= '&userAgent='.urlencode(self::$userAgent);
				}
				
				if (isset($_SERVER['HTTP_X_OPERAMINI_PHONE'])) {
					$specialHeadersResult .='&operaMiniPhone='.urlencode($_SERVER['HTTP_X_OPERAMINI_PHONE']);
				}
			} else {
				foreach ($specialHeaders as $specialHeader) {
					if (isset($_SERVER[$specialHeader])) {
						$specialHeadersResult =	'&specialHeader='.urlencode($specialHeader.':::'.$_SERVER[$specialHeader]);
					}
					break;
				}
				if (isset($_SERVER['HTTP_USER_AGENT'])) {
					self::$userAgent = $_SERVER['HTTP_USER_AGENT'];
					$specialHeadersResult .='&userAgent='. urlencode(self::$userAgent);
				}
			}
			return $specialHeadersResult;
		}
		
		
		private function getSession()
		{
			if (isset ($_COOKIE[self::COOKIE_NAME]))
				return $_COOKIE[self::COOKIE_NAME];
			if (!session_id()) {
				//headers may be already sent
				try { @session_start(); }
				catch (Exception $e) {/*boo*/}
			}
			return sha1(session_id());
		}
	}

	Plus1BannerAsker::setCookie();
?>
