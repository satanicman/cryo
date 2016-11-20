$(document).ready(function(){
	$('#home-page-tabs li:first, #index .tab-content ul:first').addClass('active');
	$('.slider.products').slick({
		arrows: true,
		dots: false,
		slidesToShow: 3,
		centerMode: true,
		centerPadding: '60px',
		nextArrow: '<button type="button" class="slick-next"><i class="product-next-icon icon"></i>Next</button>',
		prevArrow: '<button type="button" class="slick-prev"><i class="product-prev-icon icon"></i>Previous</button>'
	});
	$('.slider.index').slick({
		arrows: false,
		dots: true,
		slidesToShow: 1,
		// responsive: [
		// 	{
		// 		breakpoint: 768,
		// 		settings: {
		// 			arrows: false,
		// 			centerMode: true,
		// 			centerPadding: '40px',
		// 			slidesToShow: 3
		// 		}
		// 	},
		// 	{
		// 		breakpoint: 480,
		// 		settings: {
		// 			arrows: false,
		// 			centerMode: true,
		// 			centerPadding: '40px',
		// 			slidesToShow: 1
		// 		}
		// 	}
		// ]
	});
});