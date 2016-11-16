/*
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
*/
$(document).ready(function(){
	$('ul.tree.dhtml').hide();

	//to do not execute this script as much as it's called...
	if(!$('ul.tree.dhtml').hasClass('dynamized'))
	{
		//add growers to each ul.tree elements
		$('ul.tree.dhtml ul').prev().before("<span class='grower OPEN'><span></span></span>");
		
		//dynamically add the '.last' class on each last item of a branch
		$('ul.tree.dhtml ul li:last-child, ul.tree.dhtml li:last-child').addClass('last');
		
		//collapse every expanded branch

		$('ul.tree.dhtml span.grower.OPEN').addClass('CLOSE').removeClass('OPEN').parent().find('ul:first').hide();
		$('ul.tree.dhtml').show();
		
		//open the tree for the selected branch
			$('ul.tree.dhtml .selected').parents().each( function() {
				if ($(this).is('ul'))
					toggleBranch($(this).prev().prev(), true);
			});
			toggleBranch( $('ul.tree.dhtml .selected').prev(), true);
		
		//add a fonction on clicks on growers
		$('ul.tree.dhtml span.grower').click(function(){
			toggleBranch($(this));
		});
		//mark this 'ul.tree' elements as already 'dynamized'
		$('ul.tree.dhtml').addClass('dynamized');

		$('ul.tree.dhtml').removeClass('dhtml');
	}
});
$(window).load(function() {
    $('.categories_block_thumb img').each(function() {
        var that = $(this),
            image = new Image();
        image.src = that.attr("src");
        that.parent().css({
            // 'width': image.width,
            'height': (parseInt(image.height) / 2) + 'px'
        });
        that.css({
        	"marginLeft": "-" + (parseInt(image.width) / 2) + 'px'
		});
    });
});
//animate the opening of the branch (span.grower jQueryElement)
function openBranch(jQueryElement, noAnimation)
{
		jQueryElement.addClass('OPEN').removeClass('CLOSE').parent().addClass('OPEN').removeClass('CLOSE');
		var parent = jQueryElement.parent();
		if(noAnimation) {
			parent.find('ul:first').show();
		}
		else {
			parent.find('ul:first').slideDown();
		}
		parent.find('.categories_block_thumb img').css({
			top: 'auto',
			bottom: 0
		});
}
//animate the closing of the branch (span.grower jQueryElement)
function closeBranch(jQueryElement, noAnimation)
{
	jQueryElement.addClass('CLOSE').removeClass('OPEN').parent().addClass('CLOSE').removeClass('OPEN');
	var parent = jQueryElement.parent();
	if(noAnimation)
		parent.find('ul:first').hide();
	else
		parent.find('ul:first').slideUp();
	parent.find('.categories_block_thumb img').css({
		top: 0,
		bottom: 'auto'
	});
}

//animate the closing or opening of the branch (ul jQueryElement)
function toggleBranch(jQueryElement, noAnimation)
{
	if(jQueryElement.hasClass('OPEN'))
		closeBranch(jQueryElement, noAnimation);
	else
		openBranch(jQueryElement, noAnimation);
}