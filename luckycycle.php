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
				&& $this->registerHook('displayNav')
				&& $this->registerHook('displayHeader')
				&& $this->registerHook('actionCartSave')
				&& $this->registerHook('displayOrderConfirmation')
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
			Configuration::updateValue('LUCKYCYCLE_API_KEY', Tools::getValue('luckycycle_api_key'));
			Configuration::updateValue('LUCKYCYCLE_OPERATION_HASH', Tools::getValue('luckycycle_operation_hash'));
			Configuration::updateValue('LUCKYCYCLE_ACTIVE', Tools::getValue('luckycycle_active'));
			$this->_clearCache('luckycycle.tpl');
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
		$this->context->controller->addCSS($this->_path.'views/css/elusive-icons/elusive-webfont.css');
		// JS
		$this->context->controller->addJS($this->_path.'views/js/js_file_name.js');	
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
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		error_log("LuckyForm displayed");
		
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
			'luckycycle_api_key' => Tools::getValue('luckycycle_api_key', Configuration::get('LUCKYCYCLE_API_KEY')),
			'luckycycle_operation_hash' => Tools::getValue('luckycycle_operation_hash', Configuration::get('LUCKYCYCLE_OPERATION_HASH')),
			'luckycycle_active' => Tools::getValue('luckycycle_active', Configuration::get('LUCKYCYCLE_ACTIVE')),
		);
	}

	public function hookActionCartSave($params)
	{
		error_log("LuckyForm hookActionCartSave");
		error_log( print_R($params['cart']->id,TRUE) );
		$api_key = '5ddb2b0631c47caaf17868d89c01261e80159fd7';
		$op = '478aa07fa86b29703f73c78afe17f650';
		$req = new LuckyCycleApi('http://localhost:3000');
		$req->setApiKey($api_key);
		$req->setOperationId($op);
		$pokedata = array(
		    'operation_id' => $op,
		    'user_uid' => time(),
		    //'item_uid' => $params['cart']->id,
		    'item_value' => 108,
		    'item_currency' => 'EUR',
		    // 'email' => $email,
		    // 'firstname' => $firstname,
		    // 'lastname' => $lastname,


		);
		if($random_data) {
			$pokedata['random_data'] = 1;
		}

		$poke = $req->poke($pokedata);
	}

}

?>
