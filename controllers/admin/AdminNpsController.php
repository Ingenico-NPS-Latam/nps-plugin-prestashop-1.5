<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'nps/nps.php');

class AdminNpsController extends AdminOrdersControllerCore
{
	public function __construct()
	{
    global $cookie;
    
    parent::__construct();
    
    $this->_where .= " AND a.module = 'nps' ";
    
    $this->addRowAction('capture');
    
    $this->list_no_link = true;
    
    if( !empty($cookie->confirmation) ) {
      $this->confirmations[] = $cookie->confirmation;
      unset($cookie->confirmation);
    }

	}

  /**
   * Render NPS Capture form/captureorder
   * @return boolean
   */
	public function renderForm()
	{
    global $cookie;
    
    if( isset($cookie->exception) ) {
      $this->errors[] = $cookie->exception;
      unset($cookie->exception);
    }    
    
		$order = new Order(Tools::getValue('id_order'));
		if (!Validate::isLoadedObject($order))
			throw new PrestaShopException('object can\'t be loaded');
    
    $currency = new Currency((int)$order->id_currency);
    
		$this->fields_form = array(
        'legend' => array(
          'title' => $this->l('Capture').' | '.$this->l('total order amount is').' '.Tools::displayPrice($order->total_paid, $currency),
          'icon' => 'icon-credit-card',
          'desc' => 'hola mundo',
        ),
        'input' => array(
          array(
            'type' => 'text',
            'label' => $this->l('Amount to capture:'),
            'name' => 'capture_amount',
            'required' => true,
            'hint' => $this->l('the amount to capture, partial or total in format 1000,00 or 1000.00'),
            'desc' => $this->l('the amount to capture, partial or total in format 1000,00 or 1000.00'),      
          ),    
          array(
            'type' => 'hidden',
            'name' => 'id_order',
          ),    
        ),    
        'submit'=>array(
            'title' => $this->l('Capture'),
        ),
    );    
    
		$this->fields_value = array(
			'capture_amount' => $order->total_paid,
      'id_order'=> $order->id,
		);        
    
		return AdminController::renderForm();
	}

	public function initToolbar()
	{
    $res = parent::initToolbar();
    
    if ($this->display == null)
		{
      $this->toolbar_btn['standard_refund'] = array(
        'short' => 'Create',
        'href' => 'https://backoffice.sub1.com.ar/',
        'target' => '_blank',
        'desc' => "Online Refund Transactions",
        'class' => 'process-icon-partial_refund process-icon-new partial_refund',
      );
    }
    
    unset($this->toolbar_btn['new']);
    
    return $res;
	}

  
	public function initContent()
	{
    if ( Tools::getValue('id_order') && Tools::getValue('captureorder') !== false )
			$this->display = 'edit';

		return parent::initContent();
	}  
  
  
  /**
   * Process capture form and send capture to NPS
   */
	public function processSave()
	{
    global $cookie, $link;
    $order = new Order(Tools::getValue('id_order'));          
    
    try {
      $nps = new Nps();
      $nps->capture($order);
      $cookie->confirmation = $this->l('Capture successful');
      Tools::redirectAdmin( Context::getContext()->link->getAdminLink('AdminNps') );
    }catch(Exception $e) {
      Logger::AddLog($e->getMessage(), 2, null, null, null, true);
      $cookie->exception = $e->getMessage();
      Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminNps')."&id_order={$order->id}&captureorder");
    }
    
  }  
  
	public function displayCaptureLink($token = null, $id, $name = null)
	{
    $order = new Order((int)$id);
    if($order->getCurrentState() != (int)Configuration::get('NPS_PENDING_CAPTURE')) {
      return;
    }
    
		$tpl = $this->context->smarty->createTemplate('controllers/nps/list_action_capture.tpl');
		if (!array_key_exists('Capture', self::$cache_lang))
			self::$cache_lang['Capture'] = $this->l('Capture', 'Helper');

		$tpl->assign(array(
			'href' => Tools::safeOutput(self::$currentIndex.'&'.$this->identifier.'='.$id.'&capture'.$this->table.'&token='.($token != null ? $token : $this->token)),
			'action' => self::$cache_lang['Capture'],
		));

		return $tpl->fetch();

	}  
  
	public function displayViewLink($token = null, $id, $name = null)
	{
    $order = new Order((int)$id);
		$tpl = $this->context->smarty->createTemplate('controllers/nps/list_action_view.tpl');
		if (!array_key_exists('View', self::$cache_lang))
			self::$cache_lang['View'] = $this->l('View');
    
		$tpl->assign(array(
			'href' => Tools::safeOutput($this->context->link->getAdminLink('AdminOrders').'&'.$this->identifier.'='.$id.'&view'.$this->table),
			'action' => self::$cache_lang['View'],
		));

		return $tpl->fetch();
	}    
  
}

