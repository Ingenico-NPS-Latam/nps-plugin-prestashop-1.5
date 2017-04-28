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
<fieldset class="tab-configure">
	<h4 class="first">{l s='How to get a NPS account:' mod='nps'}</h4>
	<p>{l s='Go to' mod='nps'} <a href="http://www.latinamericanpayments.com/prestashop" rel="external">{l s='NPS' mod='nps'}</a> {l s='and fill out the form to create your account.' mod='nps'}</p>
	<h4>{l s='How to configure the NPS module:' mod='nps'}</h4>
	<ul>
		<li class="first"><p>{l s='Enter your username and password to the Sign in  located in the upper right corner in' mod='nps'} <a href="http://www.nps.com.ar" rel="external">{l s='www.nps.com.ar' mod='nps'}</a><br>
		<img alt="" class="info-img" src="{$module_dir}img/info-img1.jpg" /></p></li>
		<li><p>{l s='In the NPS administrative module go to the "Configuration" tab' mod='nps'}<br><br>
		<img alt="" class="info-img" src="{$module_dir}img/info-img2.jpg" /></p>
</li>
		<li><p>{l s='In the "Technical information" section you will find the ApiKey and Merchant Id' mod='nps'}<br>
		<img alt="" class="info-img" src="{$module_dir}img/info-img3.jpg" /></p></li>
		<li class="last"><p>{l s='With this information, go to "Credentials" within the PrestaShop NPS module and fill in the required fields' mod='nps'}</p><br>
		<p>{l s='Press the "Save" button' mod='nps'}</p></li>
	</ul>
	<h4>{l s='Please consider:' mod='nps'}</h4>
	<p>{l s='If you enable the "Test Mode" all transactions to be processed will not be real' mod='nps'} <sup>*</sup></p>
	<p class="note"><sup>*</sup> {l s='this mode should never be active in production for real transactions' mod='nps'}</p>
</fieldset>