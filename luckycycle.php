<?php 
/*
* minicskeleton - a module template for Prestashop v1.5+
* Copyright (C) 2013 S.C. Minic Studio S.R.L.
* 
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__).'/luckysdk/lucky.php';

if (!defined('_PS_VERSION_'))
	exit;

class LuckyCycle extends Module
{
	// DB file
	const INSTALL_SQL_FILE = 'install.sql';

	public $game_ok;

	public function __construct()
	{
		$this->name = 'luckycycle';

		$this->tab = 'advertising_marketing';
		$this->version = '0.1';
		$this->author = 'LuckyCycle';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6'); 
		// $this->dependencies = array('blockcart');
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('LuckyCycle');
		$this->description = $this->l('Buy & Win.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	/**
 	 * install
	 */
	public function install()
	{
		// Create DB tables - uncomment below to use the install.sql for database manipulation
		
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		else if (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		$sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
		// Insert default template data
		$sql = str_replace('THE_FIRST_DEFAULT', serialize(array('width' => 1, 'height' => 1)), $sql);
		$sql = str_replace('FLY_IN_DEFAULT', serialize(array('width' => 1, 'height' => 1)), $sql);
		$sql = preg_split("/;\s*[\r\n]+/", trim($sql));

		foreach ($sql as $query)
			if (!Db::getInstance()->execute(trim($query)))
				return false;


			return parent::install()
			&& Configuration::updateValue('LUCKYCYCLE_API_KEY', '')
			&& Configuration::updateValue('LUCKYCYCLE_OPERATION_HASH', '')
			&& Configuration::updateValue('LUCKYCYCLE_ACTIVE', false)
			&& Configuration::updateValue('LUCKYCYCLE_PRODUCTION', true)
			&& Configuration::updateValue('LUCKYCYCLE_CUSTOM_URL', 'http://localhost:3000')
			&& Configuration::updateValue('LUCKYCYCLE_IFRAME_WIDTH', '100%')
			&& Configuration::updateValue('LUCKYCYCLE_IFRAME_HEIGHT', '450')
			&& Configuration::updateValue('LUCKYCYCLE_IFRAME_CSS', 'border:1px solid lightgrey;')
			&& Configuration::updateValue('LUCKYCYCLE_MANUFACTURERS_IDS', '')
			&& Configuration::updateValue('LUCKYCYCLE_CATEGORIES_EXCLUDED', '')
			&& Configuration::updateValue('LUCKYCYCLE_CATEGORIES_ONLY', '')
			&& Configuration::updateValue('LUCKYCYCLE_INCLUDE_SHIPPING', true)
			&& Configuration::updateValue('LUCKYCYCLE_SHOW_BANNER', false)
			&& $this->registerHook('displayNav')
			&& $this->registerHook('displayHeader')
				//&& $this->registerHook('actionCartSave')
			&& $this->registerHook('displayOrderConfirmation')
			&& $this->registerHook('actionPaymentConfirmation')
			&& $this->registerHook('displayShoppingCart')
				//&& $this->registerHook('actionOrderStatusPostUpdate')
			;
		}

	/**
 	 * uninstall
	 */
	public function uninstall()
	{
		return Configuration::deleteByName('LUCKYCYCLE_API_KEY') 
		&& Configuration::deleteByName('LUCKYCYCLE_OPERATION_HASH') 
		&& Configuration::deleteByName('LUCKYCYCLE_ACTIVE') 
		&& Configuration::deleteByName('LUCKYCYCLE_PRODUCTION') 
		&& Configuration::deleteByName('LUCKYCYCLE_CUSTOM_URL') 
		&& Configuration::deleteByName('LUCKYCYCLE_IFRAME_WIDTH') 
		&& Configuration::deleteByName('LUCKYCYCLE_IFRAME_HEIGHT') 
		&& Configuration::deleteByName('LUCKYCYCLE_IFRAME_CSS') 
		&& Configuration::deleteByName('LUCKYCYCLE_MANUFACTURERS_IDS')
		&& Configuration::deleteByName('LUCKYCYCLE_CATEGORIES_EXCLUDED')
		&& Configuration::deleteByName('LUCKYCYCLE_CATEGORIES_ONLY')
		&& Configuration::deleteByName('LUCKYCYCLE_INCLUDE_SHIPPING') 
		&& Configuration::deleteByName('LUCKYCYCLE_SHOW_BANNER') 
		&& parent::uninstall();
	}

	/**
 	 * admin page
	 */	
	public function getContent()
	{
		$html = '';
		// If we try to update the settings
		if (Tools::isSubmit('submitModule'))
		{
			// TODO check for validity of apikey and op id through the api
			Configuration::updateValue('LUCKYCYCLE_API_KEY', Tools::getValue('luckycycle_api_key'));
			Configuration::updateValue('LUCKYCYCLE_OPERATION_HASH', Tools::getValue('luckycycle_operation_hash'));
			Configuration::updateValue('LUCKYCYCLE_ACTIVE', Tools::getValue('luckycycle_active'));
			Configuration::updateValue('LUCKYCYCLE_PRODUCTION', Tools::getValue('luckycycle_production'));
			Configuration::updateValue('LUCKYCYCLE_CUSTOM_URL', Tools::getValue('luckycycle_custom_url'));
			Configuration::updateValue('LUCKYCYCLE_IFRAME_WIDTH', Tools::getValue('luckycycle_iframe_width'));
			Configuration::updateValue('LUCKYCYCLE_IFRAME_HEIGHT', Tools::getValue('luckycycle_iframe_height'));
			Configuration::updateValue('LUCKYCYCLE_IFRAME_CSS', Tools::getValue('luckycycle_iframe_css'));
			Configuration::updateValue('LUCKYCYCLE_MANUFACTURERS_IDS', Tools::getValue('luckycycle_manufacturers_ids'));
			Configuration::updateValue('LUCKYCYCLE_CATEGORIES_EXCLUDED', Tools::getValue('luckycycle_categories_excluded'));
			Configuration::updateValue('LUCKYCYCLE_CATEGORIES_ONLY', Tools::getValue('luckycycle_categories_only'));
			Configuration::updateValue('LUCKYCYCLE_INCLUDE_SHIPPING', Tools::getValue('luckycycle_include_shipping'));
			Configuration::updateValue('LUCKYCYCLE_SHOW_BANNER', Tools::getValue('luckycycle_show_banner'));
			//$this->_clearCache('luckycycle.tpl');
			//$this->_clearCache('nav.tpl');
			$html .= $this->displayConfirmation($this->l('Configuration updated'));
		}

		$html .= $this->renderForm();

		return $html;
	}

	// BACK OFFICE HOOKS

	/**
 	 * admin <head> Hook
	 */
	public function hookDisplayBackOfficeHeader()
	{
		// CSS
		// $this->context->controller->addCSS($this->_path.'views/css/elusive-icons/elusive-webfont.css');
		// // JS
		// $this->context->controller->addJS($this->_path.'views/js/js_file_name.js');	
	}

	/**
	 * Hook for back office dashboard
	 */
	public function hookDisplayAdminHomeQuickLinks()
	{	
		$this->context->smarty->assign('minicskeleton', $this->name);
		return $this->display(__FILE__, 'views/templates/hooks/quick_links.tpl');    
	}

	// FRONT OFFICE HOOKS

	/**
 	 * <head> Hook
	 */
	public function hookDisplayShoppingCart()
	{
		error_log("Lucky hookShoppingCart");
		if (Configuration::get('LUCKYCYCLE_ACTIVE')) {
			return $this->display(__FILE__, 'views/templates/hooks/shoppingCart.tpl');
		}
		// CSS
		//$this->context->controller->addCSS($this->_path.'views/css/'.$this->name.'.css');
		// JS
		//$this->context->controller->addJS($this->_path.'views/js/'.$this->name.'.js');
	}


	/**
 	 * <head> Hook
	 */
	public function hookDisplayHeader()
	{
		// CSS
		$this->context->controller->addCSS($this->_path.'views/css/'.$this->name.'.css');
		// JS
		$this->context->controller->addJS($this->_path.'views/js/'.$this->name.'.js');
	}

	/**
 	 * Top of pages hook
	 */
	public function hookDisplayTop($params)
	{
		return $this->hookDisplayHome($params);
	}

	/**
 	 * Home page hook
	 */
	public function hookDisplayHome($params)
	{
		$this->context->smarty->assign('MinicSkeleton', array(
			'some_smarty_var' => 'some_data',
			'some_smarty_array' => array(
				'some_smarty_var' => 'some_data',
				'some_smarty_var' => 'some_data'
				),
			'some_smarty_var' => 'some_data'
			));

		return $this->display(__FILE__, 'views/templates/hooks/home.tpl');	
	}

	/**
 	 * Left Column Hook
	 */
	public function hookDisplayRightColumn($params)
	{
		return $this->hookDisplayHome($params);
	}

	/**
 	 * Right Column Hook
	 */
	public function hookDisplayLeftColumn($params)
	{
		return $this->hookDisplayHome($params);
	}

	/**
 	 * Footer hook
	 */
	public function hookDisplayFooter($params) 
	{
		return $this->hookDisplayHome($params);
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
					),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Activate LuckyCycle Basket'),
						'name' => 'luckycycle_active',
						'desc' => $this->l('Pokes not sent and frame not shown if disabled'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
								)
							),
						),
					array(
						'type' => 'text',
						'label' => $this->l('LuckyCycle Api Key'),
						'name' => 'luckycycle_api_key',
						'desc' => $this->l('You can get this ID on LuckyCycle.com.'),
						),
					array(
						'type' => 'text',
						'label' => $this->l('Operation Id'),
						'name' => 'luckycycle_operation_hash',
						'desc' => $this->l('You can get this ID on LuckyCycle.com.'),
						),
					
					array(
						'type' => 'switch',
						'label' => $this->l('Use banner image'),
						'name' => 'luckycycle_show_banner',
						'desc' => $this->l('Use the banner send in json in place of an iframe. Go to Luckycycle backend to setup'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
								)
							),
						),
					array(
						'type' => 'text',
						'label' => $this->l('iFrame width'),
						'name' => 'luckycycle_iframe_width',
						'desc' => $this->l('Specify iframe width in % or pixels'),
						'size'     => 100,
						),
					array(
						'type' => 'text',
						'label' => $this->l('iFrame height'),
						'name' => 'luckycycle_iframe_height',
						'desc' => $this->l('Specify iframe height in pixels'),
						),
					array(
						'type' => 'textarea',
						'label' => $this->l('iFrame tag css'),
						'name' => 'luckycycle_iframe_css',
						'rows' => 3,
						'desc' => $this->l('Specify iframe tag css'),
						),
					array(
						'type' => 'switch',
						'label' => $this->l('Include shipping costs'),
						'name' => 'luckycycle_include_shipping',
						'desc' => $this->l('Only available in full basket mode (no filtering on categories or manufacturers)'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
								)
							),
						),
					array(
						'type' => 'text',
						'label' => $this->l('Limit to some manufacturers (hybrid version)'),
						'name' => 'luckycycle_manufacturers_ids',
						'rows' => 3,
						'desc' => $this->l('Comma separated list of manufacturers ids (ex: 1,4,23,44)'),
						),
					// UNCOMMENT HERE TO BE ABLE TO CHANGE API SERVER
					array(
						'type' => 'switch',
						'label' => $this->l('Production mode'),
						'name' => 'luckycycle_production',
						'desc' => $this->l('Set to Yes to use the production server'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
								)
							),
						),
					array(
						'type' => 'text',
						'label' => $this->l('Custom Url'),
						'name' => 'luckycycle_custom_url',
						'desc' => $this->l('Use a custom url to make the calls'),
						),
					// array(
					// 	'type' => 'text',
					// 	'label' => $this->l('Exclude categories (not working)'),
					// 	'name' => 'luckycycle_categories_excluded',
					// 	'rows' => 3,
					// 	'desc' => $this->l('Comma separated list of categories ids'),
					// 	),
					// array(
					// 	'type' => 'text',
					// 	'label' => $this->l('Only include categories (not working)'),
					// 	'name' => 'luckycycle_categories_only',
					// 	'rows' => 3,
					// 	'desc' => $this->l('Comma separated list of categories ids'),
					// 	),


					),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

$helper = new HelperForm();
$helper->show_toolbar = false;
$helper->table =  $this->table;
$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
$helper->default_form_language = $lang->id;
$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
$this->fields_form = array();

$helper->identifier = $this->identifier;
$helper->submit_action = 'submitModule';
$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
$helper->token = Tools::getAdminTokenLite('AdminModules');
$helper->tpl_vars = array(
	'fields_value' => $this->getConfigFieldsValues(),
	'languages' => $this->context->controller->getLanguages(),
	'id_language' => $this->context->language->id
	);

return $helper->generateForm(array($fields_form));
}

public function getConfigFieldsValues()
{
	return array(
		'luckycycle_api_key' => 		Tools::getValue('luckycycle_api_key', Configuration::get('LUCKYCYCLE_API_KEY')),
		'luckycycle_operation_hash' => 	Tools::getValue('luckycycle_operation_hash', Configuration::get('LUCKYCYCLE_OPERATION_HASH')),
		'luckycycle_active' => 			Tools::getValue('luckycycle_active', Configuration::get('LUCKYCYCLE_ACTIVE')),
		'luckycycle_production' => 		Tools::getValue('luckycycle_production', Configuration::get('LUCKYCYCLE_PRODUCTION')),
		'luckycycle_custom_url' => 		Tools::getValue('luckycycle_custom_url', Configuration::get('LUCKYCYCLE_CUSTOM_URL')),
		'luckycycle_iframe_width' => 		Tools::getValue('luckycycle_iframe_width', Configuration::get('LUCKYCYCLE_IFRAME_WIDTH')),
		'luckycycle_iframe_height' => 		Tools::getValue('luckycycle_iframe_height', Configuration::get('LUCKYCYCLE_IFRAME_HEIGHT')),
		'luckycycle_iframe_css' => 		Tools::getValue('luckycycle_iframe_css', Configuration::get('LUCKYCYCLE_IFRAME_CSS')),
		'luckycycle_manufacturers_ids' => 		Tools::getValue('luckycycle_manufacturers_ids', Configuration::get('LUCKYCYCLE_MANUFACTURERS_IDS')),
		'luckycycle_categories_excluded' => 		Tools::getValue('luckycycle_categories_excluded', Configuration::get('LUCKYCYCLE_CATEGORIES_EXCLUDED')),
		'luckycycle_categories_only' => 		Tools::getValue('luckycycle_categories_only', Configuration::get('LUCKYCYCLE_CATEGORIES_ONLY')),
		'luckycycle_include_shipping' => 		Tools::getValue('luckycycle_include_shipping', Configuration::get('LUCKYCYCLE_INCLUDE_SHIPPING')),
		'luckycycle_show_banner' => 		Tools::getValue('luckycycle_show_banner', Configuration::get('LUCKYCYCLE_SHOW_BANNER')),
		);
}

public function hookActionCartSave($params)
{
	error_log("LuckyForm hookActionCartSave");
}


public function hookDisplayOrderConfirmation($params)
{
	error_log("Display form order");
	//error_log( print_R($params['objOrder'],TRUE) );

	$order_id = $params['objOrder']->id;
	$customer_id = $params['objOrder']->id_customer;

	error_log("PS order_id/customer_id:" . $order_id . " / " . $customer_id);

	$sql = "select hash,banner_url,count(*) as tot FROM " . _DB_PREFIX_ . "luckycycle_pokes where id_order = '" . $order_id . "' AND id_customer = '" . $customer_id . "'";
	error_log( print_R($sql,TRUE) );
	if ($row = Db::getInstance()->getRow($sql))
		$tot = $row['tot'];

	error_log($tot);
	error_log($row['hash']);

	if($tot>0) {
	//if(true) {
		$this->context->smarty->assign('vars', array(
			'iframe' => 'Play the LuckyCycle Game',
			'frame' => array(
				'width' => Configuration::get('LUCKYCYCLE_IFRAME_WIDTH'),
				'height' => Configuration::get('LUCKYCYCLE_IFRAME_HEIGHT'),
				'css' => Configuration::get('LUCKYCYCLE_IFRAME_CSS'),
				),
			'hash' => $row['hash'],
			'banner_url' => $row['banner_url'],
			));
		if (Configuration::get('LUCKYCYCLE_SHOW_BANNER')) {
			return $this->display(__FILE__, 'views/templates/hooks/orderConfirmationBanner.tpl');
		} else {
			return $this->display(__FILE__, 'views/templates/hooks/orderConfirmation.tpl');
		}
	}
}


	// run on confirmation (later for cheque and wire)
public function hookActionPaymentConfirmation($params) 
{
	if (Configuration::get('LUCKYCYCLE_ACTIVE')) {
		// error_log( print_R($params['id_order'],TRUE) );
		error_log("LuckyForm hookActionPaymentConfirmation -> trying to POKE");
		$order = new Order((int)$params['id_order']);
		$currency = new Currency((int)$order->id_currency);
		$lang = new Language((int)$order->id_lang);
		$customer = new Customer((int)$order->id_customer);
		// error_log( print_R($currency->iso_code,TRUE) );
		// error_log( print_R($lang->iso_code,TRUE) );
		error_log( print_R("Total paid : " .$order->total_paid,TRUE) );
		error_log( print_R("Total paid real?: " .$order->total_paid_real,TRUE) );
		error_log( "Total products : " . print_R($order->total_products,TRUE) );
		error_log( print_R("Total products wt : " .$order->total_products_wt,TRUE) );
		error_log( print_R("Total shipping : " .$order->total_shipping,TRUE) );
		// error_log( print_R("Customer id : " .$order->id_customer,TRUE) );

		// error_log(print_R($order->getCartProducts(),TRUE));

		$normal_mode_total = ( Configuration::get('INCLUDE_SHIPPING') ? $order->total_products_wt : $order->total_products );

		if (Configuration::get('LUCKYCYCLE_MANUFACTURERS_IDS') && strlen( Configuration::get('LUCKYCYCLE_MANUFACTURERS_IDS') )) {
			$total_lc = 0;
			error_log("we have a manufacturer list" . print_R(Configuration::get('LUCKYCYCLE_MANUFACTURERS_IDS'),true));
			$manufacturers = explode(',', Configuration::get('LUCKYCYCLE_MANUFACTURERS_IDS'));
			if (count($manufacturers)>0) {
				foreach ($order->getCartProducts() as $key => $value) {
					error_log("checking product " . $value['id_product'] . " with manufacturer id " . $value['id_manufacturer']);
					foreach ($manufacturers as $k => $m) {
						if ($value['id_manufacturer'] == $m) {
							error_log($value['id_product'] . " is in manufacturer list");
							$total_lc += $value['total_price_tax_incl'];
						}
					}
				}
			} else {
				$total_lc = $normal_mode_total;
			}
		} else {
			$total_lc = $normal_mode_total;
		}

// CART
		#$customer_id = $params['objOrder']->id_customer;
		#$customer = new Customer((int)($customer_id));

		global $cookie;
		#$lang = strtolower(Language::getIsoById(intval($cookie->id_lang)));

		#$the_order = $params['objOrder']->getProducts();
		$the_order = $order->getProducts();

		// echo "<pre>";
		// print_r($the_order);
		// echo "</pre>";

		$the_cart = [];

		foreach ($the_order as $key => $value) {
			$item['price'] = $value['unit_price_tax_incl'];
			$item['quantity'] = $value['product_quantity'];
			$item['product_id'] = $value['product_id'];
			$item['category_id'] = $value['id_category_default'];
			$item['manufacturer_id'] = $value['id_manufacturer'];
			$item['product_name'] = $value['product_name'];
			$item['reference'] = $value['reference'];
			// echo "<pre>";
			// var_dump($item);
			// echo "</pre>";	
			array_push($the_cart,$item);
		}
// -> CART
		error_log( "LC total : " . print_R($total_lc,TRUE) );

		// here we can recalculate the total for the products in cas of hybrid version

		if($total_lc > 0) {
			$api_key = Configuration::get('LUCKYCYCLE_API_KEY');
			$op = Configuration::get('LUCKYCYCLE_OPERATION_HASH');
			$url = Configuration::get('LUCKYCYCLE_PRODUCTION') ? 'https://www.luckycycle.com' : Configuration::get('LUCKYCYCLE_CUSTOM_URL');
			$req = new LuckyCycleApi($url);
			$req->setApiKey($api_key);
			$req->setOperationId($op);
			$pokedata = array(
				'operation_id' => $op,
				'user_uid' => (string)$order->id_customer,
				'item_uid' => (string)$params['id_order'],
				'item_value' => (string)$total_lc,
				'item_currency' => $currency->iso_code,
				'language' => $lang->iso_code,
				'firstname' => $customer->firstname,
				'lastname' => $customer->lastname,
				'email' => $customer->email,
				'cart' => $the_cart,

			);

			try {
				$poke = $req->poke($pokedata);
			} catch (Exception $e) {
				error_log( "Error poking : " . print_R($e->getMessage(),TRUE) );
				// TODO : logt his and handle this case...
			}

			// error_log( print_R($poke,TRUE) );
			// error_log( print_R($poke['can_play'],TRUE) );
			if ($poke && $poke['can_play']==1) {

				error_log("we got a poke back that can play");

				// check existenz
				$sql = "select count(*) as tot FROM " . _DB_PREFIX_ . "luckycycle_pokes where hash = '" . $poke['computed_hash'] . "'";
				//error_log( print_R($sql,TRUE) );
				if ($row = Db::getInstance()->getRow($sql))
					$tot = $row['tot'];

				if ($tot==0) 
				{
					Db::getInstance()->insert('luckycycle_pokes', array(
						'hash' => $poke['computed_hash'],
						'banner_url' => $poke['banner_url'],
						'id_order' => (string)$params['id_order'],
						'operation_id' => Configuration::get('LUCKYCYCLE_OPERATION_HASH'),
						'type' => 'basket',
						'id_customer' => (string)$order->id_customer,
						'created_at' => date('Y-m-d H:i:s'),
						'total_played' => $order->total_paid_real
						));
					error_log('Poke added in DB');
				} else {
					error_log('Poke already in DB');
				}

				
			} else {
				// user cannot play whatever reason... TODO handle this
				error_log("we didn't get a poke back, user cannot play on this order or an error happened");
			}
		}
	} else {
		error_log("LC module is not active");
	}
}


}

?>
