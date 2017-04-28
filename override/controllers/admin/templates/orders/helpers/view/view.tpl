{*
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{include file="controllers/orders/helpers/view/view.tpl"}

{if $order->module == 'nps'}
{literal}
<script>
window.onload = function() {

var trTotalProducts = document.getElementById('total_products');
var labelTotalProducts = trTotalProducts.cells[0];
var amountTotalProducts = trTotalProducts.cells[1];


var trTotalPaymentFee = document.createElement('tr');
trTotalPaymentFee.id = "total_payment_fee";

var labelPaymentFee = document.createElement('td');
labelPaymentFee.innerHTML = "<b>Payment Fee</b>";
labelPaymentFee.className = labelTotalProducts.className;
labelPaymentFee.width = labelTotalProducts.width;

var amountPaymentFee = document.createElement('td');
amountPaymentFee.innerHTML = "{/literal}{displayPrice price=($order->total_paid_tax_incl + $order->total_discounts_tax_incl - $order->total_products_wt - $order->total_wrapping_tax_incl - $order->total_shipping_tax_incl) currency=$currency->id}{literal}";
amountPaymentFee.className = amountTotalProducts.className;
amountPaymentFee.align = amountTotalProducts.align;

trTotalPaymentFee.appendChild(labelPaymentFee);
trTotalPaymentFee.appendChild(amountPaymentFee);


var tableTotalDetails = trTotalProducts.parentNode;
tableTotalDetails.insertBefore(trTotalPaymentFee, document.getElementById('total_order'));


}
</script>
{/literal}
{/if}