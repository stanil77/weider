<?php
if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'homesliderpro/homesliderpro.php');
include_once(_PS_MODULE_DIR_.'homesliderpro/classes/ImageResizer.php');

class SlidersEverywhereController extends AdminController {
	private $_html = '';
	private $baseHooks;
	public $context;
	public $langs;
	public $module;
	private $settings;
	private $temp_html = '';

	public function __construct()
	{
		$this->html = '';
		$this->langs = Language::getLanguages(true, true);
		$this->context = Context::getContext();
		$this->display = 'view';
		$this->meta_title = $this->l('Sliders Everywhere');
		$this->toolbar_title = $this->l('Sliders Everywhere');
		$this->name = 'SlidersEverywhere';
		$this->module = new HomeSliderPro;

		$this->displayName = $this->module->displayName;
		$this->secure_key = $this->module->secure_key;
		
		$hooks = Configuration::get('HOMESLIDERPRO_HOOKS');
		
		$this->settings = unserialize(Configuration::get('SLIDERSEVERYWHERE_SETS'));
		
		$this->standardHooks = unserialize(Configuration::get('HOMESLIDERPRO_STANDARD'));
		
		$this->counter=0;

		if (!empty($hooks))
			$this->hook = unserialize($hooks);
		else
			$this->hook = array();
		
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

		$this->defaultConf = $this->module->defaultConf;

		parent::__construct();
	}

	public function initContent()
	{
		//$this->_postProcess();
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/css/font-awesome.css');
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/css/imgareaselect-animated.css');	
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/css/config.css');
		
		$this->context->controller->addJS(__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js');
		$this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/js/jquery.imgareaselect.pack.js');
		$this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/js/config.js');
		
		$this->show_toolbar = true;
		$this->display = 'view';
		$this->meta_title = $this->l('Sliders Everywhere');
		$this->confirmations = array();
		parent::initContent();	
	}
	
	public function initToolBarTitle()
	{
		$this->toolbar_title = $this->l('Sliders E');
	}
	
	public function initToolBar()
	{
		return false;
	}
	
	private function getCategoryTree($id_category = 1, $id_lang = false, $id_shop = false, $recursive = true)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
		$category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);

		if (is_null($category->id))
			return;

		if ($recursive)
		{
			$children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
			$spacer = 12 * (int)$category->level_depth;
		}

		$shop = (object) Shop::getShop((int)$category->getShopID());
		$this->temp_html .= '<li style="padding-left:'.(isset($spacer) ? ($spacer+10).'px' : '10px').';" data-cat="'.(int)$category->id.'" >'.$category->name.' <span>('.$shop->name.')</span><i class="fa fa-circle-o"></i></li>';

		if (isset($children) && count($children))
			foreach ($children as $child)
				$this->getCategoryTree((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
	}
	
	public function renderView() {
			
		$this->_html .='<div id="SESlides">';
		$this->_html .= $this->headerHTML();
		$this->getCategoryTree();
		$this->_html .= '<ul class="catTree">'. $this->temp_html .'<li class="closeme">'.$this->l('Close and remove Category').'<span class="fa fa-times"></span></li></ul>';
		
		$this->_html .= '<div id="overlayer"></div>';
		
		$headStart = '<div class="toolbarBox toolbarHead">
			<ul class="cc_button">';
		$headSaveConfig	= '';
		$headSaveSlide = '<li>
					<a style="display: block;" id="single-save" class="toolbar_btn" href="#" title="'.$this->l('Save').'">
						<span class="fa fa-check-circle savebig "></span>
						<div>'.$this->l('Save').'</div>
					</a>
				</li>';
		$headBack = '
				<li>
					<a id="desc-product-back" class="toolbar_btn" href="'.AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite($this->name).'" title="'.$this->l('Back').'">
						<span class="fa fa-reply backbutton"></span>
						<div>'.$this->l('Back').'</div>
					</a>
				</li>							
		';
		$headFoot = '</ul>
			<div class="pageTitle">
				<h3><img src="'.__PS_BASE_URI__.'modules/'.$this->module->name.'/logo.png" alt="Logo" title="Put your sliders Everywhere!"/>'.$this->displayName.'<span class="small"><span class="small"><span class="small">
				(v:'.$this->module->version.')
				</span></span></span></h3>
			</div>
		</div>';

		/* Validate & process */
		if (
			Tools::isSubmit('delete_id_slide') ||
			Tools::isSubmit('submitSlider') ||
			Tools::isSubmit('changeStatus') ||
			Tools::isSubmit('addHook') ||
			Tools::isSubmit('deleteHook') ||
			Tools::isSubmit('updateConfiguration') ||
			Tools::isSubmit('saveHooks')
			)
		{		
			if ($this->_postValidation()) {
				$this->_html .= $headStart.$headSaveConfig.$headFoot;
				$this->_postProcess();
			} else {
				$this->_html .= $headStart.$headSaveConfig.$headFoot;
			}
			$this->_displayForm();
		}
		elseif (Tools::isSubmit('addSlide') || ( Tools::isSubmit('id_slide') && $this->slideExists((int)Tools::getValue('id_slide')))) {
			if ($this->_postValidation()) {
				if (Tools::isSubmit('submitSlide')){
					$this->_html .= $headStart.$headSaveConfig.$headFoot;
					$this->_postProcess();
					$this->_displayForm();
				} else {
					$this->_html .= $headStart.$headSaveSlide.$headBack.$headFoot;
					$this->_displayAddForm();
				}					
			} else {
				$this->_html .= $headStart.$headSaveSlide.$headBack.$headFoot;
				$this->_displayAddForm();
			}
		}
		else {
			$this->_html .= $headStart.$headSaveConfig.$headFoot;
			$this->_displayForm();
		}
		$this->_html .='</div>';
		
		return $this->_html;
	}
	
	public function headerHTML()
	{
		if (Tools::getValue('controller') != $this->name)
			return;
		if ($this->module->settings['need_update']) {
			$this->_html .= '<div class="module_confirmation conf confirm">
			'.$this->l('NEW VERSION Available for ').$this->module->displayName.' (v:'.$this->module->settings['need_update'].')
			</div>';
			$this->_html .= '<form action="#" id="moduleUpdate" method="post"><input type="submit" class="button centered big" id="moduleUpdate" name="moduleUpdate" value="'.$this->l('Update Now!').'"/></form>';
		}
		
		$html = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>';
		$html .= '<script type="text/javascript">
			var ajaxUrl = "'._PS_BASE_URL_.__PS_BASE_URI__.'modules/'.$this->module->name.'/ajax_'.$this->module->name.'.php?secure_key='.$this->secure_key.'";
		</script>';

		return $html;
	}
	
	private function _displayForm()
	{
				
		$confs = unserialize(Configuration::get('HOMESLIDERPRO_CONFIG'));
		$standardHooks = unserialize(Configuration::get('HOMESLIDERPRO_STANDARD'));

		$enabled = false;
		if ( $this->context->employee->id_profile == _PS_ADMIN_PROFILE_ || $this->settings['permissions']['hooks'] == 0)
			$enabled = true;
		
		/** Genearl settings */
		if ($enabled) {
		$this->_html .= '
		<form id="sliders_setup" action="'.AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite($this->name).'" method="post">
			<fieldset>
				<legend><img src="'.__PS_BASE_URI__.'modules/'.$this->module->name.'/logo.png" alt="logo" />'.$this->l('Slides Setup').' </legend>
				<p>'.$this->l('Choose where you want your sliders to appear, add new sliders or delete it.').'</p>
				<table class="table confighooks" >
					<tr >
						<th>'.$this->l('Slider Name').'</th>
						<th>'.$this->l('Top').'</th>
						<th>'.$this->l('Home').'</th>
						<th>'.$this->l('Left Sidebar').'</th>
						<th>'.$this->l('Left Product Sidebar').'</th>
						<th>'.$this->l('Right Sidebar').'</th>
						<th>'.$this->l('Right Product Sidebar').'</th>
						<th>'.$this->l('Footer').'</th>
						<th>'.$this->l('Product Footer').'</th>
						<th>'.$this->l('Category').'</th>
						<th>'.$this->l('Custom Hook').'</th>
						<th>'.$this->l('Shortcode').'</th>
						<th><b>'.$this->l('Delete').'</b></th>
					</tr>';
		if (is_array($this->hook) && !empty($this->hook)){
			$i=0;
			foreach ($this->hook as $hookid=>$hookname) {
				$this->_html .= '<tr class="'.($i%2 == 0? 'odd':'even').'">
					<td>'.$hookname.'</td>';
				foreach ($this->baseHooks as $shook){ // standard prestashop hooks
					if (isset($standardHooks[$shook]) && is_array($standardHooks[$shook]) && in_array($hookname, $standardHooks[$shook])) {
						$checked="checked='checked'";
						$class = 'active';
					} else {
						$checked = '';
						$class = '';
					}
					$this->_html .= '<td class="'.$class.'">
						<input '.$checked.' type="checkbox" name="standardHooks['.$shook.'][]" value="'.$hookname.'" />
						</td>';
				}
				// category hook
				if (!$chosenCat = $this->module->getCategoryIdBySlide($hookname))
					$chosenCat = '';
				$this->_html .= '<td>'.$this->l('Category ID').': <input size="2" class="catnumber" type="number" value="'.$chosenCat.'" name="cat['.$hookname.']"/></td>';

				
				$this->_html .= '<td><span class="hookCode">{hook h="displaySlidersPro" slider="'.$hookname.'"}</span></td>';
				$this->_html .= '<td><span class="hookCode">[SE:'.$hookname.']</span></td>';
				$this->_html .= '<td class="delete"><input type="checkbox" name="hooksetup['.$hookid.']" value="'.$hookname.'"/></td>';				
				$this->_html .= '</tr>';
				$i++;
			}
		}
		
		
		$this->_html .= '</table><br/>
			<div class="margin-form">
				<input class="button" type="submit" name="saveHooks" value="'.$this->l('Save Hook Configuration').'"/>
				<input class="button deleteSlide" type="submit" name="deleteHook" value="'.$this->l('Delete Selected Slides').'"/>
			</div>';
		
		$this->_html .= '<hr/><label><span class="fa fa-plus-circle"></span> '.$this->l('New Slider name').'</label>
			<div class="margin-form"><input type="text" name="newSlide" value=""/> ('.$this->l('Only lowercase letters and underscores, no special characters, no numbers or blank spaces.').')</div>
			<div class="margin-form"><input class="button" type="submit" name="addHook" value="'.$this->l('Add New Slider').'"/></div>';
			
		$this->_html .= '</fieldset></form>';
		} // end if enabled permissions

		/** End Genearl settings */
		
		
		
		/** slide CHOOSER **/
		
		$slideArray = array();// cache slides to avoide double query
		$this->_html .= '<div class="slideChooserCont">';
		if (is_array($this->hook) && !empty($this->hook)){
			foreach ($this->hook as $hookId => $hookname) {
				$slideArray[$hookname] = $this->module->getSlides(null, $hookname);
				$empty = (!$slideArray[$hookname] ? true : false);
				$count = count($slideArray[$hookname]);
				$this->_html .= '<a class="slideChoose '.($hookId == 0 ? 'active':'').'" href="#'.$hookname.'slideConf"><span class="anim">'.$hookname.' '.($empty ? '<span title="'.$this->l('You have not yet added any slides.').'" class="fa fa-exclamation"></span>' : '<span class="number">'.$count.'</span>').'</span></a>';
			}
		}
		$this->_html .= '</div>';
		
	//	$this->_html .= '<pre>'.print_r($slideArray,true).'</pre>';
		
		/** slides configuration **/
		$this->_html .= '<form id="sliders_config" class="fixsize" action="'.AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite($this->name).'" method="post">';
		if (is_array($this->hook) && !empty($this->hook)){		
			foreach ($this->hook as $hookId => $hookname) {
				$this->_html .= '
				<fieldset class="position '.$hookname.' '.($hookId == 0 ? 'open':'').'" id="'.$hookname.'slideConf">
					<legend><img src="'.__PS_BASE_URI__.'modules/'.$this->module->name.'/logo.png" alt="logo" /> '.$this->l('Slider').': "'.$hookname.'"</legend>
					<div class="position"></div>
					<fieldset class="slideOptions"><legend>'.$this->l('Edit slider options').'</legend>
					';
										
					if ($this->context->employee->id_profile == _PS_ADMIN_PROFILE_ || $this->settings['permissions']['sizes'] == 0) {
						$this->_html .= '
							<div class="margin-form">
								<label>'.$this->l('Width').': </label>
								<input size="3" maxlength="4" class="config" name="conf['.$hookname.'][width]" type="text" value="'.$confs[$hookname]['width'].'" /> px
							</div>
							
							<div class="margin-form">
								<label>'.$this->l('Height').': </label>
								<input size="3" maxlength="4" class="config" name="conf['.$hookname.'][height]" type="text" value="'.$confs[$hookname]['height'].'"/> px
							</div>';
						$this->_html .= '
						<div class="margin-form">
							<label>'.$this->l('Speed').': </label>
							<input size="3" maxlength="4" class="config" name="conf['.$hookname.'][speed]" type="text" value="'.$confs[$hookname]['speed'].'"/> ms
						</div>
						<div class="margin-form">
							<label>'.$this->l('Pause').': </label>
							<input size="3" maxlength="4" class="config" name="conf['.$hookname.'][pause]" type="text" value="'.$confs[$hookname]['pause'].'"/> ms
						</div>';
					} else {
						$this->_html .= '
						<div class="margin-form">
							<label>'.$this->l('Width').': </label> '.$confs[$hookname]['width'].' px
							<input type="hidden" name="conf['.$hookname.'][width]" value="'.$confs[$hookname]['width'].'">
						</div>';
						$this->_html .= '
						<div class="margin-form">
							<label>'.$this->l('Height').': </label> '.$confs[$hookname]['height'].' px
							<input type="hidden" name="conf['.$hookname.'][height]" value="'.$confs[$hookname]['height'].'">
						</div>';
						$this->_html .= '
						<div class="margin-form">
							<label>'.$this->l('Speed').': </label> '.$confs[$hookname]['speed'].' ms
							<input type="hidden" size="3" maxlength="4" class="config" name="conf['.$hookname.'][speed]" value="'.$confs[$hookname]['speed'].'"/> 
						</div>
						<div class="margin-form">
							<label>'.$this->l('Pause').': </label> '.$confs[$hookname]['pause'].' ms
							<input size="3" maxlength="4" class="config" name="conf['.$hookname.'][pause]" type="hidden" value="'.$confs[$hookname]['pause'].'"/> 
						</div>';
					}
						
					

					
					$this->_html .= '<div class="margin-form">
						<label>'.$this->l('Mode').': </label>
						<select name="conf['.$hookname.'][mode]">
							<option value="horizontal" '.(($confs[$hookname]['mode'] == 'horizontal') ? 'selected="selected"' : '' ).'>'.$this->l('Horizontal').'  &nbsp;</option>
							<option value="vertical" '.(($confs[$hookname]['mode'] == 'vertical') ? 'selected="selected"' : '' ).'>'.$this->l('Vertical').'  &nbsp;</option>
							<option value="fade" '.(($confs[$hookname]['mode'] == 'fade') ? 'selected="selected"' : '' ).'>'.$this->l('Fade').'  &nbsp;</option>
							<option value="3Dflip" '.(($confs[$hookname]['mode'] == '3Dflip') ? 'selected="selected"' : '' ).'>'.$this->l('3D Flip').'  &nbsp;</option>
						</select>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Direction').': </label>
						<select name="conf['.$hookname.'][direction]">
							<option value="next" '.(($confs[$hookname]['direction'] == 'next') ? 'selected="selected"' : '' ).'>'.$this->l('Forward').'  &nbsp;</option>
							<option value="prev" '.(($confs[$hookname]['direction'] == 'prev') ? 'selected="selected"' : '' ).'>'.$this->l('Backward').'  &nbsp;</option>
						</select>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Auto Start').': </label>
						<label class="t" for="enableauto_'.$hookname.'"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input id="enableauto_'.$hookname.'" name="conf['.$hookname.'][auto]" type="radio" value="1" '. (($confs[$hookname]['auto'] == 1) ? 'checked="checked"' : '' ).'/> '.$this->l('Yes').'</label>
						<label class="t" for="disableauto_'.$hookname.'"><img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input id="disableauto_'.$hookname.'" name="conf['.$hookname.'][auto]" type="radio" value="0" '. (($confs[$hookname]['auto'] == 1) ? '' : 'checked="checked"' ).'/> '.$this->l('No').'</label>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Auto Restart').': </label>
						<label class="t" for="enableRestart_'.$hookname.'"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input id="enableRestart_'.$hookname.'" name="conf['.$hookname.'][restartAuto]" type="radio" value="1" '. (($confs[$hookname]['restartAuto'] == 1) ? 'checked="checked"' : '' ).'/> '.$this->l('Yes').'</label>
						<label class="t" for="disableRestart_'.$hookname.'"><img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input id="disableRestart_'.$hookname.'" name="conf['.$hookname.'][restartAuto]" type="radio" value="0" '. (($confs[$hookname]['restartAuto'] == 1) ? '' : 'checked="checked"' ).'/> '.$this->l('No').'</label>
						<div class="helper"><div class="help">'.$this->l('After clicking on controls the slider stops, if this is enabled the slider will start again').'</div></div>
					</div>
					
					<div class="margin-form clearfix">
						<label>'.$this->l('Show Play / Stop').': </label>
						<label class="t" ><img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input id="showplay'.$hookname.'" name="conf['.$hookname.'][autoControls]" type="radio" value="1" '. (($confs[$hookname]['autoControls'] == 1) ? 'checked="checked"' : '' ).'/> '.$this->l('Yes').'</label>
						<label class="t" ><img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input id="hidePlay_'.$hookname.'" name="conf['.$hookname.'][autoControls]" type="radio" value="0" '. (($confs[$hookname]['autoControls'] == 1) ? '' : 'checked="checked"' ).'/> '.$this->l('No').'</label>
						<div class="helper"><div class="help">'.$this->l('This will show play and stop icons to allow the user to control the automatic slideshow').'</div></div>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Loop').': </label>
						<label class="t" for="enableloop_'.$hookname.'"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input id="enableloop_'.$hookname.'" name="conf['.$hookname.'][loop]" type="radio" value="1" '. (($confs[$hookname]['loop'] == 1) ? 'checked="checked"' : '' ).'/> '.$this->l('Yes').'</label>
						<label class="t" for="disableloop_'.$hookname.'"><img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input id="disableloop_'.$hookname.'" name="conf['.$hookname.'][loop]" type="radio" value="0" '. (($confs[$hookname]['loop'] == 1) ? '' : 'checked="checked"' ).'/> '.$this->l('No').'</label>
						<div class="helper"><div class="help">'.$this->l('This option make the slider infinite').'</div></div>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Show Title').': </label>
						<label class="t" for="enabletitle_'.$hookname.'"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input id="enabletitle_'.$hookname.'" name="conf['.$hookname.'][show_title]" type="radio" value="1" '. (($confs[$hookname]['show_title'] == 1) ? 'checked="checked"' : '' ).'/> '.$this->l('Show').'</label>
						<label class="t" for="disabletitle_'.$hookname.'"><img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input id="disabletitle_'.$hookname.'" name="conf['.$hookname.'][show_title]" type="radio" value="0" '. (($confs[$hookname]['show_title'] == 1) ? '' : 'checked="checked"' ).'/> '.$this->l('Hide').'</label>
						<div class="helper"><div class="help">'.$this->l('Hide the title for every slide').'</div></div>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Title Position').': </label>
						<select name="conf['.$hookname.'][title_pos]">
							<option value="1" '.(($confs[$hookname]['title_pos'] == 1) ? 'selected="selected"' : '' ).'>'.$this->l('Right').' &nbsp;</option>
							<option value="2" '.(($confs[$hookname]['title_pos'] == 2) ? 'selected="selected"' : '' ).'>'.$this->l('Left').' &nbsp;</option>
						</select>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Show Controls').': </label>
						<label class="t" for="enablecontrols_'.$hookname.'"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input id="enablecontrols_'.$hookname.'" name="conf['.$hookname.'][controls]" type="radio" value="1" '. (($confs[$hookname]['controls'] == 1) ? 'checked="checked"' : '' ).'/> '.$this->l('Yes').'</label>
						<label class="t" for="disablecontrols_'.$hookname.'"><img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input id="disablecontrols_'.$hookname.'" name="conf['.$hookname.'][controls]" type="radio" value="0" '. (($confs[$hookname]['controls'] == 1) ? '' : 'checked="checked"' ).'/> '.$this->l('No').'</label>
						<div class="helper"><div class="help">'.$this->l('Show or hide navigation arrows').'</div></div>
					</div>
					
					<div class="margin-form">
						<label>'.$this->l('Show Pager').': </label>
						<label class="t" for="enablepager_'.$hookname.'"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
						<input id="enablepager_'.$hookname.'" name="conf['.$hookname.'][pager]" type="radio" value="1" '. (($confs[$hookname]['pager'] == 1) ? 'checked="checked"' : '' ).'/> '.$this->l('Yes').'</label>
						<label class="t" for="disablepager_'.$hookname.'"><img src="../img/admin/disabled.gif" alt="No" title="No" />
						<input id="disablepager_'.$hookname.'" name="conf['.$hookname.'][pager]" type="radio" value="0" '. (($confs[$hookname]['pager'] == 1) ? '' : 'checked="checked"' ).'/> '.$this->l('No').'</label>
						<div class="helper"><div class="help">'.$this->l('Show or hide pager on bottom').'</div></div>
					</div>
					
						<input class="button centered" type="submit" value="'.$this->l('Save').'" name="updateConfiguration" />
					
					</fieldset><br/>
				<strong>
					<a href="'.AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite($this->name).'&addSlide&hook='.$hookname.'">
						<i class="fa fa-plus-circle addslide"></i> '.$this->l('Add Slide').'
					</a>
				</strong>';
				
				/* Gets Slides from stored array*/
				$slides = $slideArray[$hookname];
				
				/* Display notice if there are no slides yet */
				if (!$slides)
					$this->_html .= '<p style="margin-left: 40px;">'.$this->l('You have not yet added any slides.').'</p>';
				else /* Display slides */
				{
					$this->_html .= '
					<div id="slidesContent_'.$hookname.'" class="slideList">
						<ul class="slides">';
					$pos = 0;

					foreach ($slides as $slide)
					{
						$pos++;
						$this->_html .= '
							<li id="slides_'.$slide['id_slide'].'"><i class="fa fa-bars list"></i>
								<img class="thumb" src="'.__PS_BASE_URI__.'modules/'.$this->module->name.($slide['image'] != '' ? '/images/thumb_'.$slide['image'] : '/css/img/nolanguage.png').'" width="50" height="40" />
								<strong>'.$pos.' : </strong> '.$slide['title'].'
								<p class="icons" >'.
									$this->displayStatus($slide['id_slide'], $slide['active'], $hookname).'
									<a href="'.AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite($this->name).'&id_slide='.(int)($slide['id_slide']).'&hook='.$hookname.'" title="'.$this->l('Edit').'"><i class="fa fa-pencil"></i></a>
									<a href="'.AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite($this->name).'&delete_id_slide='.(int)($slide['id_slide']).'#'.$hookname.'slideConf" title="'.$this->l('Delete').'"><i class="fa fa-trash-o"></i></a>
								</p>
							</li>';
					}
					$this->_html .= '</ul></div>';
				}
				// End fieldset
				$this->_html .= '</fieldset>';
			}
		}
		$this->_html .= '</form>';
		
		$this->_html .= "<script type='text/javascript'>

			$('#hints').click(function(){
				$('#hints').parent().find('.margin-form').slideToggle();
			});
			$('#slide-save').click(function(){
				$('#sliders_config').submit();
			})
		
			$('#sliders_config').submit(function(e){
				//e.preventDefault();
				var valid = true;
				$('input.config').each(function(){
					if ($(this).val() =='' || isNaN($(this).val()) ) {
						valid = false;
					}
				})
				
				if (valid == true) {
					return true;
				} else {
					alert('".$this->l('Insert a valid number')."');
					return false;
				}
				
			})
		</script>";
		$this->_html .= $this->module->getCreds();
	}

	
	public function displayStatus($id_slide, $active, $hookname)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$img = ((int)$active == 0 ? 'fa-times' : 'fa-check');
		$fakeParam =  ((int)$active == 0 ? 'enable=1' : 'enable=0'); //used to force window reload wen the same slide is activated and than deactivated
		$html = '<a class="changeStatus" data-slide-id="'.(int)$id_slide.'" href="'.AdminController::$currentIndex.
				'&token='.Tools::getAdminTokenLite($this->name).'&changeStatus=1&id_slide='.(int)$id_slide.'&'.$fakeParam.'#'.$hookname.'slideConf" title="'.$title.'"><i class="fa '.$img.'"></i></a>';
		return $html;
	}
	private function _postValidation()
	{
		$errors = array();

		if (Tools::isSubmit('changeStatus'))
		{
			if (!Validate::isInt(Tools::getValue('id_slide')))
				$errors[] = $this->l('Invalid slide');
		}
		
		/* Validation for Slide */
		elseif (Tools::isSubmit('submitSlide'))
		{
			/* Checks state (active) */
			if (!Validate::isInt(Tools::getValue('active_slide')) || (Tools::getValue('active_slide') != 0 && Tools::getValue('active_slide') != 1))
				$errors[] = $this->l('Invalid slide state');
			/* Checks position */
			if (!Validate::isInt(Tools::getValue('position')) || (Tools::getValue('position') < 0))
				$errors[] = $this->l('Invalid slide position');
			/* If edit : checks id_slide */
			if (Tools::isSubmit('id_slide'))
			{
				if (!Validate::isInt(Tools::getValue('id_slide')) && !$this->slideExists(Tools::getValue('id_slide')))
					$errors[] = $this->l('Invalid id_slide');
			}
			/* Checks title/url/legend/description/image */
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				if (Tools::strlen(Tools::getValue('title_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The title is too long.');
				if (Tools::strlen(Tools::getValue('legend_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The legend is too long.');
				if (Tools::strlen(Tools::getValue('url_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The URL is too long.');
				if (Tools::strlen(Tools::getValue('description_'.$language['id_lang'])) > 4000)
					$errors[] = $this->l('The description is too long.');
				if (Tools::strlen(Tools::getValue('url_'.$language['id_lang'])) > 0 && !Validate::isUrl(Tools::getValue('url_'.$language['id_lang'])))
					$errors[] = $this->l('The URL format is not correct.');
				if (Tools::getValue('image_'.$language['id_lang']) != null && !Validate::isFileName(Tools::getValue('image_'.$language['id_lang'])))
					$errors[] = $this->l('Invalid filename');
				if (Tools::getValue('image_old_'.$language['id_lang']) != null && !Validate::isFileName(Tools::getValue('image_old_'.$language['id_lang'])))
					$errors[] = $this->l('Invalid filename');
			}

			/* Checks title/url/legend/description for default lang */
			$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
			
			if (!Tools::isSubmit('has_picture') && (!isset($_FILES['image_'.$id_lang_default]) || empty($_FILES['image_'.$id_lang_default]['tmp_name'])))
				$errors[] = $this->l('The image is not set.');
			if (Tools::getValue('image_old_'.$id_lang_default) && !Validate::isFileName(Tools::getValue('image_old_'.$id_lang_default)))
				$errors[] = $this->l('The image is not set.');
		} /* Validation for deletion */
		elseif (Tools::isSubmit('delete_id_slide') && (!Validate::isInt(Tools::getValue('delete_id_slide')) || !$this->slideExists((int)Tools::getValue('delete_id_slide'))))
			$errors[] = $this->l('Invalid id_slide');

		/* Display errors if needed */
		if (count($errors))
		{
			$this->_html .= $this->displayError(implode('<br />', $errors));
			return false;
		}

		/* Returns if validation is ok */
		return true;
	}
	
	private function _postProcess()
	{
		$errors = array();
		/* Processes Slider */
		if (Tools::isSubmit('changeStatus') && Tools::isSubmit('id_slide'))
		{
			$slide = new HomeSlidePro((int)Tools::getValue('id_slide'));
			if ($slide->active == 0)
				$slide->active = 1;
			else
				$slide->active = 0;
			$res = $slide->update();
			$this->_html .= ($res ? $this->displayConfirmation($this->l('Configuration updated')) : $this->displayError($this->l('The configuration could not be updated.')));
		}
		/* Processes Slide */
		elseif (Tools::isSubmit('submitSlide'))
		{
			//get slide configuration
			
			$position = Tools::getValue('hook'); 
			$confs = unserialize(Configuration::get('HOMESLIDERPRO_CONFIG'));	
			$configuration = $confs[$position];
			
		
			/* Sets ID if needed */
			if (Tools::getValue('id_slide'))
			{
				$slide = new HomeSlidePro((int)Tools::getValue('id_slide'));
				if (!Validate::isLoadedObject($slide))
				{
					$this->_html .= $this->displayError($this->l('Invalid id_slide'));
					return;
				}
			}
			else
				$slide = new HomeSlidePro();
			/* Sets position */
			$slide->position = (int)Tools::getValue('position');
			/* Sets active */
			$slide->active = (int)Tools::getValue('active_slide');
			/* Sets new_window */
			$slide->new_window = (int)Tools::getValue('new_window');
			/* set hook */
			$slide->id_hook = Tools::getValue('hook');
			
			$languages = Language::getLanguages(false);
			
			$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
			$usedefault = false;
			if ((int)Tools::getValue('crosslanguage') == 1) {
				$usedefault = true;
				if ( isset($_FILES['image_'.$id_lang_default]) ) {
					$fileDefaultName = $_FILES['image_'.$id_lang_default]['name'];
					$typeDefault = strtolower(substr(strrchr($fileDefaultName, '.'), 1));
				}
			}

			/* Sets each langue fields */
			
			$salt = sha1(microtime());
			
			foreach ($languages as $language)
			{
				$slide->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
				$slide->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);
				$slide->legend[$language['id_lang']] = Tools::getValue('legend_'.$language['id_lang']);
				$slide->description[$language['id_lang']] = addslashes(Tools::getValue('description_'.$language['id_lang']));
								
				$langID = $language['id_lang'];
				if ($usedefault) {
					$langID = $id_lang_default;
				}
				$type = strtolower(substr(strrchr($_FILES['image_'.$langID]['name'], '.'), 1));
				$cleanFileName = str_replace('.'.$type, '', $_FILES['image_'.$langID]['name']);

				/* Uploads image and sets slide */
				if ($langID == $language['id_lang']) {
					
					
					$imagesize = array();
					$imagesize = @getimagesize($_FILES['image_'.$langID]['tmp_name']);
					if (isset($_FILES['image_'.$langID]) &&
						isset($_FILES['image_'.$langID]['tmp_name']) &&
						!empty($_FILES['image_'.$langID]['tmp_name']) &&
						!empty($imagesize) &&
						in_array(strtolower(substr(strrchr($imagesize['mime'], '/'), 1)), array('jpg', 'gif', 'jpeg', 'png')) &&
						in_array($type, array('jpg', 'gif', 'jpeg', 'png')))
					{
						$fileName = $this->file_newname(_PS_MODULE_DIR_.$this->module->name.'/images/', $cleanFileName, $type, $langID);
						$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
						if ($error = ImageResizer::validateUpload($_FILES['image_'.$langID])){
							$errors[] = $error;
						} elseif (!$temp_name || !move_uploaded_file($_FILES['image_'.$langID]['tmp_name'], $temp_name)){
							return false;
						} elseif ( !ImageResizer::resize($temp_name, _PS_MODULE_DIR_.$this->module->name.'/images/'.$fileName.'-'.$langID.'.'.$type, $configuration['width'], $configuration['height'], 'png', true)){
							//resize($src_file, $dst_file, $dst_width = null, $dst_height = null, $file_type = 'jpg', $force_type = false)
							$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
						} else {
							//creaThumb
							ImageResizer::resize($temp_name,  _PS_MODULE_DIR_.$this->module->name.'/images/thumb_'.$fileName.'-'.$langID.'.'.$type, 50, 40, 'png', true);
						}
						if (isset($temp_name))
							@unlink($temp_name);
						$slide->image[$language['id_lang']] = $fileName.'-'.$langID.'.'.$type;
					} 
				} 
				elseif ($usedefault) {
					$fileName = $this->file_newname(_PS_MODULE_DIR_.$this->module->name.'/images/', $cleanFileName, $type, $langID);
					$slide->image[$language['id_lang']] = $fileName.'-'.$langID.'.'.$typeDefault;
				}
				elseif (Tools::getValue('image_old_'.$language['id_lang']) != '') {
					$slide->image[$language['id_lang']] = Tools::getValue('image_old_'.$language['id_lang']);
				}
			}

			/* Processes if no errors  */
			if (!$errors)
			{
				/* Adds */
				if (!Tools::getValue('id_slide'))
				{
					if (!$slide->add())
						$errors[] = $this->l('The slide could not be added.');
				}
				/* Update */
				elseif (!$slide->update())
					$errors[] = $this->l('The slide could not be updated.');
			}
		} /* Deletes */
		elseif (Tools::isSubmit('delete_id_slide'))
		{
			$slide = new HomeSlidePro((int)Tools::getValue('delete_id_slide'));
			$res = $slide->delete();
			if (!$res)
				$this->_html .= $this->displayError('Could not delete');
			else
				$this->_html .= $this->displayConfirmation($this->l('Slide deleted'));
		} 
		/** add HOOK Slide **/
		else if (Tools::isSubmit('addHook')) {
			$slideName = Tools::getValue('newSlide');
			$slideName = strtolower(str_replace(' ', '_', $slideName));
			$slideName = preg_replace('/[^a-za-z_\']/', '', $slideName);
			if (!empty($slideName) && $slideName != '') { //check if something is entered in hook name
				if (!in_array($slideName,$this->hook)){ //check if hook name is already used
					$this->hook[] = $slideName;
					Configuration::updateValue('HOMESLIDERPRO_HOOKS', serialize($this->hook));
					$config = unserialize(Configuration::get('HOMESLIDERPRO_CONFIG'));
					$config[$slideName] = $this->defaultConf;
					Configuration::updateValue('HOMESLIDERPRO_CONFIG', serialize($config));
				} else
					$errors[] = $this->l('Slider name already used.');
			} else {
				$errors[] = $this->l('Slider name cannot be empty.');
			}
			
		}
		/** remove HOOK SLIDE **/
		else if (Tools::isSubmit('deleteHook')) {
			$choiches = Tools::getValue('hooksetup');
			if (!empty($choiches) && is_array($choiches)) {
				foreach ($choiches as $key=>$hook){
					$this->module->removeCatHook($hook);
					$slides = $this->module->getSlides(null, $hook);
					if ($slides){
						foreach ($slides as $slide){
							$slide = new HomeSlidePro((int)$slide['id_slide']);
							$slide->delete();
						}
					}
					unset($this->hook[$key]);
				}
				Configuration::updateValue('HOMESLIDERPRO_HOOKS', serialize($this->hook));
			}
			
		} else if (Tools::isSubmit('updateConfiguration')) {
			$configs = Tools::getValue('conf');
			$newconfigs = serialize($configs);		
			if (Configuration::updateValue('HOMESLIDERPRO_CONFIG', $newconfigs)){
				$this->_html .= $this->displayConfirmation($this->l('Configuration updated'));
			}

		} else if (Tools::isSubmit('saveHooks')){
			$error = false;
			$standardHooks = Tools::getValue('standardHooks');
			Configuration::updateValue('HOMESLIDERPRO_STANDARD', serialize($standardHooks));
			$catHooks = Tools::getValue('cat');
			if ($this->checkduplicates($catHooks)){
				foreach ($catHooks as $hook=>$idCat){
					if (Validate::isInt($idCat)){
						if (!$this->module->saveCatHook($hook, $idCat)){
							$error = true;
						}
					} else if (empty($idCat) || $idCat == ''){
						$this->module->removeCatHook($hook);
					} else {
						$error = true;
					}
				}
			} else {
				$error = true;
				$this->_html .= $this->displayError($this->l('Cannot set the same Category id on multiple hooks!'));
			}
			
			if (!$error)
				$this->_html .= $this->displayConfirmation($this->l('Positions updated'));
		} 

		/* Display errors if needed */
		if (count($errors))
			$this->_html .= $this->displayError(implode('<br />', $errors));
		elseif (Tools::isSubmit('submitSlide') && Tools::getValue('id_slide'))
			$this->_html .= $this->displayConfirmation($this->l('Slide updated'));
		elseif (Tools::isSubmit('submitSlide'))
			$this->_html .= $this->displayConfirmation($this->l('Slide added'));
		
	}
	
	/** if there is already an image with the same name rename it **/
	public function file_newname($path, $filename, $ext, $idlang = ''){
		
		$appendString = '-'.$idlang.'.'.$ext;
		$newpath = $path.'/'.$filename.$appendString;
		$newname = $filename;
		$counter = 0;
		$splitResult = array();
		while (file_exists($newpath)) {
			   $newname = $newname .'c'. $counter;
			   $newpath = $path.'/'.$newname.$appendString;
			   $counter++;
		}

		return $newname;
	}
	
	public function checkduplicates($array = array()){
		if (!empty($array) && is_array($array)) {
			$temp = array();
			foreach ($array as $key=>$value) {
				if (!in_array($value, $temp)){
					if ($value != '')
						$temp[$key] = $value;
				} else
					return false;
			}
			return true;
		}
	}
	
	public function slideExists($id_slide)
	{
		$req = 'SELECT hs.`id_homeslider_slides` as id_slide
				FROM `'._DB_PREFIX_.'homesliderpro` hs
				WHERE hs.`id_homeslider_slides` = '.(int)$id_slide;
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($row);
	}
	
	private function _displayAddForm()
	{
		$confs = unserialize(Configuration::get('HOMESLIDERPRO_CONFIG'));
		/* Sets Slide : depends if edited or added */
		$slide = null;
		if (Tools::isSubmit('id_slide') && $this->slideExists((int)Tools::getValue('id_slide')))
			$slide = new HomeSlidePro((int)Tools::getValue('id_slide'));
		
		$hook = Tools::getValue('hook');
		
		
		/* Checks if directory is writable */
		if (!is_writable(_PS_MODULE_DIR_.$this->module->name))
			parent::DisplayWarning(sprintf($this->l('Modules %s must be writable (CHMOD 755 / 777)'), $this->name));

		/* Gets languages and sets which div requires translations */
		$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
		$languages = Language::getLanguages(false);
		//$divLangName = 'image¤title¤url¤legend¤description';
		$this->_html .= '<script type="text/javascript">id_language = Number('.$id_lang_default.');</script>';

		/* Form */
		$this->_html .= '<form id="single-slide" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" enctype="multipart/form-data">';

		/* Fieldset Upload */
		$this->_html .= '
		<fieldset class="width3">
			<br />
			<legend><span class="fa fa-plus-circle addslide"></span> 1 - '.$this->l('Upload your slide').'</legend>';
		
		/** carica la stessa immagine per tutte le lingue?? */
		$this->_html .= '<label>'.$this->l('Same image for all languages?').' : </label>
			<div class="margin-form">
			<input '.(Tools::getValue('id_slide') == '' ? 'checked="checked"' : '') .' type="checkbox" id="crosslanguage" name="crosslanguage" style="margin:5px 0 0;" value="1"/>
			<div class="helper"><div class="help">'.$this->l('If checked the next image you upload will be copied over for all languages!').'</div></div>
			<input type="hidden" id="langID" name="langID" value="'.$id_lang_default.'" />
			</div>';
		/* Image */
		$this->_html .= '<label>'.$this->l('Select a file:').' * </label><div id="imgchooser" class="margin-form"><div class="translatable">';
		foreach ($languages as $language)
		{
			$this->_html .= '<div class="lang_'.$language['id_lang'].'" id="image_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').';float: left;">';
			$this->_html .= '<input type="file" name="image_'.$language['id_lang'].'" id="image_'.$language['id_lang'].'" size="30" value="'.(isset($slide->image[$language['id_lang']]) ? $slide->image[$language['id_lang']] : '').'"/>';
			/* Sets image as hidden in case it does not change */
			if ($slide && isset($slide->image[$language['id_lang']]))
				$this->_html .= '<input type="hidden" name="image_old_'.$language['id_lang'].'" value="'.($slide->image[$language['id_lang']]).'" id="image_old_'.$language['id_lang'].'" />';
			/* Display image */
			if ( $slide && isset($slide->image[$language['id_lang']]) && !empty($slide->image[$language['id_lang']]) )
				$this->_html .= '<input type="hidden" name="has_picture" value="1" /><img class="preview" src="'.__PS_BASE_URI__.'modules/'.$this->module->name.'/images/'.$slide->image[$language['id_lang']].'" width="'.($confs[$hook]['width']/2).'" height="'.($confs[$hook]['height']/2).'" alt=""/>';
			else
				$this->_html .= '<input type="hidden" name="has_picture" value="0" />';
			$this->_html .= '</div>';
		}
		$this->_html .= '</div>';
		/* End Fieldset Upload */
		$this->_html .= '</fieldset><br /><br />';

		/* Fieldset edit/add */
		$this->_html .= '<fieldset class="width3">';
		if (Tools::isSubmit('addSlide')) /* Configure legend */
			$this->_html .= '<span class="fa fa-plus-circle addslide"></span> 2 - '.$this->l('Configure your slide').'</legend>';
		elseif (Tools::isSubmit('id_slide')) /* Edit legend */
			$this->_html .= '<legend><img src="'.__PS_BASE_URI__.'modules/'.$this->module->name.'/logo.png" alt="" /> 2 - '.$this->l('Edit your slide').'</legend>';
		/* Sets id slide as hidden */
		if ($slide && Tools::getValue('id_slide'))
			$this->_html .= '<input type="hidden" name="id_slide" value="'.$slide->id.'" id="id_slide" />';
		/* Sets position as hidden */
		$this->_html .= '<input type="hidden" name="position" value="'.(($slide != null) ? ($slide->position) : ($this->getNextPosition())).'" id="position" />';
		
		

		/* Form content */
		
		/***** Title ******/
		$this->_html .= '<br /><label>'.$this->l('Title:').'</label><div class="margin-form translatable">';
		foreach ($languages as $language)
		{
			$this->_html .= '
					<div class="lang_'.$language['id_lang'].'" id="title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').';float: left;">
						<input type="text" name="title_'.$language['id_lang'].'" id="title_'.$language['id_lang'].'" size="30" value="'.(isset($slide->title[$language['id_lang']]) ? $slide->title[$language['id_lang']] : '').'"/>
					</div>';
		}

		$this->_html .= '</div><br /><br />';

		/* URL */
		$this->_html .= '<label>'.$this->l('URL:').'</label><div class="margin-form translatable">';
		foreach ($languages as $language)
		{
			$this->_html .= '
					<div class="lang_'.$language['id_lang'].'" id="url_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').';float: left;">
						<input type="text" name="url_'.$language['id_lang'].'" id="url_'.$language['id_lang'].'" size="30" value="'.(isset($slide->url[$language['id_lang']]) ? $slide->url[$language['id_lang']] : '').'"/>
					</div>';
		}

		$this->_html .= '</div><br /><br/>';
		
		/* New Window */

		$this->_html .= '
		<label for="new_window_on">'.$this->l('Open url in New Window:').'</label>
		<div class="margin-form">
			<img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
			<input type="radio" name="new_window" id="new_window_on" '.( ( isset($slide->new_window) && (int)$slide->new_window == 1 ) ? ' checked="checked" ' : '').' value="1" />
			<label class="t" for="new_window_on">'.$this->l('Yes').'</label>
			<img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;" />
			<input type="radio" name="new_window" id="new_window_off" '. ( ( (isset($slide->new_window) && (int)$slide->new_window == 0) || !isset($slide->new_window) ) ? 'checked="checked" ' : '').' value="0" />
			<label class="t" for="new_window_off">'.$this->l('No').'</label>
		</div><br />';

		/***** Legend ********/
		$this->_html .= '<label>'.$this->l('Legend:').'</label><div class="margin-form translatable">';
		foreach ($languages as $language)
		{
			$this->_html .= '
					<div class="lang_'.$language['id_lang'].'" id="legend_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').';float: left;">
						<input type="text" name="legend_'.$language['id_lang'].'" id="legend_'.$language['id_lang'].'" size="30" value="'.(isset($slide->legend[$language['id_lang']]) ? $slide->legend[$language['id_lang']] : '').'"/>
					</div>';
		}

		$this->_html .= '</div><br/><br/>';
		
		

		/* Description */
		$this->_html .= '
		<label>'.$this->l('Description:').' </label>
		<div class="margin-form translatable">';
		foreach ($languages as $language)
		{
			$this->_html .= '<div class="lang_'.$language['id_lang'].'" id="description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').';float: left;">
				<textarea name="description_'.$language['id_lang'].'" rows="10" cols="29">'.(isset($slide->description[$language['id_lang']]) ? $slide->description[$language['id_lang']] : '').'</textarea>
			</div>';
		}

		$this->_html .= '</div><div class="clear"></div><br />';

		/* Active */
		$this->_html .= '
		<label for="active_on">'.$this->l('Active:').'</label>
		<div class="margin-form">
			<img src="../img/admin/enabled.gif" alt="Yes" title="Yes" />
			<input type="radio" name="active_slide" id="active_on" '.(((isset($slide->active) && (int)$slide->active == 1)) ? ' checked="checked" ' : '').' value="1" />
			<label class="t" for="active_on">'.$this->l('Yes').'</label>
			<img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;" />
			<input type="radio" name="active_slide" id="active_off" '.(((isset($slide->active) && (int)$slide->active == 0) || !isset($slide->new_window)) ? 'checked="checked" ' : '').' value="0" />
			<label class="t" for="active_off">'.$this->l('No').'</label>
		</div>';

		/* Save */
		$this->_html .= '
		<p class="center">
			<input type="submit" class="button" name="submitSlide" value="'.$this->l('Save').'" />
			<input type="hidden" name="id_hook" value="'.Tools::getValue('hook').'" />
			<a class="button" style="position:relative; padding:3px 3px 4px 3px; top:1px" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Cancel').'</a>
		</p>';

		/* End of fieldset & form */
		$this->_html .= '
			<p><sup>*</sup> '.$this->l('Required fields').'</p>
			</fieldset>
		</form>';
		$this->_html .= "<script type='text/javascript'>
			$('#single-save').click(function(){
				$('#single-slide input[name=submitSlide]').trigger('click');
			})
			var languages = new Array();";
			foreach ($languages as $k => $language){
			$this->_html .= 'languages['.$k.'] = {
					id_lang: "'.$language['id_lang'].'",
					iso_code: "'.$language['iso_code'].'",
					name: "'.$language['name'].'",
					is_default: "'.($language['id_lang'] == $id_lang_default ? '1' : '0').'"
				};';
			}
			$this->_html .= 'displayFlags(languages, '.$id_lang_default.', 0);
		</script>';
		
	}
	
	public function getNextPosition()
	{
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
				SELECT MAX(hss.`position`) AS `next_position`
				FROM `'._DB_PREFIX_.'homesliderpro_slides` hss, `'._DB_PREFIX_.'homesliderpro` hs
				WHERE hss.`id_homeslider_slides` = hs.`id_homeslider_slides` AND hs.`id_shop` = '.(int)$this->context->shop->id
		);

		return (++$row['next_position']);
	}
	
	public function displayConfirmation($string)
	{
		$this->confirmations = array();
	 	$output = '
		<div class="module_confirmation conf confirm">
			'.$string.'
		</div>';
		return $output;
	}
	
	public function displayError($error)
	{
	 	$output = '
		<div class="module_error alert error">
			'.$error.'
		</div>';
		//$this->error = true;
		return $output;
	}
}