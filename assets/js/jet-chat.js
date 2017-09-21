( function( $, settings ) {

	"use strict";

	var jetChat = {

		init: function() {

			$( document )
				.on( 'click.jetChat', '.jet-chat__form-submit', this.handleForm )
				.on( 'click.jetChat', '.jet-locales__trigger', this.showLocales )
				.on( 'click.jetChat', '.jet-chat-trigger', this.showChatForm )
				.on( 'click.jetChat', '.jet-locales__item', this.switchLocale );

		},

		handleForm: function() {

			var $this   = $( this ),
				$chat   = $this.closest( '.jet-chat' ),
				locale  = $chat.find( '.jet-locales__trigger' ).data( 'locale' ),
				$name   = $chat.find( 'input[name="uname"]' ),
				name    = $name.val(),
				$mail   = $chat.find( 'input[name="umail"]' ),
				mail    = $mail.val(),
				isValid = true;

			if ( ! name ) {
				isValid = false;
				$name.addClass( 'jet-chat-error' );
			}

			if ( ! mail ) {
				isValid = false;
				$mail.addClass( 'jet-chat-error' );
			}

			if ( ! isValid ) {
				return;
			}

			$.ajax({
				url: settings.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'jet_chat_get_url',
					name:   name,
					mail:   mail,
					locale: locale
				}
			}).done( function( response ) {

				console.log( response );

			});

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
			var $this = $( this );

			$this.addClass( 'chat-active' );
		}

	};

	jetChat.init();

} ( jQuery, jetChatSettings ) );