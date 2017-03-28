{*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{include file="$tpl_dir./errors.tpl"}
{if isset($category)}
	{if isset($subcategories)}
        {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories) }
		<div id="subcategories">
			<p class="main-title">{l s='Subcategories'}</p>
			<ul class="clearfix row">
			{foreach from=$subcategories item=subcategory}
				<li class="col-xs-6 col-sm-6 col-md-4">
                    <div class="subcategory-container">
                        <div class="subcategory-image">
                            <a href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                                {if $subcategory.id_image}
                                    <img class="replace-2x img-responsive" src="{$link->getCatImageLink($subcategory.link_rewrite, $subcategory.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{$subcategory.name|escape:'html':'UTF-8'}" width="{$homeSize.width}" height="{$homeSize.height}" />
                                {else}
                                    <img class="replace-2x img-responsive" src="{$img_cat_dir}{$lang_iso}-default-home_default.jpg" alt="{$subcategory.name|escape:'html':'UTF-8'}" width="{$homeSize.width}" height="{$homeSize.height}" />
                                {/if}
                            </a>
                        </div>
                        <h5><a class="subcategory-name" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}">{$subcategory.name|truncate:75:' (...)'|escape:'html':'UTF-8'}</a></h5>
                    </div>
				</li>
			{/foreach}
			</ul>
		</div>
        {/if}
	{/if}
	{if $category->id AND $category->active}
		{if $products}
			{include file="./product-list.tpl" products=$products}
		{/if}
	{elseif $category->id}
		<p class="alert alert-warning">{l s='This category is currently unavailable.'}</p>
	{/if}
{/if}
