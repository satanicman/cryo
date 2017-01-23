<?php

if (!defined('_PS_VERSION_'))
	exit;		

include_once(_PS_MODULE_DIR_.'homesliderpro/HomeSlidePro.php');
include_once(_PS_MODULE_DIR_.'homesliderpro/classes/PerfectResizer.php');

class HomeSliderPro extends Module
{
	private $_html = '';
	public $standardHooks;
	public $baseHooks;
	private $counter;
	private $activateCat = FALSE;
	private $categorySlide;
	public $defaultConf;
	public $settings;
	public $config;
	public $warning;
	public $processingUpdate = FALSE;
	public $isPS6 = false;
	public $path;
	public $_smarty;
	

	public function __construct()
	{
		$this->name = 'homesliderpro';
		$this->tab = 'front_office_features';
		$this->version = '1.6.34';
		$this->author = 'Syncrea';
		$this->need_instance = 0;
		$this->secure_key = Tools::encrypt($this->name);
		
		$this->displayName = '!'.$this->l('Sliders Everywhere!');
		$this->description = $this->l('Add image sliders everywhere you want.');
		
		$this->path = $this->_path;
		$this->_smarty = $this->smarty;
				
		$settings = Configuration::get('SLIDERSEVERYWHERE_SETS');
		
		$this->context = Context::getContext();
		
		$this->defaultConf = array(
			'width' => 1200,
			'height' => 520,
			'show_title' => 1,
			'controls' => 1,
			'pager' => 1,
			'speed' => 500,
			'auto' => 1,
			'pause' => 3000,
			'mode' => 'horizontal', //'horizontal', 'vertical', 'fade',
			'loop' => 1,
			'direction' => 'next', //autoDirection: 'next', 'prev'
			'title_pos' => 1,
			'autoControls' => 0,
			'restartAuto' => 0,
			'idCat' => 0,
			'max_slides' => 3,
			'min_slides' => 1,
			'margin'=> 0,
			'media' => array(
				'max' => array(
					'tspace' => 0,
					'bspace' => 0,
					'lspace' => 0,
					'rspace' => 0,
					'pos' => 0,
					'swidth' => 100,
				),
				'1199' => array(
					'tspace' => 0,
					'bspace' => 0,
					'lspace' => 0,
					'rspace' => 0,
					'pos' => 0,
					'swidth' => 100,
				),
				'989' => array(
					'tspace' => 0,
					'bspace' => 0,
					'lspace' => 0,
					'rspace' => 0,
					'pos' => 0,
					'swidth' => 100,
				),
			),
			'color' => array(
				'titlebg' => 'rgba(0, 0, 0, 0.5)',
				'titlec' => '#fff',
				'descbg' => 'rgba(0, 0, 0, 0.5)',
				'descc' => '#fff',
				'arrowbg' => 'rgba(0, 0, 0, 0.5)',
				'arrowc' => '#fff',
				'arrowg' => '#fff',
				'pagerc' => '#0090f0',
				'pagerac' => '#ffa500',
				'pagerbc' => 'rgba(0, 0, 0, 0.5)',
				'pagerhbc' => 'transparent',
				'pagerhg' => '#fff',
			)
		);
		
		
		
		if (!empty($settings)) { // settings exists, is not new install
			$this->settings = unserialize($settings);
			if ($this->settings['version'] != $this->version){ //stored version differ
				$checkVersion = version_compare($this->settings['version'], $this->version);
				if ($checkVersion < 0) {
					@$this->runUpgradeModule($this->name);
					if ($this->upgradeDb()) {
						$this->settings['version'] = $this->version;
						Configuration::updateValue('SLIDERSEVERYWHERE_SETS', serialize($this->settings));
					}
				} else {
					$this->settings['version'] = $this->version;
					Configuration::updateValue('SLIDERSEVERYWHERE_SETS', serialize($this->settings));
				}
			}
		} else {
			$this->settings = array(
				'version' => $this->version,
				'need_update' => 0,
				'update_time' => 0,
				'CMS' => 0,
				'CAT' => 0,
				'permissions' => array(
					'hooks' => 0,
					'sizes' => 0,
				),
			);
			Configuration::updateValue('SLIDERSEVERYWHERE_SETS', serialize($this->settings));
		}
		
		$this->config = $this->getConfiguration();

		$this->standardHooks = unserialize(Configuration::get('HOMESLIDERPRO_STANDARD', null, null, (int)$this->getShopId()));
		
		$this->counter = 0;
		
		$hooks = Configuration::get('HOMESLIDERPRO_HOOKS', null, null, (int)$this->getShopId());
		if (!empty($hooks))
			$this->hook = unserialize($hooks);
		
		$this->baseHooks = array(
			0 => 'displayTop',
			1 => 'displayHome',
			2 => 'displayLeftColumn',
			3 => 'displayLeftColumnProduct',
			4 => 'displayRightColumn',
			5 => 'displayRightColumnProduct',
			6 => 'displayFooter',
			7 => 'displayFooterProduct',
		);
		
		$checkVersion = version_compare(_PS_VERSION_, '1.6');
		if ($checkVersion >= 0){
			$this->isPS6 = true;
		}
		
		if ($this->isPS6){
			$this->baseHooks[8] = 'displayTopColumn';
			$this->baseHooks[9] = 'displayHomeTabContent';
			$this->baseHooks[10] = 'displayProductTab';
			$this->baseHooks[11] = 'displayShoppingCartFooter';
			$this->baseHooks[12] = 'displayBanner';
		}
		
		if (!$this->processingUpdate){
			if ($this->settings['need_update'] && version_compare($this->settings['need_update'], $this->version) > 0 ){
				$this->warning = ' '.$this->l('New Update Available! Visit configuration page to update:').'<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Click').'</a>';
			} else {
				$this->settings['need_update'] = 0;
				Configuration::updateValue('SLIDERSEVERYWHERE_SETS', serialize($this->settings));
				$this->updateCheck();
			}
		}

		parent::__construct();
		
	}
	
	
	/**
	 * @see Module::install()
	 */
	public function install()
	{
		
		/* Adds Module */
		if (parent::install() 
			&& $this->registerHook('displayHeader')
			&& $this->registerHook('displayHome') 
			&& $this->registerHook('displayTop') 
			&& $this->registerHook('displayLeftColumn')
			&& $this->registerHook('displayLeftColumnProduct')
			&& $this->registerHook('displayRightColumn')
			&& $this->registerHook('displayRightColumnProduct')
			&& $this->registerHook('displayFooter')
			&& $this->registerHook('displayFooterProduct')
			&& $this->registerHook('displaySlidersPro')
			&& $this->registerHook('actionShopDataDuplication')
			&& $this->registerHook('displayBackOfficeHeader')
			)
		{
			
			if ($this->isPS6){
				$this->registerHook('displayTopColumn');
				$this->registerHook('displayHomeTabContent');
				$this->registerHook('displayProductTab');
				$this->registerHook('displayShoppingCartFooter');
				$this->registerHook('displayBanner');
			}
		
			/* Sets up fake tab to override CMS content Tab */
			$tab = new Tab();
			$tab->class_name = 'AdminCmsContent';
			$tab->id_parent = Tab::getIdFromClassName('AdminPreferences');
			$tab->active =  false; //this is a override not a real tab we just create it to insert our controller
			$tab->module = $this->name;
			$tab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('CMS');

			if(!$tab->add())
				return false;
				
			$tab2 = new Tab();
			$tab2->class_name = 'SlidersEverywhere';
			$tab2->id_parent = Tab::getIdFromClassName('AdminPreferences');
			$tab2->module = $this->name;
			$tab2->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Sliders Everywhere');
			if( !$tab2->add())
				return false;
			/* Creates tables */
			$res = $this->createTables();

			/* Adds samples */
			if ($res)
				$this->installSamples();

			return $res;
		}
		return false;
	}

	/**
	 * Adds samples
	 */
	private function installSamples()
	{
		$languages = Language::getLanguages(false);
		$defaults['sample'] = $this->defaultConf;
		if ($this->isPS6) {
			$defaults['sample']['pos'] = 2;
			$defaults['sample']['media']['max']['lspace'] = 15;
			$defaults['sample']['media']['max']['rspace'] = 15;
		}
		$this->hook = array('sample');
		$standardHooks = array(
			'displayTop' => array( 0 => 'sample')
		);
		
		if (Shop::isFeatureActive() && $this->context->shop->getContext() == Shop::CONTEXT_ALL )
			$id_shops = Shop::getContextListShopID();
		else {
			$id_shops = array( 0 => $this->getShopId());
		}
		
		foreach ($id_shops as $id_shop) {
			$this->_html .= print_r(Shop::getCompleteListOfShopsID(),true).'<br/>';
			$this->_html .= print_r(Shop::getContextListShopID(),true).'<br/>';
			
			Configuration::updateValue('HOMESLIDERPRO_STANDARD', serialize($standardHooks), false, null, (int)$id_shop);
			Configuration::updateValue('HOMESLIDERPRO_HOOKS', serialize($this->hook), false, null, (int)$id_shop);

			$this->saveConfiguration($defaults, $id_shop);
			$folder = _PS_MODULE_DIR_.$this->name.'/images/';
			for ($i = 1; $i <= 5; ++$i)
			{
				$slide = new HomeSlidePro();
				$slide->position = $i;
				$slide->active = 1;
				$slide->id_hook = 'sample';
				$slide->has_area = 0;
				
				$resizeObj = new PerfectResize($folder.'sample-'.$i.'.jpg'); 
				$resizeObj->resizeImage($defaults['sample']['width'], $defaults['sample']['height'], 'crop');
				$resizeObj->saveImage($folder.'resize_'.'sample-'.$i.'.jpg', 90);
				$resizeObj->resizeImage(60, 40, 'crop');
				$resizeObj->saveImage($folder.'thumb_'.'sample-'.$i.'.jpg', 90);
				
				foreach ($languages as $language)
				{
					$slide->title[$language['id_lang']] = 'Sample '.$i;
					$slide->description[$language['id_lang']] = 'This is a sample picture';
					$slide->legend[$language['id_lang']] = 'sample-'.$i;
					$slide->url[$language['id_lang']] = 'http://www.syncrea.it';
					$slide->image[$language['id_lang']] = 'sample-'.$i.'.jpg';
					
				}
				$slide->add(true,false,(int)$id_shop);
			}
		}
	}

	/**
	 * @see Module::uninstall()
	 */
	public function uninstall()
	{
		/* Deletes Module */
		if (parent::uninstall())
		{
			/* Deletes tables */
			$res = $this->deleteTables();
			/* Unsets configuration */
			$res &= Configuration::deleteByName('HOMESLIDERPRO_CONFIG');
			$res &= Configuration::deleteByName('HOMESLIDERPRO_HOOKS');
			$res &= Configuration::deleteByName('HOMESLIDERPRO_STANDARD');
			$res &= Configuration::deleteByName('SLIDERSEVERYWHERE_SETS');
			
			$tab = new Tab(Tab::getIdFromClassName('AdminCmsContent'));
			if ($tab->delete()){
				// restore original CMS tab 
				$tab = new Tab(Tab::getIdFromClassName('AdminCmsContent'));
				$tab->active = true;
			};
			
			$tab2 = new Tab(Tab::getIdFromClassName('SlidersEverywhere'));
			if(!$tab2->delete()){
				return false;
			}			
			return $res;
		}
		
		return false;
	}

	/**
	 * Creates tables
	 */
	protected function createTables()
	{
		/* Slides */
		$res = (bool)Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'homesliderpro` (
				`id_homeslider_slides` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_shop` int(10) unsigned NOT NULL,
				`id_hook` varchar(255) NULL,
				PRIMARY KEY (`id_homeslider_slides`, `id_shop`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		');
		
		$res &= (bool)Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'sesliders_slideconf` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_shop` int(10) unsigned NOT NULL,
				`id_hook` varchar(255) NULL,
				`conf` text NULL,
				PRIMARY KEY (`id`, `id_hook`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		');

		/* Slides configuration */
		$res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'homesliderpro_slides` (
			  `id_homeslider_slides` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `position` int(10) unsigned NOT NULL DEFAULT \'0\',
			  `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			  `new_window` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			  `has_area` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			  PRIMARY KEY (`id_homeslider_slides`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		');

		/* Slides lang configuration */
		$res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'homesliderpro_slides_lang` (
			  `id_homeslider_slides` int(10) unsigned NOT NULL,
			  `id_lang` int(10) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `description` text NOT NULL,
			  `legend` varchar(255) NOT NULL,
			  `url` varchar(255) NOT NULL,
			  `image` varchar(255) NOT NULL,
			  `areas` text NULL,
			  PRIMARY KEY (`id_homeslider_slides`,`id_lang`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		');
		
		if (!$this->columnExists(_DB_PREFIX_.'cms','proslider')) {
			$res &= Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'cms`
				ADD proslider varchar(255) NULL
			');
		}
		
		if(!$this->columnExists(_DB_PREFIX_.'category','proslider')) {
			$res &= Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'category`
				ADD proslider varchar(255) NULL
			');
		}

		return $res;
	}

	/**
	 * deletes tables
	 */
	protected function deleteTables()
	{
		$slides = $this->getSlides();
		foreach ($slides as $slide)
		{
			$to_del = new HomeSlidePro($slide['id_slide']);
			$to_del->delete();
		}
		$res = Db::getInstance()->execute('
			DROP TABLE IF EXISTS 
				`'._DB_PREFIX_.'sesliders_slideconf`,
				`'._DB_PREFIX_.'homesliderpro`, 
				`'._DB_PREFIX_.'homesliderpro_slides`,
				`'._DB_PREFIX_.'homesliderpro_slides_lang`;
		');
		$res &= Db::getInstance()->execute('
			ALTER TABLE `'._DB_PREFIX_.'cms`
			DROP proslider
		');
		$res &= Db::getInstance()->execute('
			ALTER TABLE `'._DB_PREFIX_.'category`
			DROP proslider
		');
		return $res;
	}
	
	public function getConfiguration($hook = null, $id_shop = null){
		if (!$this->tableExists(_DB_PREFIX_.'sesliders_slideconf')){
			return;
		}
			
		if ($id_shop == null)
			$id_shop = $this->getShopId();
		
		$sql = 'SELECT id_hook, conf FROM `'._DB_PREFIX_.'sesliders_slideconf`
			WHERE `id_shop` = '.(int)$id_shop.'';
		if ($hook != null)
			$sql .= ' AND id_hook = "'.$hook.'"';
		
		if ($result = Db::getInstance()->executeS($sql)){
			$config = array();
			foreach ($result as $c){
				$config[$c['id_hook']] = unserialize($c['conf']);
			}
			return $config;
		}
		return false;
	}
	
	public function saveConfiguration($configuration = array(), $id_shop = null) {
		if (empty($configuration))
			return false;
		
		if ($id_shop == null)
			$id_shop = $this->getShopId();

		//check if database have more rows than configuration (some slider may have been deleted)
		if (count($this->config) > count($configuration)) {
			foreach ($this->config as $oldHook => $oc) {
				if (!array_key_exists($oc,$configuration)) {
					Db::getInstance()->delete('sesliders_slideconf', 'id_hook = "'.$oldHook.'" AND `id_shop` = '.(int)$id_shop, 1);
				}
			}
		}
		
		foreach ($configuration as $hook => $c){
			if (!Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'sesliders_slideconf WHERE id_hook = "'.$hook.'" AND id_shop = "'.$id_shop.'"') ){
				Db::getInstance()->insert('sesliders_slideconf', array(
					'id_hook' => pSQL($hook),
					'id_shop' => (int)$id_shop,
					'conf' => serialize($c),
					)
				);
			} else {
				Db::getInstance()->update('sesliders_slideconf', array(
					'conf' => serialize($c),
				), 'id_hook = "'.$hook.'" AND id_shop = "'.$id_shop.'"');
			}
		}
		return true;
	}
	
	public function deleteConfiguration($hook = null) {
		$id_shop = $this->getShopId();
		if (!empty($hook) && $hook != null)
			Db::getInstance()->delete('sesliders_slideconf', 'id_hook = "'.$hook.'" AND id_shop = "'.$id_shop.'"', 1);
	}
	
	public function getShopId(){
		$shopcontext = $this->context->cookie->shopContext;
		if (empty($shopcontext)) {
			$shop = $this->context->shop->id;
		} else
		if (strpos($shopcontext,'s-') === false){
			$shop = $this->context->shop->id;
		} else if (strpos($shopcontext,'s-') === 0){
			$shop = str_replace('s-','',$shopcontext);
		}
		return (int)$shop;
	}

	public function getContent()
	{
		$this->_html .= '<div id="SESlides">';
		$this->_postProcess();
		
		// check if tab exist (update from 1.3 to 1.4)
		$newtab = new Tab(Tab::getIdFromClassName('SlidersEverywhere'));	
		if ($newtab->class_name == ''){
			$tab2 = new Tab();
			$tab2->class_name = 'SlidersEverywhere';
			$tab2->id_parent = Tab::getIdFromClassName('AdminPreferences');
			$tab2->module = $this->name;
			$tab2->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Sliders Everywhere');
			$tab2->add();
		}
	
		if (!$this->columnExists(_DB_PREFIX_.'category','proslider')) {
			$this->_html .= '<div class="module_error alert error">
				'.$this->l('WARNING: Sliders Everywhere needs to update some database tables, please press the button below.').'
			</div>';
			$this->_html .= '<input type="button" class="button centered big" id="updateDb" value="'.$this->l('Update Now!').'"/>';
		}
		
		if ($this->settings['need_update'] && $this->settings['need_update'] != $this->version) {
			$this->_html .= '<div class="module_confirmation conf confirm">
			'.$this->l('NEW VERSION Available for ').$this->displayName.' (v:'.$this->settings['need_update'].')
			</div>';
			$this->_html .= '<form action="#" id="moduleUpdate" method="post"><input type="submit" class="button centered big" id="moduleUpdate" name="moduleUpdate" value="'.$this->l('Update Now!').'"/></form>';
		}
						
		$this->_html .= $this->headerHTML();

		$this->_html .= '<div class="toolbarBox toolbarHead">
			<div class="pageTitle">
				<h3><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/logo.png" alt="Logo" title="Put your sliders Everywhere!"/> '.$this->displayName.'
				<span class="small"><span class="small"><span class="small">
				(v:'.$this->version.')
				</span></span></span>
				</h3>
				<h4>Base Configuration</h4>
				<div>
				</div>
			</div>
		</div>';

		
		$this->_displayForm();
		
		$this->_html .= '</div>'; //wrapper
		
		return $this->_html;

	}

	private function _displayForm()
	{
				
		$confs = $this->config;
		$standardHooks = unserialize(Configuration::get('HOMESLIDERPRO_STANDARD', null, null, (int)$this->getShopId()));
		
		$currentUrl = parse_url($_SERVER["REQUEST_URI"]);
		
		$this->_html .= '<br/><form id="accessEdit">
			<fieldset>
				<legend>'.$this->l('Configure permissions').'</legend>
				
				<div class="margin-form clearfix"><label class="t">'.$this->l('Admin profile only').'</label></div>
				<label>'.$this->l('Show slider positions (hooks)').'</label>
				<div class="margin-form clearfix">
					<label class="t">
						<img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input type="radio" name="settings[permissions][hooks]" '.($this->settings['permissions']['hooks'] == 1 ? 'checked="checked"' : '').' value="1"/>
					</label>
					<label class="t">
						<img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input type="radio" name="settings[permissions][hooks]" '.($this->settings['permissions']['hooks'] == 0 ? 'checked="checked"' : '').' value="0"/>
					</label>
				</div>
				<label>'.$this->l('Edit slider sizes and timing').'</label>
				<div class="margin-form clearfix">
					<label class="t">
						<img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input type="radio" name="settings[permissions][sizes]" '.($this->settings['permissions']['sizes'] == 1 ? 'checked="checked"' : '').' value="1"/>
					</label>
					<label class="t">
						<img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input type="radio" name="settings[permissions][sizes]" '.($this->settings['permissions']['sizes'] == 0 ? 'checked="checked"' : '').' value="0"/>
					</label>
				</div>
			</fieldset>
		</form>
		<a href="'.$currentUrl['path'].'?controller=SlidersEverywhere&token='.Tools::getAdminTokenLite('SlidersEverywhere').'" class="button big centered">'.$this->l('Go to slider configuration').' <span class="fa fa-camera"></span></a><br/>';
		
		/** Genearl settings */
		
		$stringOld = '{$cms->content}';
		$stringNew = '{hook h="DisplaySlidersPro" CMS="1"}
'.$stringOld;
		
		$this->_html .= '<div class="notice"><p>'.$this->l('Sliders Everywere can now show a different slider for every CMS or CATEGORY page in your shop, in order to do that a little modification is required to your "cms.tpl" or "category.tpl" file.').'</p>
			<p><span class="red">'.$this->l('IMPORTANT').'</span>: '.$this->l('If you want your sliders to show on CMS or CATEGORY pages there are two methods, manual or automatic, here is provided an automatic activation, but it can fail depending on your theme. Clicking the activate button the system will try to make a backup copy of your file "cms.tpl" (or "category.tpl") of the active theme and will add the required code. The old file will be named "cms.tpl.bak" (or "category.tpl.bak") and will be located in your theme folder.').'</p>
			<p>'.$this->l('Deactivation will restore the backup, please use those functions with care, if you activate the slider and then manullay modify something in your theme when you restore the backup every change will be lost!!').'</p></div><br/>';
		
		$this->_html .= '<input type="button" id="showAct" class="button centered" value="'.$this->l('I understand that, please show me the activations methods!').'"/>';
		$this->_html .= '<div id="ajax"><table class="activations"><tr><td>
		<fieldset>
			<legend>'.$this->l('Sliders Activation for CMS Pages').'</legend>
			<fieldset>
				<legend>'.$this->l('Automatic Method').'</legend>';
				if (!isset($this->settings['CMS']) || $this->settings['CMS'] == 0){
				$this->_html .= '<form id="activateCMS" class="activationForm">
					<input class="button centered" type="submit"  value="Activate" name="activateCms"/>
					<div class="message" style="display:none;">'.$this->l('This action will search for a file named "cms.tpl" in your template and modify it, a backup file will be genreated with the name "cms.tpl.bak".').'</div>
				</form><br/>';
				} else {
				$this->_html .= '<form id="deactivateCMS" class="activationForm">
					<input class="button centered" type="submit"  value="DeActivate" name="deactivateCms"/>
					<div class="message" style="display:none;">'.$this->l('CAUTION: The cms.tpl file will be restored from a backup, if you modified it all your changes will be lost. Are you sure?').'</div>
				</form>';
				}
			$this->_html .= '</fieldset><br/>
			<fieldset>
				<legend>'.$this->l('Manual Method').'</legend>
				<p>'.$this->l('To manually activate the slider just replace in your "cms.tpl" file this code').':</p>
				<pre>'.htmlentities($stringOld).'</pre>
				<p>'.$this->l('With this').':</p>
				<pre>'.htmlentities($stringNew).'</pre>
			</fieldset>
		</fieldset></td>';
		
		
		if ($this->isPS6){ //we are on ps 1.6
			$stringOld = '{if $category->id AND $category->active}';
		} else { //we are on ps 1.5
			$stringOld = '{if $scenes || $category->description || $category->id_image}';
		}
		
		$stringNew = '{hook h="DisplaySlidersPro" CAT="1"}
'.$stringOld;
		
		$this->_html .= '<td>
		<fieldset>
			<legend>'.$this->l('Sliders Activation for CATEGORY Pages').'</legend>
			
			<fieldset>
				<legend>'.$this->l('Automatic Method').'</legend>';
				if (!isset($this->settings['CAT']) || $this->settings['CAT'] == 0){
					$this->_html .= '<form id="activateCat" class="activationForm">
						<input class="button centered" type="submit"  value="Activate" name="activateCat"/>
						<div class="message" style="display:none;">'.$this->l('This action will search for a file named "category.tpl" in your template and modify it, a backup file will be genreated with the name "category.tpl.bak".').'</div>
					</form>';
				} else {
					$this->_html .= '<form id="deactivateCat" class="activationForm">
						<input class="button centered" type="submit"  value="DeActivate" name="deactivateCat"/>
						<div class="message" style="display:none;">'.$this->l('CAUTION: The category.tpl file will be restored from a backup, if you modified it all your changes will be lost. Are you sure?').'</div>
					</form>';
				}
			$this->_html .= '</fieldset><br/>
			<fieldset>
				<legend>'.$this->l('Manual Method').'</legend>
				<p>'.$this->l('To manually activate the slider just replace in your "category.tpl" file this code').':</p>
				<pre>'.htmlentities($stringOld).'</pre>
				<p>'.$this->l('With this').':</p>
				<pre>'.htmlentities($stringNew).'</pre>
			</fieldset>
		</fieldset></td></tr></table>';
		
		/** End Genearl settings */

		$this->_html .= $this->getCreds();
	}
	
	private function updateCheck(){
		if (Tools::getValue('configure') == $this->name || Tools::getValue('controller') == 'AdminModules' || Tools::getValue('controller') == 'SlidersEverywhere') {
			$time = time();
			if (!isset($this->settings['update_time']) || $this->settings['update_time'] == 0) {
				$this->settings['update_time'] = $time;
				Configuration::updateValue('SLIDERSEVERYWHERE_SETS', serialize($this->settings));
			}
			if( $this->settings['update_time'] < ($time-(60*60*8)) || Tools::getValue('check') == 1) {
				$this->settings['need_update'] = 0;
				$this->settings['update_time'] = $time;
								
				$u = base64_decode('aHR0cDovL3N5bmNyZWEuaXQvZGV2ZWwvdXBkYXRlLnBocA==');
				
				$fields = array(
					'v' => urlencode($this->version),
					'p' => urlencode($this->getBaseUrl()),
					'n' => urlencode($this->name)
				);
				
				$fields_string = '';
				foreach($fields as $key=>$value) { 
					$fields_string .= $key.'='.$value.'&'; 
				}
				rtrim($fields_string, '&');
				$success = false;
				$result = 0;
				if(is_callable('curl_init')){
					$ch = curl_init();
					curl_setopt($ch,CURLOPT_URL, $u);
					curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch,CURLOPT_POST, count($fields));
					curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
					$result = curl_exec($ch);
					if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200){
						$success = true;
					}
					curl_close($ch);
				}  				
				if (!$success){
					if (ini_get("allow_url_fopen")) {
						if (function_exists("file_get_contents")){
							$con = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
							if (($result = @file_get_contents($u.'?'.$fields_string, false, $con )) !== false) {
								$success = true;
							}
						}
					}
				}
				
				
				$check = version_compare($this->version, $result);
				if ($check < 0) { //need update
					$this->settings['need_update'] = $result;
				}
				Configuration::updateValue('SLIDERSEVERYWHERE_SETS', serialize($this->settings));
			}
		}
	}


	private function _postProcess()
	{
		$errors = array();
		/* Display errors if needed */
		if (count($errors))
			$this->_html .= $this->displayError(implode('<br />', $errors));
	}

	private function _prepareHook($hook, $forcedCounter = false)
	{
		$slides = $this->getSlides(true, $hook);
		
		if (!$slides)
			return false;
		
		foreach ($slides as $k=>$slide){
			if ($slide['has_area'])
				$slides[$k]['areas'] = json_decode($slide['areas']);
			if (!file_exists(dirname(__FILE__).'/images/resize_'.$slide['image'])) {
				$slides[$k]['image'] = $slide['image'];
			} else {
				$slides[$k]['image'] = 'resize_'.$slide['image'];
			}
		}
			
		$this->counter++;
					
		$config = $this->config;
		
		$this->smarty->assign('configuration', $config[$hook]);
		$this->smarty->assign('homeslider_slides', $slides);
		$this->smarty->assign('slideName', $hook);
		$this->smarty->assign('hookid', $hook.($forcedCounter ? $forcedCounter : $this->counter));
		return $this->display(__FILE__, 'homesliderpro.tpl');

	}
	
	public function hookDisplayTop($params)
	{
		return $this->generalHook('displayTop');
	}
	public function hookDisplayBanner($params)
	{
		return $this->generalHook('displayBanner');
	}
	public function hookDisplayHome($params)
	{	
		return $this->generalHook('displayHome');
	}
	public function hookDisplayLeftColumn($params)
	{	
		return $this->generalHook('displayLeftColumn');
	}
	public function hookDisplayLeftColumnProduct($params)
	{	
		return $this->generalHook('displayLeftColumnProduct');
	}
	public function hookDisplayRightColumn($params)
	{	
		return $this->generalHook('displayRightColumn');
	}
	public function hookDisplayRightColumnProduct($params)
	{	
		return $this->generalHook('displayRightColumnProduct');
	}
	public function hookdisplayTopColumn($params) {
		return $this->generalHook('displayTopColumn');
	}
	public function hookDisplayHomeTabContent($params) {	
		return $this->generalHook('displayHomeTabContent');
	}
	public function hookDisplayProductTab($params) {	
		return $this->generalHook('displayProductTab');
	}
	public function hookDisplayShoppingCartFooter($params) {	
		return $this->generalHook('displayShoppingCartFooter');
	}
	
	public function generalHook($hookname) {
		$data = '';
		if (isset($this->standardHooks[$hookname]) && is_array($this->standardHooks[$hookname]))
			foreach ($this->standardHooks[$hookname] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
		
	public function hookDisplayFooter($params)
	{	
		$this->smartOverloARd();
		$data = '';
		if (isset($this->standardHooks['displayFooter']) && is_array($this->standardHooks['displayFooter']))
			foreach ($this->standardHooks['displayFooter'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	
	private function smartOverloARd() { //must be called in hookDisplayFooter to Work
		if (Tools::getValue('controller') == 'category') {
			$idCat = Tools::getValue('id_category');
			$scene = Scene::getScenes($idCat, $this->context->language->id, true, true);
			if ($scene) //if there is a scene we stop we don't display the slider and let the scene show
				return;
				
			$this->categorySlide = $this->getCategorySlide($idCat); //check if we have a slide for this category
			if ($this->categorySlide) { //we have a slide so let's remove the category image
				if ($this->hasActiveSlides($this->categorySlide)) { //check if there is any active slide
				
					$category = $this->context->smarty->getVariable('category');
					$category = $category->value;
					$category->id_image = 0;
				}
			}
		}
		if (Tools::getValue('controller') == 'cms' && Tools::getValue('id_cms') != '') {
			$cms = $this->context->smarty->getVariable('cms');
			//$this->context->smarty->clearAssign('cms');
			$cms = $cms->value;
			if (isset($cms->content) && !empty($cms->content)) {
				$cms->content = $this->doShortcode($cms->content);
				//$this->context->smarty->assign('cms', $cms);
			}
		}
	}
	
	private function doShortcode($string, $shortcode = 'SE'){
		preg_match_all('/\[(.*?):(.*?)\]/', $string, $matches);
		foreach ($matches[1] as $k=>$m){ // get only shortcodes for slidersEverywhere, you don't know if someone else start placing shortcodes 
			if ($m == $shortcode) {
				$pos = strpos($string,$matches[0][$k]);
				if ($pos !== false) {
					$string = substr_replace($string,$this->_prepareHook($matches[2][$k]),$pos,strlen($matches[0][$k]));
				}
			}
		}
		return $string;
	}
		
	public function hookDisplayFooterProduct()
	{		
		$data = '';
		if (isset($this->standardHooks['displayFooterProduct']) && is_array($this->standardHooks['displayFooterProduct']))
			foreach ($this->standardHooks['displayFooterProduct'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	
	public function hookDisplayHeader(){
		$this->context->controller->addCSS($this->_path.'css/font-awesome.css');
		$this->context->controller->addCSS($this->_path.'css/styles.css');
		$this->context->controller->addJS($this->_path.'js/slidereverywhere.js');
		
		if (!$this->isCached('header.tpl', $this->getCacheId())) {
			$config = $this->config;
			$this->smarty->assign('configuration', $config);
			$this->smarty->assign('rtlslide', $this->context->language->is_rtl);
			return $this->display(__FILE__, 'header.tpl');	
		}
		return true;
	}
	
	public function hookDisplaySlidersPro($params){
		if (isset($params['slider']) && !empty($params['slider']) ){
			$slide = $params['slider'];
			return $this->_prepareHook($slide);
			if( !$this->_prepareHook($slide) )
				return;
		} else if (isset($params['CMS']) && !empty($params['CMS']) ) {
			$context = Context::getContext();
			$slide = $this->getCmsSlide($context->controller->cms->id);
			if ($slide)
				return $this->_prepareHook($slide);
		} else if (isset($params['CAT']) && !empty($params['CAT']) ) {
			if ($this->categorySlide)
				return $this->_prepareHook($this->categorySlide);
		}
		return;
	}
	
	public function hookDisplayBackOfficeHeader($params = NULL){
		
		$headHtml ='';
		//$this->context->controller->addJs($this->_path.'js/config.js');
		if (Tools::getValue('configure') == $this->name | Tools::getValue('controller') == 'AdminModules') {
			$headHtml .='<link type="text/css" rel="stylesheet" href="'.$this->_path.'css/font-awesome.css"/>';
			$headHtml .='<link type="text/css" rel="stylesheet" href="'.$this->_path.'css/config.css"/>';
			$headHtml .='<script type="text/javascript" src="'.$this->_path.'js/config.js"></script>';		
		}
		return $headHtml;
	}
	
	public function clearCache()
	{
		$this->_clearCache('homesliderpro.tpl');
		$this->_clearCache('header.tpl');
	}
	
	public function getCmsSlide($cmsId) {
		$sql = 'SELECT proslider FROM '._DB_PREFIX_.'cms WHERE id_cms= '.$cmsId;
		if ($hook = Db::getInstance()->getValue($sql))
			return $hook;
		return false;
	}
	
	public function getCategorySlide($categoryId) {
		if (empty($categoryId))
			return false;
		$sql = 'SELECT proslider FROM '._DB_PREFIX_.'category WHERE id_category= '.$categoryId;
		if ($hook = Db::getInstance()->getValue($sql))
			return $hook;
		return false;
	}
	
	public function getCategoryIdBySlide($hook) {
		$sql = 'SELECT id_category FROM '._DB_PREFIX_.'category WHERE proslider= "'.$hook.'"';
		if ($categoryId = Db::getInstance()->getValue($sql))
			return $categoryId;
		return false;
	}
	
	public function saveCatHook($hook, $idCat) {
		if ($oldCatId = $this->getCategoryIdBySlide($hook)){ // the slider was already assigned to a category, remove it
			Db::getInstance()->update('category', array('proslider' => NULL), 'id_category = '.$oldCatId);
		}
		if (Db::getInstance()->update('category', array('proslider' => $hook), 'id_category = '.(int)$idCat))
			return true;
		
	}
	
	public function removeCatHook($hook) {
		if ($idCat = $this->getCategoryIdBySlide($hook))
			Db::getInstance()->update('category', array('proslider' => NULL), 'id_category = '.$idCat);
	}

	public function hookActionShopDataDuplication($params)
	{
		Db::getInstance()->execute('
			INSERT IGNORE INTO '._DB_PREFIX_.'homesliderpro (id_homeslider_slides, id_shop)
			SELECT id_homeslider_slides, '.(int)$params['new_id_shop'].'
			FROM '._DB_PREFIX_.'homesliderpro
			WHERE id_shop = '.(int)$params['old_id_shop']);
		$this->clearCache();
	}

	public function headerHTML()
	{
		if (Tools::getValue('controller') != 'AdminModules' && Tools::getValue('configure') != $this->name)
			return;
		
		$html ='<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
		<script type="text/javascript">
			var ajaxUrl = "'.$this->_path.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'";
		</script>';
		
		return $html;
	}
	
	public function getBaseUrl(){
		//this function ignore saved prestashop parameters for safe ajax requests where WWW or non WWW matters for same domain policy
		$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'];
		$url .= $_SERVER['REQUEST_URI'];
		return dirname(dirname($url));
	}

	public function getSlides($active = null, $hook = null)
	{
		$this->context = Context::getContext();
		$id_shop = $this->context->shop->id;
		$id_lang = $this->context->language->id;
		
		if ($hook == null) {
			$hook = 0;
		}
		$sql = 'SELECT hs.`id_homeslider_slides` as id_slide,
					hs.`id_hook`,
					hssl.`image`,
					hss.`position`,
					hss.`active`,
					hss.`new_window`,
					hss.`has_area`,
					hssl.`title`,
					hssl.`url`,
					hssl.`legend`,
					hssl.`description`,
					hssl.`areas`
			FROM '._DB_PREFIX_.'homesliderpro hs
			LEFT JOIN '._DB_PREFIX_.'homesliderpro_slides hss ON (hs.id_homeslider_slides = hss.id_homeslider_slides)
			LEFT JOIN '._DB_PREFIX_.'homesliderpro_slides_lang hssl ON (hss.id_homeslider_slides = hssl.id_homeslider_slides)
			WHERE (hs.id_shop = '.(int)$id_shop.')
			AND (hs.id_hook = "'.$hook.'")
			AND hssl.id_lang = '.(int)$id_lang.
			($active ? ' AND hss.`active` = 1' : ' ').'
			ORDER BY hss.position';
			
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}
	
	public function hasActiveSlides($hook){
		$sql = 'SELECT hs.`id_homeslider_slides` 
			FROM '._DB_PREFIX_.'homesliderpro hs
			LEFT JOIN '._DB_PREFIX_.'homesliderpro_slides hss ON (hs.id_homeslider_slides = hss.id_homeslider_slides) 
			WHERE hs.id_hook = "'.$hook.'"
			AND hss.`active` = 1';
		if ($slides = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql))
			return true;
		return false;
	}
	
	private function upgradeDb(){
	
		if ($this->isPS6){
			$this->registerHook('displayTopColumn');
			$this->registerHook('displayHomeTabContent');
			$this->registerHook('displayProductTab');
			$this->registerHook('displayShoppingCartFooter');
		}
		
		$dir = dirname(__FILE__);
		if (file_exists($dir.'/error_log'))
			unlink($dir.'/error_log');
		if (file_exists($dir.'/homesliderpro.tpl'))
			unlink($dir.'/homesliderpro.tpl');
		if (file_exists($dir.'/cms.tpl'))
			unlink($dir.'/cms.tpl');
		if (file_exists($dir.'/css/styles.php'))
			unlink($dir.'/css/styles.php');
		if (file_exists($dir.'/css/error_log'))
			unlink($dir.'/css/error_log');
		if (is_dir($dir.'/imgs'))
			rmdir($dir.'/imgs');
	
		$res = 1;
	
		if ($this->tableExists(_DB_PREFIX_.'category') && !$this->columnExists(_DB_PREFIX_.'category','proslider')){
			$res &= (bool)Db::getInstance()->execute(
				'ALTER TABLE `'._DB_PREFIX_.'category`
				ADD proslider varchar(255) NULL'
			);
		}
		
		if ($this->tableExists(_DB_PREFIX_.'homesliderpro_slides') && !$this->columnExists(_DB_PREFIX_.'homesliderpro_slides','has_area')){
			$res &= (bool)Db::getInstance()->execute(
				'ALTER TABLE `'._DB_PREFIX_.'homesliderpro_slides`
				ADD `has_area` tinyint(1) unsigned NOT NULL DEFAULT \'0\' 
			');
		}
		
		if ($this->tableExists(_DB_PREFIX_.'homesliderpro_slides_lang') && !$this->columnExists(_DB_PREFIX_.'homesliderpro_slides_lang','areas')){
			$res &= (bool)Db::getInstance()->execute(
				'ALTER TABLE `'._DB_PREFIX_.'homesliderpro_slides_lang`
				ADD `areas` text NULL
			');
		}
		
		if(!$this->tableExists(_DB_PREFIX_.'sesliders_slideconf')) {
			$res2 = (bool)Db::getInstance()->execute('
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'sesliders_slideconf` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`id_shop` int(10) unsigned NOT NULL,
					`id_hook` varchar(255) NULL,
					`conf` text NULL,
					PRIMARY KEY (`id`, `id_hook`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
			');
			$oldConfig = Configuration::get('HOMESLIDERPRO_CONFIG', null, null, (int)$this->getShopId());
	
			if(!empty($oldConfig))
				$this->updateConfigs(unserialize($oldConfig));
			if ( $res2 && $this->move_config()) {
				if (Configuration::deleteByName('HOMESLIDERPRO_CONFIG'))
					return true;
			}
		}
				
		return $res;
	}
	
	private function move_config(){
		$config = Configuration::get('HOMESLIDERPRO_CONFIG');
		if (!empty($config)){
			$config = unserialize($config);
			foreach ($config as $hook => $conf) {
				if (!Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'sesliders_slideconf WHERE id_hook = "'.$hook.'"') ){
					$id_shop = $this->getShopId();
					if ( is_array($conf) ){
						Db::getInstance()->insert('sesliders_slideconf', array(
							'id_hook' => pSQL($hook),
							'id_shop' => (int)$id_shop,
							'conf' => serialize($conf),
							)
						);
					}
				}
			}
		}
		return true;
	}
	
	private function columnExists($tablename,$columname) {
		$sql = 'SELECT * 
			FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = "'._DB_NAME_.'"
				AND TABLE_NAME = "'.$tablename.'"
				AND COLUMN_NAME = "'.$columname.'"';
		if (Db::getInstance()->executeS($sql))
			return true;
		return false;
	}
	
	private function tableExists($tablename) {
		$table = Db::getInstance()->executeS('show tables like "'.$tablename.'"');
		if (!empty($table))
			return true;
		return false;
	}
		
	public function getCreds(){
		$html = '<div class="credits">
		<p style="text-align:center;"><img src="../modules/'.$this->name.'/beer.png"/>'.$this->l('If you like this module and wanna see it improved why not to buy the developer a beer? it\'s just 5€!').'<img src="../modules/'.$this->name.'/beer.png"/></p>
		
		<form style="display:block;text-align:center;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick"/>
		<input type="hidden" name="hosted_button_id" value="WKKKH27C9RU3E"/>
		<input type="image" src="//imageshack.com/a/img691/3066/o4t.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
		<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1"/>
		</form>
		<p style="text-align:center;"><a href="http://www.prestashop.com/forums/index.php?showtopic=310597" target="_blank">'.$this->l('Need support? Click here!').'</a></p></div>';
		return $html;
	}
	
	public function updateConfigs($old_config = array()){
		$updated = false;
		foreach ($old_config as $hook => $config){
			foreach ($this->defaultConf as $k=>$default){
				if (!array_key_exists($k,$config)){
					$old_config[$hook][$k] = $default;
					$updated = true;
				}
			}
			//moved configuration inside media queries
			if (array_key_exists('center',$config)){
				if ($config['center'] == 1)
					$old_config[$hook]['media']['max']['pos'] = 2;
			}
			if (array_key_exists('side',$config)){
				if ($config['side'] == 1)
					$old_config[$hook]['media']['max']['pos'] = 1;
			}
			if (array_key_exists('vspace',$config)){
				$old_config[$hook]['media']['max']['tspace'] = $config['vspace'];
				$old_config[$hook]['media']['max']['bspace'] = $config['vspace'];
			}
			if (array_key_exists('hspace',$config)){
				$old_config[$hook]['media']['max']['lspace'] = $config['hspace'];
				$old_config[$hook]['media']['max']['rspace'] = $config['hspace'];
			}
		}
		if ($updated)
			$this->saveConfiguration($old_config);
	}

}