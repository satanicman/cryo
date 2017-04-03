$(document).ready(function(){
	$('#home-page-tabs li:first, #index .tab-content ul:first').addClass('active');

	// $(document).on('init', '.slider.products', function (slick) {
	// 	var matrixRegex = /matrix\((-?\d*\.?\d+),\s*(-?\d*\.?\d+),\s*(-?\d*\.?\d+),\s*(-?\d*\.?\d+),\s*(-?\d*\.?\d+),\s*(-?\d*\.?\d+)\)/,
	// 		slide = $('.slider.products .slick-slide'),
	// 		last = $('.slider.products .slick-active + .slick-slide:not(.slick-active)').first(),
	// 		first = $('.slider.products .slick-slide:not(.slick-active)'),
	// 		transform = last.css('transform'),
	// 		slideWidth = parseInt(slide.attr('style').replace(/\D*/, '')),
	// 		scale;
	// 	if(typeof transform != 'undefined') {
	// 		scale = transform.match(matrixRegex)[4];
	// 		first.css('transform', 'scale(' + scale + ') translateX(' + Math.round(slideWidth*scale/2 + parseInt(slide.css('padding-right').replace(/\D*/, ''))) + 'px)');
	// 		last.css('transform', 'scale(' + scale + ') translateX(-' + Math.round(slideWidth*scale/2 + parseInt(slide.css('padding-right').replace(/\D*/, ''))) + 'px)');
	// 	}
	// });
	$('.slider.products').slick({
		arrows: true,
		dots: false,
		slidesToShow: 3,
		centerMode: true,
		centerPadding: '120px',
		nextArrow: '<button type="button" class="slick-next"><i class="product-next-icon icon"></i>Next</button>',
		prevArrow: '<button type="button" class="slick-prev"><i class="product-prev-icon icon"></i>Previous</button>',
		responsive: [
			{
				breakpoint: 1261,
				settings: {
					centerMode: false,
					centerPadding: 0,
				}
			},
			{
				breakpoint: 992,
				settings: {
					centerMode: false,
					centerPadding: 0,
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 768,
				settings: {
                    arrows: false,
					centerMode: false,
					centerPadding: 0,
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 401,
				settings: {
                    arrows: false,
					centerMode: false,
					centerPadding: 0,
					slidesToShow: 1,
				}
			}
		]
	});
	$('.slider.index').slick({
		arrows: false,
		dots: true,
        autoplay: true,
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