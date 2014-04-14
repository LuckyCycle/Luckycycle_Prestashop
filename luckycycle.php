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
			&& Configuration::updateValue('LUCKYCYCLE_ACTIVE', false)
			&& Configuration::updateValue('LUCKYCYCLE_PRODUCTION', false)
			&& Configuration::updateValue('LUCKYCYCLE_CUSTOM_URL', 'http://localhost:3000')
			&& $this->registerHook('displayNav')
			&& $this->registerHook('displayHeader')
				//&& $this->registerHook('actionCartSave')
			&& $this->registerHook('displayOrderConfirmation')
			&& $this->registerHook('actionPaymentConfirmation')
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
						'desc' => $this->l('Pokes not sent if disabled.'),
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
						'desc' => $this->l('You can get this ID on <a target="_blank" href="http://LuckyCycle.com">LuckyCycle.com</a>.'),
						),
					array(
						'type' => 'text',
						'label' => $this->l('Operation Id'),
						'name' => 'luckycycle_operation_hash',
						'desc' => $this->l('You can get this ID on <a target="_blank" href="http://LuckyCycle.com">LuckyCycle.com</a>.'),
						),
					array(
						'type' => 'switch',
						'label' => $this->l('Production mode'),
						'name' => 'luckycycle_production',
						'desc' => $this->l('Set to Yes to use the production server.'),
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
						'desc' => $this->l('Use a custom url to make the calls.'),
						),

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
		);
}

public function hookActionCartSave($params)
{
	error_log("LuckyForm hookActionCartSave");
}


public function hookDisplayOrderConfirmation($params)
{
	error_log("Display form called");
	//error_log( print_R($params['objOrder'],TRUE) );

	$order_id = $params['objOrder']->id;
	$customer_id = $params['objOrder']->id_customer;

	error_log("order:".$order_id);
	error_log("custo:".$customer_id);

	$sql = "select count(*) as tot FROM " . _DB_PREFIX_ . "luckycycle_pokes where id_order = '" . $order_id . "' AND id_customer = '" . $customer_id . "'";
	//error_log( print_R($sql,TRUE) );
	if ($row = Db::getInstance()->getRow($sql))
		$tot = $row['tot'];

	error_log($tot);

	//if($this->game_ok) {
	if(true) {
		$this->context->smarty->assign('vars', array(
			'iframe' => 'Play the LuckyCycle Game',
			'some_smarty_array' => array(
				'some_smarty_var' => 'some_data',
				'some_smarty_var' => 'some_data'
				),
			'some_smarty_var2' => 'some_data2'
			));

		return $this->display(__FILE__, 'views/templates/hooks/orderConfirmation.tpl');	
	}
}


	// run on confirmation (later for cheque and wire)
public function hookActionPaymentConfirmation($params) 
{
	if (Configuration::get('LUCKYCYCLE_ACTIVE')) {
		// error_log( print_R($params['id_order'],TRUE) );
		error_log("LuckyForm hookActionPaymentConfirmation");
		$order = new Order((int)$params['id_order']);
		$currency = new Currency((int)$order->id_currency);
		$lang = new Language((int)$order->id_lang);
		$customer = new Customer((int)$order->id_customer);
		// error_log( print_R($currency->iso_code,TRUE) );
		// error_log( print_R($lang->iso_code,TRUE) );
		// error_log( print_R($order->total_paid,TRUE) );
		// error_log( print_R($order->total_paid_real,TRUE) );
		// error_log( print_R($order->total_products,TRUE) );
		// error_log( print_R($order->total_products_wt,TRUE) );
		// error_log( print_R($order->total_shipping,TRUE) );
		// error_log( print_R($order->id_customer,TRUE) );
		// error_log( print_R($customer,TRUE) );

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
			'item_value' => (string)$order->total_paid,
			'item_currency' => $currency->iso_code,
			'language' => $lang->iso_code,
			'firstname' => $customer->firstname,
			'lastname' => $customer->lastname,
			'email' => $customer->email,

		);

		try {
			$poke = $req->poke($pokedata);
		} catch (Exception $e) {
			error_log( print_R($e->getMessage(),TRUE) );
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
			error_log("we didn't get a poke back, user cannot play on this order or error");
		}
	}
}


}

?>
