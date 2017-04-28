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
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link href="{$css|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">
<img src="{$tracking}" alt="tracking" style="display:none"/>
<div class="nps-module-wrapper">
	<div class="nps-module-inner-wrap">
		<img src="{$logo|escape:'htmlall':'UTF-8'}" alt="logo" class="nps-logo" />
                <p class="nps-module-intro">{l s='NPS is a platform devoted to on-line payment processing, offering credit cards and alternative means of payment acceptance to e-commerce sites. Through a unique technical integration, a site could be connected to all means of payment available in Latin America.' mod='nps'}<br /><br />
                <div class="nps-module-right-col">
                    <h1>{l s='NPS - Net Payment Services' mod='nps'}</h1>
                    <ul>                        
                        <li><b>{l s='PCI certification' mod='nps'}</b><br/>{l s='PCI DSS international certification ensures protection, confidentiality and integrity of all cardholder payment information.' mod='nps'}</li>
                        <li><b>{l s='Reconciliation services' mod='nps'}</b><br/>{l s='This service introduce the highest level of automation in the reconciliation process, making the crosschecking between registered operations in platform and payment files provided by processors.' mod='nps'}</li>
                        <li><b>{l s='Fraud prevention' mod='nps'}</b><br/>{l s='NPS uses a fraud prevention engine chosen  by hundreds of sites all over the world. This technology allows to instantaneously accept, challenge or deny any suspected operation in real time, reducing chargebacks and improving sales indicators.' mod='nps'}</li>
                        <li><a href="www.securenps.com" target="_blank">www.securenps.com</a></li>
                    </ul>
		</div>
	</div>
	<ul id="menuTab">
	  {foreach from=$tab item=li}
	  <li id="menuTab{$li.tab|escape:'htmlall':'UTF-8'}" class="menuTabButton {if $li.selected}selected{/if}">{if $li.icon != ''}<img src="{$li.icon|escape:'htmlall':'UTF-8'}" alt="{$li.title|escape:'htmlall':'UTF-8'}"/>{/if} {$li.title|escape:'htmlall':'UTF-8'}</li>
	  {/foreach}
	</ul>
	<div id="tabList">
	  {foreach from=$tab item=div}
	  <div id="menuTab{$div.tab|escape:'htmlall':'UTF-8'}Sheet" class="tabItem {if $div.selected}selected{/if}">
	    {$div.content}
	  </div>
	  {/foreach}
	</div>
</div>
{foreach from=$script item=link}
<script type="text/javascript" src="{$link|escape:'htmlall':'UTF-8'}"></script>
{/foreach}
