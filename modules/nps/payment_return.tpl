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

{if $status == 'ok'}
	<p class="alert alert-success">{l s='Your order is complete.' mod='nps'}</p>
    <div class="box order-confirmation">
    <h3 class="page-subheading">{l s='Payment has been accepted:' mod='nps'}</h3>
		- {l s='Payment amount.' mod='nps'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br />- {l s='For any questions or for further information, please contact our' mod='nps'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='nps'}</a>.
	</div>
{else}
	<p class="alert alert-warning">
		{l s='Payment attempt was not successful. Please try again later and if the error persist, contact our' mod='nps'} 
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='nps'}</a>.
	</p>
{/if}
