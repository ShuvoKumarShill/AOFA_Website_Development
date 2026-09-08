/**
 * AOFA Theme — theme.js
 *
 * Lightweight theme JavaScript. Deferred, no jQuery.
 * Spec Item: 001-initial-setup
 *
 * @package AofaTheme
 */

( function () {
	'use strict';

	/**
	 * Adds a scrolled class to the header when the user scrolls down.
	 * Used to trigger a sticky header shadow effect via CSS.
	 */
	function initStickyHeader() {
		const header = document.querySelector( '.aofa-site-header' );
		if ( ! header ) {
			return;
		}

		window.addEventListener(
			'scroll',
			function () {
				if ( window.scrollY > 20 ) {
					header.classList.add( 'is-scrolled' );
				} else {
					header.classList.remove( 'is-scrolled' );
				}
			},
			{ passive: true }
		);
	}

	/**
	 * Smooth scroll for anchor links.
	 */
	function initSmoothScroll() {
		document.querySelectorAll( 'a[href^="#"]' ).forEach( function ( anchor ) {
			anchor.addEventListener( 'click', function ( event ) {
				const target = document.querySelector( this.getAttribute( 'href' ) );
				if ( target ) {
					event.preventDefault();
					target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				}
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initStickyHeader();
		initSmoothScroll();
	} );
}() );
