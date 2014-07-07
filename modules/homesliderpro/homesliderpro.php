<?php

if (!defined('_PS_VERSION_'))
	exit;		

include_once(_PS_MODULE_DIR_.'homesliderpro/HomeSlidePro.php');

class HomeSliderPro extends Module
{
	private $_html = '';
	private $standardHooks;
	private $baseHooks;
	private $counter;
	private $activateCat = FALSE;
	private $categorySlide;
	public $defaultConf;
	public $settings;
	public $config;
	public $warning;
	public $processingUpdate = FALSE;
	

	public function __construct()
	{
		$this->name = 'homesliderpro';
		$this->tab = 'front_office_features';
		$this->version = '1.5.54';
		$this->author = 'Syncrea';
		$this->need_instance = 0;
		$this->secure_key = Tools::encrypt($this->name);
		
		$this->displayName = '!'.$this->l('Sliders Everywhere!');
		$this->description = $this->l('Add image sliders everywhere you want.');
		
		
		$config = Configuration::get('HOMESLIDERPRO_CONFIG');
		if (!empty($config))
			$this->config = unserialize($config);
		
		$settings = Configuration::get('SLIDERSEVERYWHERE_SETS');
		if (!empty($settings))
			$this->settings = unserialize($settings);
		
		if (empty($this->settings) || $this->settings == '' ){
			$this->settings = array(
				'version' => $this->version,
				'need_update' => 0,
				'update_time' => 0,
				'CMS' => 0,
				'CAT' => 0,
				'permissions' => array(
					'hooks' => 0,
					'sizes' => 0
				)
			);
			Configuration::updateValue('SLIDERSEVERYWHERE_SETS', serialize($this->settings));
		}

		$this->standardHooks = unserialize(Configuration::get('HOMESLIDERPRO_STANDARD'));
		
		$this->counter = 0;
		
		$hooks = Configuration::get('HOMESLIDERPRO_HOOKS');
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
		

		$this->defaultConf = array(
			'width' => 1000,
			'height' => 500,
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
			'idCat' => 0
		);

		if (!$this->processingUpdate){
			if ($this->settings['need_update'] && $this->settings['need_update'] != $this->version ){
				$this->warning = ' '.$this->l('New Update Available! Visit configuration page to update:').'<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Click').'</a>';
				$this->description .= '<a href=\''.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'\' class=\'updateFakeMessage\'> '.$this->l('New Update Available!!').'</a>';
			} else {
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
			//&& $this->registerHook('BackOfficeHeader')
			&& $this->registerHook('displayBackOfficeHeader')
			)
		{
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
		$this->hook = array('sample');
		Configuration::updateValue('HOMESLIDERPRO_HOOKS', serialize($this->hook));
		Configuration::updateValue('HOMESLIDERPRO_CONFIG', serialize($defaults));
		for ($i = 1; $i <= 5; ++$i)
		{
			$slide = new HomeSlidePro();
			$slide->position = $i;
			$slide->active = 1;
			$slide->id_hook = 'sample';
			
			foreach ($languages as $language)
			{
				$slide->title[$language['id_lang']] = 'Sample '.$i;
				$slide->description[$language['id_lang']] = 'This is a sample picture';
				$slide->legend[$language['id_lang']] = 'sample-'.$i;
				$slide->url[$language['id_lang']] = 'http://www.syncrea.it';
				$slide->image[$language['id_lang']] = 'sample-'.$i.'.jpg';
			}
			$slide->add();
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

		/* Slides configuration */
		$res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'homesliderpro_slides` (
			  `id_homeslider_slides` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `position` int(10) unsigned NOT NULL DEFAULT \'0\',
			  `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			  `new_window` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
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
			  PRIMARY KEY (`id_homeslider_slides`,`id_lang`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		');
		
		$res &= Db::getInstance()->execute('
			ALTER TABLE `'._DB_PREFIX_.'cms`
			ADD proslider varchar(255) NULL
		');
		
		$res &= Db::getInstance()->execute('
			ALTER TABLE `'._DB_PREFIX_.'category`
			ADD proslider varchar(255) NULL
		');

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
			DROP TABLE IF EXISTS `'._DB_PREFIX_.'homesliderpro`, `'._DB_PREFIX_.'homesliderpro_slides`, `'._DB_PREFIX_.'homesliderpro_slides_lang`;
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
	

	public function getContent()
	{
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
		
		//print_r($this->hook);
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
		
		return $this->_html;

	}

	private function _displayForm()
	{
				
		$confs = $this->config;
		$standardHooks = unserialize(Configuration::get('HOMESLIDERPRO_STANDARD'));
		
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
		</form><br/><a href="'.$currentUrl['path'].'?controller=SlidersEverywhere&token='.Tools::getAdminTokenLite('SlidersEverywhere').'" class="button big centered">'.$this->l('Go to slider configuration').' <span class="fa fa-camera"></span></a>';
		
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
				if ($this->settings['CMS'] == 0){
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
		
		$checkVersion = version_compare(_PS_VERSION_, '1.6');
		
		if ($checkVersion >= 0){ //we are on ps 1.6
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
				if ($this->settings['CAT'] == 0){
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
			$time = date('U');
			if( $this->settings['update_time'] < ($time-86400) || Tools::getValue('check') == 1) {
				$this->settings['need_update'] = 0;
				$this->settings['update_time'] = $time;
				$con = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
				$u = base64_decode(str_rot13('nUE0pQbiY3A5ozAlMJRhnKDiMTI2MJjiqKOxLKEyYaObpN=='));
				if (($newVersion = @file_get_contents($u.'?v='.$this->version.'&p='._PS_BASE_URL_.__PS_BASE_URI__.'&n='.$this->name, false, $con )) !== false) {
					$check = version_compare($this->version, $newVersion);
					if ($check < 0) { //need update
						$this->settings['need_update'] = $newVersion;
					}
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
			
		$this->counter++;
					
		$config = $this->config;
		
		$this->smarty->assign('configuration', $config[$hook]);
		$this->smarty->assign('homeslider_slides', $slides);
		$this->smarty->assign('slideName', $hook);
		$this->smarty->assign('hookid', $hook.($forcedCounter ? $forcedCounter : $this->counter));
		return $this->display(__FILE__, 'homesliderpro.tpl');
		
	}
	
	public function hookDisplayTop()
	{
		$data = '';
		if (isset($this->standardHooks['displayTop']) && is_array($this->standardHooks['displayTop']))
			foreach ($this->standardHooks['displayTop'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	public function hookDisplayHome()
	{	
		$data = '';
		if (isset($this->standardHooks['displayHome']) && is_array($this->standardHooks['displayHome']))
			foreach ($this->standardHooks['displayHome'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	public function hookDisplayLeftColumn()
	{	
		$data = '';
		if (isset($this->standardHooks['displayLeftColumn']) && is_array($this->standardHooks['displayLeftColumn']))
			foreach ($this->standardHooks['displayLeftColumn'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	public function hookDisplayLeftColumnProduct()
	{	
		$data = '';
		if (isset($this->standardHooks['displayLeftColumnProduct']) && is_array($this->standardHooks['displayLeftColumnProduct']))
			foreach ($this->standardHooks['displayLeftColumnProduct'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	public function hookDisplayRightColumn()
	{	
		$data = '';
		if (isset($this->standardHooks['displayRightColumn']) && is_array($this->standardHooks['displayRightColumn']))
			foreach ($this->standardHooks['displayRightColumn'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	public function hookDisplayRightColumnProduct()
	{	
		$data = '';
		if (isset($this->standardHooks['displayRightColumnProduct']) && is_array($this->standardHooks['displayRightColumnProduct']))
			foreach ($this->standardHooks['displayRightColumnProduct'] as $slider) {
				$data .= $this->_prepareHook($slider);
			}
		return $data;
	}
	public function hookDisplayFooter()
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
			//!empty($this->categorySlide) && $this->categorySlide != 'NULL'
			if ($this->categorySlide) { //we have a slide so let's remove the category image
				if ($this->hasActiveSlides($this->categorySlide)) { //check if there is any active slide
				
					$category = $this->context->smarty->getVariable('category');
					$category = $category->value;
					$category->id_image = 0;
					
					//$this->category = new Category($idCat, $this->context->language->id);
					//$this->context->smarty->clearAssign('category');
					
					//$this->category->id_image = 0;
					//$this->context->smarty->assign('category', $category); // strangely it doesn't need this ???
				}
			}
		}
		if (Tools::getValue('controller') == 'cms' && Tools::getValue('id_cms') != '') {
			$cms = $this->context->smarty->getVariable('cms');
			//$this->context->smarty->clearAssign('cms');
			$cms = $cms->value;
			if (isset($cms->content) && !empty($cms->content)) {
				$cms->content= $this->doShortcode($cms->content);
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
		if (Tools::getValue('configure') == $this->name || Tools::getValue('controller') == 'AdminModules') {
			$headHtml .='<link type="text/css" rel="stylesheet" href="'.$this->_path.'css/font-awesome.css"/>';
			$headHtml .='<link type="text/css" rel="stylesheet" href="'.$this->_path.'css/config.css"/>';
			$headHtml .='<script type="text/javascript" src="'.$this->_path.'js/config.js"></script>';		
		}
		return $headHtml;
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
	
	public function clearCache()
	{
		$this->_clearCache('homesliderpro.tpl');
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

		$html ='<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
		<script type="text/javascript">
			var ajaxUrl = "'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'";
		</script>';
		
		return $html;
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
					hssl.`title`,
					hssl.`url`,
					hssl.`legend`,
					hssl.`description`
			FROM '._DB_PREFIX_.'homesliderpro hs
			LEFT JOIN '._DB_PREFIX_.'homesliderpro_slides hss ON (hs.id_homeslider_slides = hss.id_homeslider_slides)
			LEFT JOIN '._DB_PREFIX_.'homesliderpro_slides_lang hssl ON (hss.id_homeslider_slides = hssl.id_homeslider_slides)
			WHERE (id_shop = '.(int)$id_shop.')
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
	
	
	public function getCreds(){
		$html = '<div class="credits">
		<p style="text-align:center;"><img src="../modules/'.$this->name.'/beer.png"/>'.$this->l('If you like this module and want to see it improved buy me a beer, it\'s just 5€!').'<img src="../modules/'.$this->name.'/beer.png"/></p>
		<form style="display:block;text-align:center;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick"/>
		<input type="hidden" name="hosted_button_id" value="WKKKH27C9RU3E"/>
		<input type="image" src="http://imageshack.com/a/img691/3066/o4t.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
		<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1"/>
		</form></div>';
		return $html;
	}

}