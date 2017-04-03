$(document).ready(function () {
    $(document).on('click', '#block_various_links_top .b-toggle', function(e) {
        e.preventDefault();

        $('#block_various_links_top').toggleClass('menu_open');
    });
});