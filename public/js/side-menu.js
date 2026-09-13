/**
 * Side menu submenu toggle behaviour.
 * Kept as a standalone script so it never gets lost inside the bundled
 * vendor file (public/js/app.js).
 */
$(document).ready(function () {
	$('.side-menu-list li.with-sub').each(function () {
		var parent = $(this),
			clickLink = parent.find('> span'),
			subMenu = parent.find('> ul');

		subMenu.hide();

		clickLink.click(function (event) {
			if ($(event.target).closest('a').length) {
				return;
			}

			if (parent.hasClass('opened')) {
				parent.removeClass('opened');
				subMenu.stop(true, true).slideUp();
				subMenu.find('.opened').removeClass('opened');
			} else {
				if (clickLink.parents('.with-sub').length == 1) {
					$('.side-menu-list .opened').removeClass('opened').find('ul').stop(true, true).slideUp();
				}
				parent.addClass('opened');
				subMenu.stop(true, true).slideDown();
			}
		});
	});

	$('#show-hide-sidebar-toggle, .hamburger').click(function () {
		$('body').toggleClass('sidebar-hidden');
	});
});
