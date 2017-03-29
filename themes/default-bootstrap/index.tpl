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
{capture name='homeTop'}{hook h='homeTop'}{/capture}
<a href="{smartblog::GetSmartBlogLink()}" id="news_button">{l s='News and articles'}</a>
<div class="clearfix"></div>
{if $smarty.capture.homeTop}
	<div class="clearfix home-top">
		{$smarty.capture.homeTop}
		<div class="col-lg-6 col-md-6 col-sm-12 slider-col">
			<h3 class="main-title">{l s='Implemented projects'}</h3>
            {hook h="displaySlidersPro" slider="sample"}
		</div>
	</div>
{/if}
{if isset($HOOK_HOME_TAB_CONTENT) && $HOOK_HOME_TAB_CONTENT|trim}
	</div>
	</div>
	</div>
	<div class="products-tabs-wrap">
	    <div class="products-tabs-container container">
	    <h3 class="main-title">{l s='Special offers'}</h3>
	{if isset($HOOK_HOME_TAB) && $HOOK_HOME_TAB|trim}
        <ul id="home-page-tabs" class="nav nav-tabs clearfix">
			{$HOOK_HOME_TAB}
		</ul>
	{/if}
	<div class="tab-content">{$HOOK_HOME_TAB_CONTENT}</div>
</div>
	    </div>


				<div id="columns" class="container">

					<div class="row">
					<div id="center_column" class="center_column col-xs-12 col-sm-12">
{/if}
{if isset($HOOK_HOME) && $HOOK_HOME|trim}
	<div class="clearfix">
		{$HOOK_HOME}
	</div>
{/if}
