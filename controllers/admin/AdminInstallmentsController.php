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

include_once(_PS_MODULE_DIR_.'/nps/nps.php');

class AdminInstallmentsController extends AdminController
{
	public function __construct()
	{
		$this->table = 'installment';
		$this->className = 'Installment';
		$this->lang = false;
		$this->addRowAction('edit');
		$this->addRowAction('delete');    
		$this->deleted = false;
		$this->context = Context::getContext();

    if (Context::getContext()->cookie->shopContext)
    {
      $split = explode('-', Context::getContext()->cookie->shopContext);    
      $shop_id = $split[1];
    }else {
      $shop_id = $this->context->shop->id;
    }
		
    $this->_select = 'id_payment_product, id_installment, id_shop';
    $this->_where = "AND id_shop = '{$shop_id}'";
		$this->_orderBy = 'id_installment';
		$this->_orderWay = 'DESC';

		$this->fields_list = array(
		'id_payment_product' => array(
			'title' => $this->l('Product'),
			'align' => 'center',
		),
		'qty' => array(
			'title' => $this->l('Qty'),
			'align' => 'center',
		),
		'rate' => array(
			'title' => $this->l('Rate Percentage'),
			'align' => 'center',
		),        
        );

		parent::__construct();
	}
  
  
	public function renderForm()
	{
		if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive())
			$this->errors[] = $this->l('You have to select a shop before creating new installments.');

		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Installment:'),
				'image' => '../img/admin/money.gif'
			),
			'input' => array(
				array(
					'type' => 'select',
					'label' => $this->l('Product:'),
					'name' => 'id_payment_product',
					'size' => 1,
					'maxlength' => 11,
					'required' => true,
					'options' => array(
						'query' => Nps::retrieveProductsForOptionsQuery(),
						'name' => 'name',
						'id' => 'key'
					)
				),          
				array(
					'type' => 'text',
					'label' => $this->l('Qty:'),
					'name' => 'qty',
					'size' => 30,
					'maxlength' => 32,
					'required' => true,
					'hint' => $this->l('Only integer numbers are allowed.')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Rate Percentage:'),
					'name' => 'rate',
          'desc' => 'e.g: 25.00',
					'size' => 30,
					'maxlength' => 32,
					'required' => true,
					'hint' => $this->l('Only numbers with or without decimals are allowed.')
				),          
        array(
          'type' => 'hidden',  
          'name' => 'id_shop',
        ),          
          
			)
		);

    $this->fields_form['submit'] = array(
			'title' => $this->l('Save   '),
			'class' => 'button'
		);
    
		$this->fields_value = array(
			'id_shop' => Context::getContext()->shop->id
		);           

		return parent::renderForm();
	}
  
}
