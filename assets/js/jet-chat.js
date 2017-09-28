( function( $, settings ) {

	"use strict";

	var jetChat = {

		init: function() {

			$( document )
				.on( 'click.jetChat', '.jet-chat__form-submit', this.handleForm )
				.on( 'keyup.jetChat', '.jet-chat__form-field', this.openOnEnter )
				.on( 'click.jetChat', '.jet-locales__trigger', this.showLocales )
				.on( 'click.jetChat', '.jet-chat-trigger', this.showChatForm )
				.on( 'click.jetChat', '.jet-locales__item', this.switchLocale )
				.on( 'click.jetChat', 'body', this.hideChatForm )
				.on( 'click.jetChat', '.jet-chat, .jet-chat-trigger', this.stopProp )
				.on( 'focus.jetChat', '.jet-chat__form-field', this.clearErrors );

		},

		stopProp: function( event ) {
			event.stopPropagation();
		},

		handleForm: function() {

			var $this   = $( this ),
				$chat   = $this.closest( '.jet-chat' ),
				locale  = $chat.find( '.jet-locales__trigger' ).data( 'locale' ),
				$name   = $chat.find( 'input[name="uname"]' ),
				name    = $name.val(),
				$mail   = $chat.find( 'input[name="umail"]' ),
				mail    = $mail.val(),
				isValid = true,
				data    = {},
				btnText = $this.html();

			if ( ! name ) {
				isValid = false;
				$name.addClass( 'jet-chat-error' );
			}

			if ( ! mail ) {
				isValid = false;
				$mail.addClass( 'jet-chat-error' );
			}

			if ( ! $mail[0].validity.valid ) {
				$mail.addClass( 'jet-chat-error' );
			}

			if ( ! isValid ) {
				return;
			}

			data = {
				action: 'start-chat',
				name: name,
				mail: mail
			};

			window.open( settings.chaturl + '?' + $.param( data ), '_blank', 'height=700,width=800' );

		},

		openOnEnter: function( event ) {
			if( 13 === event.keyCode ){
				$( '.jet-chat__form-submit' ).trigger( 'click.jetChat' );
			}
		},

		showLocales: function() {
			$( this ).toggleClass( 'show-locales' );
		},

		switchLocale: function() {

			var $this    = $( this ),
				$trigger = $this.closest( '.jet-locales__list' ).siblings( '.jet-locales__trigger' ),
				code     = $this.data( 'value' ),
				label    = $this.data( 'label' ),
				flag     = $this.data( 'flag' );

			if ( $this.hasClass( 'active-locale' ) ) {
				$trigger.removeClass('show-locales');
			} else {
				$trigger
					.removeClass( 'show-locales' )
					.data( 'locale', code )
					.html( $.parseJSON( flag ) + label );
				$this.addClass( 'active-locale' ).siblings().removeClass( 'active-locale' );
			}

		},

		showChatForm: function() {
			var $this = $( this ),
				$chat = $this.closest( '.jet-chat-wrap' ).find( '.jet-chat' );

			$this.addClass( 'chat-active' );
			$chat.addClass( 'chat-active' );
		},

		hideChatForm: function() {
			var $chat    = $( '.jet-chat-wrap' ).find( '.jet-chat' ),
				$trigger = $( '.jet-chat-wrap' ).find( '.jet-chat-trigger' );

			$chat.removeClass( 'chat-active' );
			$trigger.removeClass( 'chat-active' );
		},

		clearErrors: function() {
			$( this ).removeClass( 'jet-chat-error' );
		}

	};

	jetChat.init();

} ( jQuery, jetChatSettings ) );