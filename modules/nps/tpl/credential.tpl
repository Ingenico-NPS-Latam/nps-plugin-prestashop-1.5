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

<form action="{$formCredential|escape:'htmlall':'UTF-8'}" method="POST">
	<fieldset>
		<p>{$credentialText|escape:'htmlall':'UTF-8'}</p>
		<input type="hidden" name="submitNps" value="1" />
		{foreach from=$credentialInputVar item=input}
		<label from="{$input.name|escape:'htmlall':'UTF-8'}">{$input.label|escape:'htmlall':'UTF-8'}</label>
		<div class="margin-form">
		
      {if $input.type == 'text'}
			<input type="{$input.type|escape:'htmlall':'UTF-8'}" name="{$input.name|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}" value="{$input.value|escape:'htmlall':'UTF-8'}" /> {if $input.required}<span style="color:red">*</span>{/if} {$input.desc|escape:'htmlall':'UTF-8'}
			{elseif $input.type == 'radio'}
	    	{foreach from=$input.values item=val}
	    		<input type="{$input.type|escape:'htmlall':'UTF-8'}" {if $val == $input.value}checked='checked'{/if} name="{$input.name|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}{$val}" value="{$val|escape:'htmlall':'UTF-8'}" /> {if $input.required}<span style="color:red">*</span>{/if} {$val|escape:'htmlall':'UTF-8'}
			{/foreach}
		  {/if}

      {if $input.type == 'select'}
      <select id="{$input.name|escape:'htmlall':'UTF-8'}" name="{$input.name|escape:'htmlall':'UTF-8'}">
        <option value=""></option>  
      {foreach $input.options as $value => $label}
        <option id="" value="{$value|escape:'htmlall':'UTF-8'}" {if ($input.default_value == $value)} selected {/if} >
          {$label|escape:'htmlall':'UTF-8'}
        </option>
      {/foreach}
      </select>  
      {/if}
    
		</div>
		{/foreach}
		<div class="margin-form">
			<input type="submit" class="button" value="{l s='Save' mod='nps'}" />
		</div>
	</fieldset>
</form>