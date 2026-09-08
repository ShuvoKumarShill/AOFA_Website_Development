/**
 * AOFA Theme — theme.js
 *
 * Vanilla JS only. No jQuery dependency.
 * Handles: mobile nav, skip-to-content, WCAG focus management.
 *
 * @package AofaTheme
 * @since   1.0.0
 */

( function () {
	'use strict';

	// ── Skip to content link ────────────────────────────────────────────────
	// Inject the skip link if it is not already in the template
	const existingSkip = document.querySelector( '.skip-to-content' );
	if ( ! existingSkip ) {
		const skip = document.createElement( 'a' );
		skip.href      = '#main-content';
		skip.className = 'skip-to-content';
		skip.textContent = 'Skip to main content';
		document.body.insertBefore( skip, document.body.firstChild );
	}

	// ── Announce page title to screen readers on navigation (SPA-safety) ───
	const title = document.querySelector( 'title' );
	if ( title ) {
		const announcer = document.createElement( 'div' );
		announcer.setAttribute( 'aria-live', 'polite' );
		announcer.setAttribute( 'aria-atomic', 'true' );
		announcer.className = 'sr-only';
		announcer.id        = 'aofa-sr-announcer';
		document.body.appendChild( announcer );
	}

	// ── Header: sticky shadow enhancement ──────────────────────────────────
	const header = document.querySelector( '.aofa-site-header' );
	if ( header ) {
		const onScroll = () => {
			if ( window.scrollY > 10 ) {
				header.classList.add( 'is-scrolled' );
			} else {
				header.classList.remove( 'is-scrolled' );
			}
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	// ── Card hover: keyboard-accessible ────────────────────────────────────
	document.querySelectorAll( '.aofa-card' ).forEach( ( card ) => {
		const link = card.querySelector( 'a' );
		if ( link ) {
			// Make the whole card keyboard navigable
			card.setAttribute( 'role', 'link' );
			card.setAttribute( 'tabindex', '0' );
			card.addEventListener( 'keydown', ( e ) => {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					link.click();
				}
			} );
		}
	} );

} )();
