<?php
/* $Id: WebPlus1BannerAskerMin.class.php 75550 2011-05-16 14:46:18Z anisimovt $ */
	
	class WebPlus1BannerAsker extends Plus1BannerAsker
	{
		public static function create()
		{
			return new self();
		}

		public function __construct()
		{
			$this->formatList['jsBanner'] = 'jsBanner';
		}
		
		public function fetch($position = null)
		{
			if (WebWapInterfaceChooser::isWapRequest()) {
				if (
					WebWapInterfaceChooser::isIphone()
					|| WebWapInterfaceChooser::isNokia()
					|| WebWapInterfaceChooser::isNokia()
					|| (
							WebWapInterfaceChooser::isAndroid2Higher()
							&& !WebWapInterfaceChooser::isOperaMini()
					)
					|| WebWapInterfaceChooser::isSymbian9Higher()
				)
					return $this->getJsCode($position);
				else
					return parent::fetch($position);
			}
			
			return null;
		}
		
		private function getJsCode($position)
		{
			$this->setFormat('jsBanner');

			return '
	<script>var plus1Banner = null;</script>
	<script type="text/javascript" src="'.$this->getBaseRotatorUri().'?area=jsStatic" charset="utf-8"></script>
	<script type="text/javascript" src="'.$this->getUrl().'" charset="utf-8"></script>
	<script>
	if (plus1Banner != null && typeof(BannerFloatingPlugin) != \'undefined\') {
		var bannerFloatingPlugin = new BannerFloatingPlugin();
		bannerFloatingPlugin.initBanner(plus1Banner, 0);
	}
	</script>';
		}
	}
?>
