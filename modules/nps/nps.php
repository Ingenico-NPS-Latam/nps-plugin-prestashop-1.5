<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'/nps/lib/Sub1/psp_client.php');

class Nps extends PaymentModule
{
  const PAYMENT_ACTION_AUTHORIZE = 'authorize';
  const PAYMENT_ACTION_AUTOCAPTURE = 'autocapture';
  
  
	private $_postErrors = array();

	/**
	 * @brief Constructor
	 */
	public function __construct()
	{
		$this->name = 'nps';
		$this->tab = 'payments_gateways';
		$this->version = '0.03.006';
		$this->author = 'Sub1 SA';

    $this->bootstrap = true;
		parent::__construct();
    

		$this->displayName = $this->l('NPS - Net Payment Service');
		$this->description = $this->l('Module for accepting payments from local credit cards, local bank transfers and cash deposits.');

		$this->confirmUninstall =	$this->l('Are you sure you want to delete your details?');

		/* Backward compatibility */
		require(_PS_MODULE_DIR_.'nps/backward_compatibility/backward.php');
		$this->context->smarty->assign('base_dir', __PS_BASE_URI__);
	}

	/**
	 * @brief Install method
	 *
	 * @return Success or failure
	 */
	public function install()
	{
		if (
            !parent::install() || 
            
            !$this->registerHook('payment') ||
            
            !$this->registerHook('PaymentReturn') ||
            
            !$this->registerHook('displayBackOfficeHeader') ||
            
            !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nps_token` (
              `id_cart` int(10) NOT NULL,
              `token` varchar(32) DEFAULT NULL,
              `status` varchar(20) DEFAULT NULL,
              PRIMARY KEY  (`id_cart`)
          ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;')  ||
            

          !Db::getInstance()->Execute("CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."installment` (
            `id_installment` int(11) NOT NULL AUTO_INCREMENT,
            `id_payment_product` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `qty` int(11) NOT NULL,
            `rate` decimal(12,4) NOT NULL,
            `id_shop` int(11) DEFAULT NULL,
            PRIMARY KEY (`id_installment`)
          ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;") ||
            
            
          !Db::getInstance()->Execute("INSERT INTO `"._DB_PREFIX_."installment` (id_payment_product,qty,rate,id_shop) VALUES (14,1,0.00,".Context::getContext()->shop->id.")") ||
          !Db::getInstance()->Execute("INSERT INTO `"._DB_PREFIX_."installment` (id_payment_product,qty,rate,id_shop) VALUES (5,1,0.00,".Context::getContext()->shop->id.")") ||
          !Db::getInstance()->Execute("INSERT INTO `"._DB_PREFIX_."installment` (id_payment_product,qty,rate,id_shop) VALUES (1,1,0.00,".Context::getContext()->shop->id.")") ||
          !Db::getInstance()->Execute("INSERT INTO `"._DB_PREFIX_."installment` (id_payment_product,qty,rate,id_shop) VALUES (2,1,0.00,".Context::getContext()->shop->id.")") ||                          
            
            !self::createMenu()
            
            ) // prod | test
			return false;

		if( !Configuration::get('NPS_WAITING_PAYMENT') ) {
                  Configuration::updateValue('NPS_WAITING_PAYMENT', $this->addState('NPS : Pending payment by customer', 'RoyalBlue'));
                }
                
		if( !Configuration::get('NPS_PENDING_CAPTURE') ) {
                  Configuration::updateValue('NPS_PENDING_CAPTURE', $this->addState('NPS : Authorize approved - Pending capture', 'RoyalBlue'));
                }
                
		if( !Configuration::get('NPS_PENDING_PAYMENT') ) {
                  Configuration::updateValue('NPS_PENDING_PAYMENT', $this->addState('NPS : Approved - Pending payment', 'LimeGreen'));
                }                

		return true;
	}
  
  private static function createMenu() 
  {
    $tabAdminParentNps = new Tab();
    $tabAdminParentNps->id_parent = 0;
    $tabAdminParentNps->class_name = 'AdminParentNps';
    $tabAdminParentNps->position = 50;
    $tabAdminParentNps->name[Configuration::get('PS_LANG_DEFAULT')] = 'NPS';
    $tab1 = $tabAdminParentNps->add();    
    
    $tabAdminNps = new Tab();
    $tabAdminNps->id_parent = $tabAdminParentNps->id;
    $tabAdminNps->class_name = 'AdminNps';
    $tabAdminNps->position = 50;
    $tabAdminNps->name[Configuration::get('PS_LANG_DEFAULT')] = 'Orders';
    $tab2 = $tabAdminNps->add();
    
    $tabAdminInstallments = new Tab();
    $tabAdminInstallments->id_parent = $tabAdminParentNps->id;
    $tabAdminInstallments->class_name = 'AdminInstallments';
    $tabAdminInstallments->position = 1;
    $tabAdminInstallments->name[Configuration::get('PS_LANG_DEFAULT')] = 'Installments';
    $tab3 = $tabAdminInstallments->add();    
    
    return $tab1 && $tab2 && $tab3;
  }

	private function addState($en, $color)
	{
		$orderState = new OrderState();
		$orderState->name = array();
		foreach (Language::getLanguages() AS $language)
		{
			/*if (strtolower($language['iso_code']) == 'en')
				$orderState->name[$language['id_lang']] = $fr;
				else*/
				$orderState->name[$language['id_lang']] = $en;
		}
		$orderState->send_email = false;
		$orderState->color = $color;
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = false;
    $orderState->module_name = $this->name;
		if ($orderState->add())
			copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
		return $orderState->id;
	}

	/**
	 * @brief Uninstall function
	 *
	 * @return Success or failure
	 */
	public function uninstall()
	{
		// Uninstall parent and unregister Configuration
		Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'nps_token`');
    Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'installment`');
    
    $rs = Db::getInstance()->executeS('SELECT id_tab FROM `'._DB_PREFIX_.'tab` WHERE class_name = "AdminParentNps" OR class_name = "AdminNps" OR class_name = "AdminInstallments"');
    foreach($rs as $r) {
      Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tab_lang` WHERE id_tab = '.$r['id_tab']);
    }
    
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tab` WHERE class_name = "AdminParentNps"');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tab` WHERE class_name = "AdminNps"');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tab` WHERE class_name = "AdminInstallments"');    
    
    
		$orderState = new OrderState((int)Configuration::get('NPS_WAITING_PAYMENT'));
		$orderState->delete();
		$orderState = new OrderState((int)Configuration::get('NPS_PENDING_CAPTURE'));
		$orderState->delete();    
		$orderState = new OrderState((int)Configuration::get('NPS_PENDING_PAYMENT'));
		$orderState->delete();        
		Configuration::deleteByName('NPS_WAITING_PAYMENT');
    Configuration::deleteByName('NPS_PENDING_CAPTURE');
    Configuration::deleteByName('NPS_PENDING_PAYMENT');
		if (!parent::uninstall())
			return false;
		return true;
	}

	/**
	 * @brief Main Form Method
	 *
	 * @return Rendered form
	 */
	public function getContent()
	{
		$html = '';

		if (isset($_POST) && isset($_POST['submitNps']))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
			{
				$this->_postProcess();
				$html .= $this->_displayValidation();
			}
			else
				$html .= $this->_displayErrors();
		}
		return $html.$this->_displayAdminTpl();
	}

	/**
	 * @brief Method that will displayed all the tabs in the configurations forms
	 *
	 * @return Rendered form
	 */
	private function _displayAdminTpl()
	{
		$this->context->smarty->assign(array(
				'tab' => array(
					'credential' => array(
						'title' => $this->l('Configuration'),
						'content' => $this->_displayCredentialTpl(),
						'icon' => '../modules/nps/img/credential.png',
						'tab' => 1,
						'selected' => true,
					),            

				),
				'tracking' => 'http://www.prestashop.com/modules/nps.png?url_site='.Tools::safeOutput($_SERVER['SERVER_NAME']).'&id_lang='.(int)$this->context->cookie->id_lang,
				'logo' => '../modules/nps/img/logo.png',
				'script' => array('../modules/nps/js/nps.js'),
				'css' => '../modules/nps/css/nps.css',
				'lang' => ($this->context->language->iso_code != 'en' || $this->context->language->iso_code != 'es' ? 'en' : $this->context->language->iso_code)
			));

		return $this->display(__FILE__, 'tpl/admin.tpl');
	}

	private function _displayHelpTpl()
	{
		return $this->display(__FILE__, 'tpl/help.tpl');
	}

	/**
	 * @brief Credentials Form Method
	 *
	 * @return Rendered form
	 */
	private function _displayCredentialTpl()
	{
		$this->context->smarty->assign(array(
				'formCredential' => './index.php?tab=AdminModules&configure=nps&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=nps',
				'credentialTitle' => $this->l('Log in'),
				'credentialText' => $this->l('In order to use this module, please fill out the form with the logins provided to you by NPS.'),
				'credentialInputVar' => array(
					
          'paymentAction' => array(
            'name' => 'paymentAction',  
            'required' => true,
            'options' => array(
              self::PAYMENT_ACTION_AUTHORIZE => $this->l('authorize'),
              self::PAYMENT_ACTION_AUTOCAPTURE => $this->l('authorize and capture'),
            ),
            'default_value' => (Tools::getValue('paymentAction') ? Tools::getValue('paymentAction') : Configuration::get('NPS_PAYMENT_ACTION')),
            'type' => 'select',
            'label' => $this->l('Payment Action:'),
            'desc' => $this->l('The payment action method you want to choose for each payment.'),
          ),            
					'merchantEmail' => array(
						'name' => 'merchantEmail',
						'required' => true,
						'value' => (Tools::getValue('merchantEmail') ? Tools::safeOutput(Tools::getValue('merchantEmail')) : Tools::safeOutput(Configuration::get('NPS_MERCHANT_EMAIL'))),
						'type' => 'text',
						'label' => $this->l('Merchant Email:'),
						'desc' => $this->l('Your email account.'),
					),                        
					'merchantId' => array(
						'name' => 'merchantId',
						'required' => true,
						'value' => (Tools::getValue('merchantId') ? Tools::safeOutput(Tools::getValue('merchantId')) : Tools::safeOutput(Configuration::get('NPS_MERCHANT_ID'))),
						'type' => 'text',
						'label' => $this->l('Merchant ID:'),
						'desc' => $this->l('The Merchant ID given to you by NPS at the creation of your account.'),
					),
					'gatewayUrl' => array(
						'name' => 'gatewayUrl',
						'required' => true,
						'value' => (Tools::getValue('gatewayUrl') ? Tools::safeOutput(Tools::getValue('gatewayUrl')) : Tools::safeOutput(Configuration::get('NPS_GATEWAY_URL'))),
						'type' => 'text',
						'label' => $this->l('Gateway URL:'),
						'desc' => $this->l('The Gateway URL given to you by NPS at the creation of your account.'),
					),            
					'apiKey' => array(
						'name' => 'apiKey',
						'required' => true,
						'value' => (Tools::getValue('apiKey') ? Tools::safeOutput(Tools::getValue('apiKey')) : Tools::safeOutput(Configuration::get('NPS_API_KEY'))),
						'type' => 'text',
						'label' => $this->l('Secret Key:'),
						'desc' => $this->l('The Secret Key given to you by NPS at the creation of your account.'),
					),
            
            )
        ));
		return $this->display(__FILE__, 'tpl/credential.tpl');
	}

	/**
	 * @brief Validate Method
	 *
	 * @return update the module depending
	 */
	private function _postValidation()
	{
		if (Tools::isSubmit('submitNps'))
			$this->_postValidationCredentials();
	}

	private function _postValidationCredentials()
	{
		$merchantId = Tools::getValue('merchantId');
		$apiKey = Tools::getValue('apiKey');
    $merchantEmail = Tools::getValue('merchantEmail');
    $paymentAction = Tools::getValue('paymentAction');
    $gatewayUrl = Tools::getValue('gatewayUrl');
    
		if( in_array('',array($merchantId,$apiKey,$merchantEmail,$paymentAction,$gatewayUrl)) ) {
			$this->_postErrors[] = $this->l('Please fill out the entire form.');
    }
		if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
			$this->_postErrors[] = $this->l('You have to select a shop before creating new orders.');
    }        
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('submitNps'))
			$this->_postProcessCredentials();
	}

	private function _postProcessCredentials()
	{
    Configuration::updateValue('NPS_PAYMENT_ACTION', pSQL(Tools::getValue('paymentAction')));
    Configuration::updateValue('NPS_MERCHANT_EMAIL', pSQL(Tools::getValue('merchantEmail')));
    Configuration::updateValue('NPS_MERCHANT_ID', pSQL(Tools::getValue('merchantId')));
    Configuration::updateValue('NPS_GATEWAY_URL', pSQL(Tools::getValue('gatewayUrl')));
		Configuration::updateValue('NPS_API_KEY', pSQL(Tools::getValue('apiKey')));
	}

	private function _displayErrors()
	{
		$this->context->smarty->assign('postErrors', $this->_postErrors);
		return $this->display(__FILE__, 'tpl/error.tpl');
	}

	private function _displayValidation()
	{
		$this->context->smarty->assign('postValidation', array($this->l('Updated succesfully')));
		return $this->display(__FILE__, 'tpl/validation.tpl');
	}

	private function _displayWarning()
	{
		$this->context->smarty->assign('warnings', array($this->l('Please, activate Soap (PHP extension).')));
		return $this->display(__FILE__, 'tpl/warning.tpl');
	}

	/**
	 * @brief to display the payment option, so the customer will pay by merchant ware
	 */
	public function hookPayment($params)
	{
		if (!$this->active || Configuration::get('NPS_MERCHANT_ID') == '')
			return false;

		$this->context->smarty->assign(array('pathSsl' => (_PS_VERSION_ >= 1.4 ? Tools::getShopDomainSsl(true, true) : '' ).__PS_BASE_URI__.'modules/nps/', 'modulePath'=> $this->_path));

		return $this->display(__FILE__, 'tpl/payment.tpl');
	}
  
  public function hookDisplayBackOfficeHeader($params) {
    $this->context->controller->addCSS(($this->_path) . 'menuTabIcon.css');
  }  

	/**
	 * @brief Validate a payment, verify if everything is right
	 */
	public function validation($retries=0)
	{
    if (!isset($_POST['psp_TransactionId']) && !isset($_POST['psp_TransactionId'])) {
			self::AddLog('Missing request parameter psp_TransactionId.');
    }else {
			$psp_TransactionId = isset($_POST['psp_TransactionId']) ? $_POST['psp_TransactionId'] : $_POST['psp_TransactionId'];    
    }
    
    $queried_psp_tx_ids = $this->context->cookie->queried_psp_tx_ids ? unserialize($this->context->cookie->queried_psp_tx_ids) : array();
    if(is_array($queried_psp_tx_ids) 
            && in_array($_POST['psp_TransactionId'],$queried_psp_tx_ids)) {
      Tools::redirect('index.php');
      return;
    }        
    
    try {
      
      // SimpleQueryTx
      $psp_parameters_query = array(
         'psp_Version'         => '1',
         'psp_MerchantId'      => Configuration::get('NPS_MERCHANT_ID'),
         'psp_QueryCriteria'   => 'T',
         'psp_QueryCriteriaId' => $psp_TransactionId,
         'psp_PosDateTime'     => date('Y-m-d H:i:s')	
      );      
      
      $cli = new PSP_Client();
      $cli->setDebug(false);
      $cli->setPrintRequest(false);
      $cli->setPrintResponse(false);
      $cli->setConnectTimeout(20);
      $cli->setExecuteTimeout(40);
      $cli->setUrl(Configuration::get('NPS_GATEWAY_URL'));
      $cli->setWsdlCache(_PS_CACHE_DIR_, 43200);
      $cli->setSecretKey(Configuration::get('NPS_API_KEY'));
      $cli->setMethodName('SimpleQueryTx');
      $cli->setMethodParams($psp_parameters_query);
      $result = $cli->send();    
      
      self::addLog($result, @$result['psp_Transaction']['psp_TransactionId']);
      
      $psp_ResponseCod = $result['psp_Transaction']['psp_ResponseCod'];
      $idCart = $result['psp_Transaction']['psp_MerchTxRef'];
      $this->context->cart = new Cart((int)$idCart);
      
      $queried_psp_tx_ids = $this->context->cookie->queried_psp_tx_ids ? unserialize($this->context->cookie->queried_psp_tx_ids) : array();
      $queried_psp_tx_ids[] = $_POST['psp_TransactionId'];
      $this->context->cookie->queried_psp_tx_ids = serialize($queried_psp_tx_ids);            

      if (!$this->context->cart->OrderExists())
      {
        self::AddLog('The shopping card '.(int)$idCart.' doesn\'t have any order created');
        return false;
      }
      
      if (Validate::isLoadedObject($this->context->cart)) {
        $orders = Db::getInstance()->ExecuteS('SELECT `id_order` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = '.(int)$this->context->cart->id.'');
        
        foreach ($orders as $order) {
          $order = new Order((int)$order['id_order']);
          $currency = new Currency((int)$this->context->cart->id_currency);
        
          switch($psp_ResponseCod) {
            case 0:
              $order->total_paid_tax_incl = (float)Tools::ps_round((float)$result['psp_Transaction']['psp_Amount']/100,2);
              $order->total_paid = $order->total_paid_tax_incl;
              if( $result['psp_Transaction']['psp_Operation'] == 'TC - Autorizacion' ) {
                $order->setCurrentState((int)Configuration::get('NPS_PENDING_CAPTURE'));
              }else {
                $order->setCurrentState((int)Configuration::get('PS_OS_PAYMENT'));
              }
              break;
            case 16:
            case 18:
            case 20:
              $order->setCurrentState((int)Configuration::get('NPS_PENDING_PAYMENT'));
              break;
            default:
              $order->setCurrentState((int)Configuration::get('PS_OS_ERROR'));
              self::AddLog('The shopping card '.(int)$idCart.' has been rejected by NPS psp_ResponseCod='.(int)$psp_ResponseCod);
              break;
          }
          
          if (_PS_VERSION_ >= 1.5)
          {
            $payment = $order->getOrderPaymentCollection();
            if (isset($payment[0]))
            {
              $payment[0]->transaction_id = pSQL($result['psp_Transaction']['psp_TransactionId']);
              $payment[0]->amount = $order->total_paid;
              $payment[0]->save();
            }else {
              $order->addOrderPayment($order->total_paid, null, pSQL($result['psp_Transaction']['psp_TransactionId']));
            }
          }
          
          $customer = new Customer((int)Context::getContext()->cart->id_customer);
          
          Tools::redirect('index.php?controller=order-confirmation&id_cart='.Context::getContext()->cart->id.'&id_module='.$this->id.'&id_order='.$order->id.'&key='.$customer->secure_key,null,new Link());
          exit;
        }
      }
      else
      {
        self::AddLog('The shopping cart '.(int)$idCart.' was not found during the payment validation step');
      }        
      
    }catch(Exception $e) {
      self::AddLog($e->getMessage(), 2);
      if($result === FALSE && $retries <= 5) {
        sleep(2);
        $this->validation($retries+1);
      }            
    }
  }  
  
  /**
   * call capture method by NPS webservice
   */
  public function capture($order) 
  {
    global $cookie;
    
    if(Tools::getValue('capture_amount') <= 0) {
      throw new Exception("Amount to capture must be greater than 0.");
    }
    
    $payment = $order->getOrderPaymentCollection();
    if (isset($payment[0])) {
      $psp_TransactionId_Orig = $payment[0]->transaction_id;
    }    
    $cart = new Cart((int)$order->id_cart);
    
    $psp_parameters = array (
      'psp_Version'            => '1',
      'psp_MerchantId'         => Configuration::get('NPS_MERCHANT_ID'),
      'psp_TxSource'           => 'WEB',
      'psp_MerchTxRef'         => $cart->id.'t'.time(),
      'psp_TransactionId_Orig' => $psp_TransactionId_Orig,
      'psp_AmountToCapture'    => self::orderTotal2Cent(Tools::getValue('capture_amount')),
      'psp_PosDateTime'        => date('Y-m-d H:i:s'),
      'psp_UserId'             => substr($cookie->id_employee,0,64),
    );

    $cli = new PSP_Client();
    $cli->setDebug(false);
    $cli->setPrintRequest(false);
    $cli->setPrintResponse(false);
    $cli->setConnectTimeout(20);
    $cli->setExecuteTimeout(40);
    $cli->setUrl(Configuration::get('NPS_GATEWAY_URL'));
    $cli->setWsdlCache(_PS_CACHE_DIR_, 43200);
    $cli->setSecretKey(Configuration::get('NPS_API_KEY')); // psp_test
    $cli->setMethodName('Capture');
    $cli->setMethodParams($psp_parameters);
    $result = $cli->send();    
    
    self::addLog($result, @$result['psp_TransactionId']);

    $psp_ResponseCod = $result['psp_ResponseCod'];    

    switch(true) {
      case $psp_ResponseCod == '0':
      case strpos($result['psp_ResponseExtended'], ' 1089 '):  
        $order->setCurrentState((int)Configuration::get('PS_OS_PAYMENT'));
        /*
        $_POST['payment_transaction_id'] = $result['psp_TransactionId'];
        $_GET['payment_transaction_id'] = $result['psp_TransactionId'];
        $_POST['payment_amount'] = Tools::getValue('capture_amount');
        $_GET['payment_amount'] = Tools::getValue('capture_amount');
        */
        
        if (_PS_VERSION_ >= 1.5)
        {
          $payments = $order->getOrderPaymentCollection();
          $c = count($payments)-1;
          
          for($i=$c;$i>=0;$i--)
          {
            if( !isset($bool) ) {
              $payments[$i]->transaction_id = pSQL($result['psp_TransactionId']);
              $payments[$i]->amount = pSQL(Tools::getValue('capture_amount'));
              $payments[$i]->save();
              $bool = true;
            }else {
              $payments[$i]->delete();
            }            
          }
        }
        break;
      default:
        throw new Exception('[NPS] psp_ResponseCod='.(int)$psp_ResponseCod.'; psp_ResponseExtended='.$result['psp_ResponseExtended']);
        // Tools::redirectAdmin( Context::getContext()->link->getAdminLink('AdminNps')."&id_order={$order->id}&captureorder" );
        break;
    }
  }
  
  function getIp() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return strlen($ip) >= 7 ? $ip : null;
  }  
  
  public static function formatDeviceToPspDevice($device) {
    switch($device) {
      case Context::DEVICE_TABLET: return '3';
      case Context::DEVICE_MOBILE; return '2';
      case Context::DEVICE_COMPUTER; return '1';          
    }
  }
  
  public static function toCents($v) {
    return intval($v*100);
  }
  
  public function pay() {

    global $cart,$customer;
    
    try
    {
      $order = new Order(Order::getOrderByCartId($cart->id));      
      $installment = Installment::getInstallment(Context::getContext()->shop->id, $_REQUEST['psp_Product'], $_REQUEST['psp_NumPayments']);
      
      if(!$installment) {
        throw new Exception("Installment is not defined.");
      }
      
      switch(Configuration::get('NPS_PAYMENT_ACTION')) {
        case self::PAYMENT_ACTION_AUTOCAPTURE:
          $method_name = 'PayOnLine_3p';
          break;
        case self::PAYMENT_ACTION_AUTHORIZE:
          $method_name = 'Authorize_3p';
          break;        
      }
      
      // compra online 3p
      $psp_parameters = array(
        'psp_Version'                  => '1',
        'psp_MerchantId'               => Configuration::get('NPS_MERCHANT_ID'),
        'psp_TxSource'                 => 'WEB',
        'psp_PosDateTime'              => date('Y-m-d H:i:s'),
        'psp_MerchTxRef'               => $cart->id.'t'.time(),
        'psp_MerchOrderId'             => $order->id,
        'psp_Amount'                   => self::orderTotal2Cent( (float)$cart->getOrderTotal() + ((float)$installment['rate']*(float)$cart->getOrderTotal()/100) ),
        'psp_NumPayments'              => $_REQUEST['psp_NumPayments'],
        'psp_Currency'                 => substr(Context::getContext()->currency->iso_code_num,0,3),
        'psp_CustomerMail'             => substr($customer->email,0,255),
        'psp_MerchantMail'             => substr(Configuration::get('PS_SHOP_EMAIL'),0,255),
        'psp_Product'                  => $_REQUEST['psp_Product'],
        'psp_Country'                  => substr(Context::getContext()->country->iso_code,0,3),
        'psp_ReturnURL'                => $_REQUEST['confirmationUrl'],
        'psp_FrmLanguage'              => 'es_AR',
        'psp_FrmBackButtonURL'         => $_REQUEST['responseUrl'],
        'psp_PurchaseDescription'      => substr('Order '.$order->id,0,255),
        'psp_SoftDescriptor'           => substr('Order '.$order->id,0,15),
      );
      
      $psp_parameters['psp_MerchantAdditionalDetails'] = array(
          'ShoppingCartInfo' => 'Prestashop '._PS_VERSION_,
          'ShoppingCartPluginInfo' => 'Prestashop NPS Plugin '.$this->version,
      );      
      

      $AccountPreviousActivity = Db::getInstance()->NumRows("SELECT so.id_order FROM `"._DB_PREFIX_."orders` so WHERE so.id_customer = '".$customer->id."' AND so.module = 'nps')"); 

      $psp_parameters['psp_CustomerAdditionalDetails'] = array(
        'EmailAddress'=>substr($customer->email,0,255),
        'IPAddress'=>substr($this->getIp(),0,45),
        'AccountID'=>substr($customer->id,0,128),
        'AccountCreatedAt'=>date('Y-m-d',strtotime($customer->date_add)),
        'AccountPreviousActivity' => $AccountPreviousActivity >= 1 ? '1' : '0',
        // 'AccountHasCredentials'=>'1', // nose lo que es
        // 'DeviceType'=>self::formatDeviceToPspDevice($this->context->getDevice()),
        // 'DeviceFingerPrint'=>'2',
        // 'BrowserLanguage'=>'ES',
        // 'HttpUserAgent'=>'2',        
      );




      $billingAddress = new Address(intval($this->context->cart->id_address_invoice));
      $billingAddressCountry = new CountryCore(intval($billingAddress->id_country));
      $billingAddressState = new StateCore((int)$billingAddress->id_state);


      $matches = array();
      $input_string = $billingAddress->address1;
      if(preg_match('/(?P<address>[^d]+) (?P<number>\d+.?)/', $input_string, $matches)){
          $street = $matches['address'];
          $number = $matches['number'];
      } else { // no number found, it is only address
          $street = $input_string;
          $number = null;
      }            


      if($billingAddress->firstname
          && ($street && $number)
          && $billingAddress->city
          && $billingAddressCountry->iso_code
          && $billingAddress->postcode) {
        $psp_parameters['psp_BillingDetails'] = array(
          'Person'=>array(
              'FirstName'=>substr($billingAddress->firstname,0,50),
              'LastName'=>substr($billingAddress->lastname,0,30),
              //'MiddleName'=>$order->getBillingAddress()->getMiddlename(),
              //'PhoneNumber1'=>'4123-1234', // no disponible 
              //'PhoneNumber2'=>'4123-1234', // no disponible
              //'Gender'=>'M', // no disponible
              //'DateOfBirth'=> '1987-01-01', // no disponible
              //'Nationality'=>'ARG', // no disponible
              //'IDNumber'=>'32123123', // no disponible
              //'IDType'=>'1', // no disponible
          ),
          'Address'=>array(
              'Street'=>substr($street,0,50),
              'HouseNumber'=>substr($number,0,15),
              // 'AdditionalInfo'=>'3', // no disponible
              'City'=>substr($billingAddress->city,0,40),
              'StateProvince'=>substr($billingAddressState->name,0,40),
              'Country'=>substr($billingAddressCountry->iso_code,0,3),
              'ZipCode'=>substr($billingAddress->postcode,0,10),
          ),
        );

      }




      $shippingAddress = new Address(intval($this->context->cart->id_address_delivery));
      $shippingAddressCountry = new CountryCore(intval($shippingAddress->id_country));
      $shippingAddressState = new StateCore((int)$shippingAddress->id_state);

      $matches = array();
      $input_string = $shippingAddress->address1;
      if(preg_match('/(?P<address>[^d]+) (?P<number>\d+.?)/', $input_string, $matches)){
          $street = $matches['address'];
          $number = $matches['number'];
      } else { // no number found, it is only address
          $street = $input_string;
          $number = null;
      }                   

      if($shippingAddress->firstname
           && ($street && $number)
           && $shippingAddress->city
           && $shippingAddressCountry->iso_code
           && $shippingAddress->postcode ) {

        $psp_parameters['psp_ShippingDetails'] = array(
          // 'TrackingNumber'=>null, // no disponible
          'Method'=>substr($this->formatShippingMethod(null),0,2),
          'Carrier'=>substr($this->formatShippingCarrier(null),0,3),
          //'DeliveryDate'=> null, // no disponible
          'FreightAmount' => (int)$this->context->cart->getOrderShippingCost() > 0 ? substr((string)self::toCents($this->context->cart->getOrderShippingCost()),0,12) : null,
          // 'GiftMessage'=>'4', // no disponible
          // 'GiftWrapping'=>'4', // no disponible
          'PrimaryRecipient'=>array( // required
            'FirstName'=>substr($shippingAddress->firstname,0,50), // required
            'LastName'=>substr($shippingAddress->lastname,0,30),
            // 'MiddleName'=>$order->getShippingAddress()->getData('middlename'),
            // 'PhoneNumber1'=>'4', // no disponible
            // 'PhoneNumber2'=>'4', // no disponible
            // 'Gender'=>'M', // no disponible
            // 'DateOfBirth'=>'1987-01-01', // no disponible
            // 'Nationality'=>'ARG', // no disponible
            // 'IDNumber'=>'4', // no disponible
            // 'IDType'=>'4', // no disponible
          ),
          'Address'=>array( // required
            'Street'=>substr($street,0,50),
            'HouseNumber'=>substr($number,0,15),
            // 'AdditionalInfo'=>'3', // no disponible
            'City'=>substr($shippingAddress->city,0,40),
            'StateProvince'=>substr($shippingAddressState->name,0,40),
            'Country'=>substr($shippingAddressCountry->iso_code,0,3),
            'ZipCode'=>substr($shippingAddress->postcode,0,10),
          ),

        );        
      }    

      foreach($this->context->cart->getProducts() as $item) {
        $psp_parameters['psp_OrderDetails']['OrderItems'][] = array(
          'Quantity'=>(string)intval($item['cart_quantity']),
          'UnitPrice'=>self::toCents($item['price']),
          'Description'=>substr($item['name'],0,127),  
          // 'Type'=>$item->getType(), // no disponible
          'SkuCode'=>substr($item['reference'],0,48),
          // 'ManufacturerPartNumber'=>'1', // no disponible
          // 'Risk'=>'H' // ?????????
        );
      }
      

      $cli = new PSP_Client();
      $cli->setDebug(false);
      $cli->setPrintRequest(false);
      $cli->setPrintResponse(false);
      $cli->setConnectTimeout(5);
      $cli->setExecuteTimeout(60);
      $cli->setUrl(Configuration::get('NPS_GATEWAY_URL'));
      $cli->setWsdlCache(_PS_CACHE_DIR_, 43200);
      $cli->setSecretKey(Configuration::get('NPS_API_KEY')); //psp_test
      $cli->setMethodName($method_name);
      $cli->setMethodParams($psp_parameters);
      $result = $cli->send();
      
      self::addLog($result, @$result['psp_TransactionId']);
      
      if(is_array($result) && count($result)) 
      {
        // Submit 3p Form
        if(@$result['psp_TransactionId'])
        {
          $html = <<<BALBEFSD
          <form name=form action="{$result['psp_FrontPSP_URL']}" method="POST">
          <input type="hidden" name="psp_Session3p" value="{$result['psp_Session3p']}">
          <input type="hidden" name="psp_TransactionId" value="{$result['psp_TransactionId']}">
          <input type="hidden" name="psp_MerchantId" value="{$result['psp_MerchantId']}">
          <input type="hidden" name="psp_MerchTxRef" value="{$result['psp_MerchTxRef']}">
          </form>
          <script>document.forms['form'].submit();</script>
BALBEFSD;

          echo $html;
          exit();
        }else {
          throw new Exception($result['psp_ResponseExtended'], $result['psp_ResponseCod']);
        }
      }else {
        throw new Exception("Gateway has not response", "9999");
      }
    }
    catch (Exception $e)
    {
      $order->setCurrentState((int)Configuration::get('PS_OS_ERROR'));
      self::AddLog('ERROR CODE['.$e->getCode().'] MESSAGE['.$e->getMessage().']');
    }    
    
    Tools::redirect('index.php?controller=order-confirmation&id_cart='.Context::getContext()->cart->id.'&id_module='.$this->id.'&id_order='.@$order->id.'&key='.$customer->secure_key);
    exit();    
  }
  
  public static function orderTotal2Cent($i) {
    $i = number_format($i, 2);
    $i = str_replace('.', '', $i);
    $i = str_replace(',', '', $i);
    return $i;
  } 
  
  public static function retrieveProducts() {
		return array(
      /* array('value' => 1, 'name' => 'American Express'),
      array('value' => 2, 'name' => 'Diners'),
      array('value' => 5, 'name' => 'Mastercard'),
      array('value' => 8, 'name' => 'Cabal'),
      array('value' => 9, 'name' => 'Naranja'),
      array('value' => 10, 'name' => 'Kadicard'),
			array('value' => 14, 'name' => 'Visa'),
      array('value' => 21, 'name' => 'Nevada'),
      array('value' => 42, 'name' => 'Tarjeta Shopping'),
      array('value' => 43, 'name' => 'Italcred'),
      array('value' => 48, 'name' => 'Mas(cencosud)'),
      array('value' => 50, 'name' => 'Pyme Nacion'),
      array('value' => 38, 'name' => 'Nativa'),
      array('value' => 65, 'name' => 'Argencard'),
      array('value' => 72, 'name' => 'Consumax'),
      array('value' => 15, 'name' => 'Favacard'),
      array('value' => 101, 'name' => 'Discover'),
      array('value' => 17, 'name' => 'Lider'),
      array('value' => 95, 'name' => 'Coopeplus'),
      array('value' => 20, 'name' => 'Credimas'),
      array('value' => 61, 'name' => 'Nexo'),
      array('value' => 101, 'name' => 'Nuevacard'), */
        
array('value' => 128, 'name' => 'Comfama'),
array('value' => 127, 'name' => 'WebPay'),
array('value' => 126, 'name' => 'Credz'),
array('value' => 125, 'name' => 'UATP'),
array('value' => 124, 'name' => 'Socios BBVA'),
array('value' => 123, 'name' => 'Codensa'),
array('value' => 122, 'name' => 'Qida'),
array('value' => 121, 'name' => 'CTC Group'),
array('value' => 120, 'name' => 'Club Dia'),
array('value' => 119, 'name' => 'Tuya'),
array('value' => 118, 'name' => 'Grupar'),
array('value' => 117, 'name' => 'Carrefour'),
array('value' => 116, 'name' => 'Hiper'),
array('value' => 115, 'name' => 'UnionPay'),
array('value' => 114, 'name' => 'Metro'),
array('value' => 113, 'name' => 'OH!'),
array('value' => 112, 'name' => 'Ripley'),
array('value' => 110, 'name' => 'BBPS'),
array('value' => 108, 'name' => 'SuperCard'),
array('value' => 107, 'name' => 'RedCompra'),
array('value' => 106, 'name' => 'Credencial COL'),
array('value' => 105, 'name' => 'Hipercard'),
array('value' => 104, 'name' => 'Aura'),
array('value' => 103, 'name' => 'Magna'),
array('value' => 102, 'name' => 'Elo'),
array('value' => 101, 'name' => 'Discover'),
array('value' => 95, 'name' => 'Coopeplus'),
array('value' => 91, 'name' => 'Credi Guia'),
array('value' => 72, 'name' => 'Consumax'),
array('value' => 66, 'name' => 'Maestro'),
array('value' => 65, 'name' => 'Argencard'),
array('value' => 63, 'name' => 'NATIVA'),
array('value' => 61, 'name' => 'Nexo'),
array('value' => 58, 'name' => 'Club La Voz'),
array('value' => 57, 'name' => 'MC Bancor'),
array('value' => 55, 'name' => 'Visa Debito'),
array('value' => 53, 'name' => 'Argenta'),
array('value' => 52, 'name' => 'Club Speedy'),
array('value' => 51, 'name' => 'Clarin 365'),
array('value' => 50, 'name' => 'Pyme Nacion'),
array('value' => 49, 'name' => 'Naranja MO'),
array('value' => 48, 'name' => 'Mas (Cencosud)'),
array('value' => 47, 'name' => 'Club Arnet'),
array('value' => 46, 'name' => 'Club Personal'),
array('value' => 45, 'name' => 'Club La Nacion'),
array('value' => 43, 'name' => 'Italcred'),
array('value' => 42, 'name' => 'Tarjeta Shopping'),
array('value' => 38, 'name' => 'Nativa MC'),
array('value' => 35, 'name' => 'CMR Falabella'),
array('value' => 34, 'name' => 'Sol'),
array('value' => 33, 'name' => 'Patagonia 365'),
array('value' => 29, 'name' => 'Visa Naranja'),
array('value' => 21, 'name' => 'Nevada'),
array('value' => 20, 'name' => 'Credimas'),
array('value' => 17, 'name' => 'Lider'),
array('value' => 15, 'name' => 'Favacard'),
array('value' => 14, 'name' => 'Visa'),
array('value' => 10, 'name' => 'Kadicard'),
array('value' => 9, 'name' => 'Naranja'),
array('value' => 8, 'name' => 'Cabal'),
array('value' => 5, 'name' => 'Mastercard'),
array('value' => 4, 'name' => 'JCB'),
array('value' => 2, 'name' => 'Diners'),
array('value' => 1, 'name' => 'American Express'),
        
        
        
        
    );
  }
  
  public static function retrieveProductsAsKeyValue() {
    $r = array();
    foreach(self::retrieveProducts() as $product) {
      $r[$product['value']] = $product['name'];
    }
    return $r;
  }
  
  public static function retrieveProductsForOptionsQuery() {
    $r = array();
    foreach(self::retrieveProducts() as $product) {
      $r[] = array('key'=>$product['value'],'name'=>$product['name']);
    }
    return $r;    
  }  
  
  public static function getProductNameByIdProduct($idProduct) {
    $products = self::retrieveProductsAsKeyValue();
    return isset($products[$idProduct]) ? $products[$idProduct] : false;
  }  
  
  public function formatShippingMethod($v=null) {
    switch($v) {
      // case 1: return "10"; //Carrier designado por el comprador
      // case 1: return "20"; //Descarga de contenidos
      // case 1: return "30"; //Militar
      // case 1: return "40"; //Entrega - Mismo dia
      // case 1: return "41"; //Entrega - Dia siguiente / Por la noche
      // case 1: return "42"; //Entrega - Segundo dia
      // case 1: return "43"; //Entrega - Tercer dia
      // case 1: return "50"; //Retiro en comercio
      default: return "99"; //Otro      
    }
  }

  public function formatShippingCarrier($v=null) {
    switch($v) {
      // case 1: return "100"; //UPS
      // case 1: return "101"; //USPS
      // case 1: return "102"; //FedEx
      // case 1: return "103"; //DHL
      // case 1: return "104"; //Purolator
      // case 1: return "105"; //Greyhound
      // case 1: return "200"; //Correo Argentino
      // case 1: return "201"; //OCA
      default: return "999"; //Other / Otro / Outro        
    }
  }  
  
  
  public static function addLog($message=null,$psp_TransactionsId=0)  {
    $severity = 2;
    $object_type = "Nps";
    $object_id = '9998888';
    
    if( is_array($message) && count($message) ) {
      Logger::AddLog("[NPS][$psp_TransactionsId][".urldecode(http_build_query($message))."]", $severity, $error_code = null, $object_type, $object_id, true);
    }
    if( is_string($message) && strlen($message) ) {
      Logger::AddLog("[NPS][$psp_TransactionsId][$message]", $severity, $error_code = null, $object_type, $object_id, true);
    }
  }  
  
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;
    
		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('PS_OS_PAYMENT') || $state == Configuration::get('NPS_PENDING_CAPTURE'))
		{
			$this->context->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'status' => 'ok',
				'id_order' => $params['objOrder']->id,
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->context->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->context->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}  

}