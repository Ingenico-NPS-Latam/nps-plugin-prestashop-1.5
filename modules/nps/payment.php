<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
0* This source file is subject to the Academic Free License (AFL 3.0)
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

$useSSL = true;
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

include_once(_PS_MODULE_DIR_.'/nps/nps.php');
include_once(_PS_MODULE_DIR_.'/nps/lib/Sub1/psp_client.php');

class NpsController extends FrontController
{
	public $ssl = true;

  public function process() {
    parent::process();
		$params = $this->initParams();
		self::$smarty->assign(array(
				'formLink' => Configuration::get('NPS_DEMO') != 'yes' ? 'https://psp.nps.com.ar/' : 'https://psp.nps.com.ar/',
				'npsRedirection' => $params,
        'npsProducts' => self::getChoicesForProducts(),
			));    
  }
  
	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_MODULE_DIR_.'nps/tpl/redirect.tpl');
	}

	public function initParams()
	{

		$tax = (float)self::$cart->getOrderTotal() - (float)self::$cart->getOrderTotal(false);
		$base = (float)self::$cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) + (float)self::$cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS) - (float)$tax;
		if($tax == 0)
			$base = 0;

		$currency = new Currency(self::$cart->id_currency);

		$language = new Language(self::$cart->id_lang);

		$customer = new Customer(self::$cart->id_customer);

		$ref = 'nps_'.Configuration::get('PS_SHOP_NAME').'_'.(int)self::$cart->id;

		$token = md5(Tools::safeOutput(Configuration::get('NPS_API_KEY')).'~'.Tools::safeOutput(Configuration::get('NPS_MERCHANT_ID')).'~'.$ref.'~'.(float)self::$cart->getOrderTotal().'~'.Tools::safeOutput($currency->iso_code));

		$params = array(
			array('value' => (Configuration::get('NPS_DEMO') == 'yes' ? 1 : 0), 'name' => 'test'),
			array('value' => Tools::safeOutput(Configuration::get('NPS_MERCHANT_ID')), 'name' => 'merchantId'),
			array('value' => $ref, 'name' => 'referenceCode'),
			array('value' => substr(Configuration::get('PS_SHOP_NAME').' Order', 0, 255), 'name' => 'description'),
			array('value' => (float)self::$cart->getOrderTotal(), 'name' => 'amount'),
			array('value' => Tools::safeOutput($customer->email), 'name' => 'buyerEmail'),
			array('value' => (float)$tax, 'name' => 'tax'),
			array('value' => 'PRESTASHOP', 'name' => 'extra1'),
			array('value' => (float)$base, 'name' => 'taxReturnBase'),
			array('value' => Tools::safeOutput($currency->iso_code), 'name' => 'currency'),
			array('value' => Tools::safeOutput($language->iso_code), 'name' => 'lng'),
			array('value' => Tools::safeOutput($token), 'name' => 'signature'),
			array('value' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__, 'name' => 'responseUrl'),
			array('value' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/nps/validation.php', 'name' => 'confirmationUrl'),
		);

		if (Configuration::get('NPS_ACCOUNT_ID') != 0)
			$params[] = array('value' => (int)Configuration::get('NPS_ACCOUNT_ID'), 'name' => 'accountId');

		if (Db::getInstance()->getValue('SELECT `token` FROM `'._DB_PREFIX_.'nps_token` WHERE `id_cart` = '.(int)self::$cart->id))
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'nps_token` SET `token` = "'.pSQL($token).'" WHERE `id_cart` = '.(int)self::$cart->id);
		else
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'nps_token` (`id_cart`, `token`) VALUES ('.(int)self::$cart->id.', \''.pSQL($token).'\')');

		return $params;
	}

	public function createPendingOrder()
	{
		$nps = new Nps();
		$nps->validateOrder((int)self::$cart->id, (int)Configuration::get('NPS_WAITING_PAYMENT'), (float)self::$cart->getOrderTotal(), $nps->displayName, NULL, array(), NULL, false,	self::$cart->secure_key);
	}
  
  public function getCart() {
    return self::$cart;
  }
  
  public static function getChoicesForProducts() {
    $installment = new Installment();
    $r = $installment->getEnabledProducts(Context::getContext()->shop->id);
    
    $choices = array();
    foreach($r as $id) {
      $choices[] = array('value' => $id, 'name' => Nps::getProductNameByIdProduct($id));
    }
    
    return $choices;
  }    
  
}

$npsController = new NpsController();

if (isset($_GET['create-pending-order'])) {
  $npsController->createPendingOrder();
  $nps = new Nps();
  $nps->pay();
  exit;
}elseif (isset($_GET['retrieve-installments'])) {
  $id_payment_product = $_REQUEST['id_payment_product'];
  $installment = new Installment();
  $r = $installment->getInstallments(Context::getContext()->shop->id, $id_payment_product);
  
  echo json_encode($r);
  exit;
}else {
	$npsController->run();
}