<?php
/**
 * Chat form template
 */
?>
<div class="jet-chat-wrap">
	<div class="jet-chat">
		<div class="jet-chat__heading">
			<div class="jet-chat__title">Live Chat</div>
			<?php echo $locales_select; ?>
		</div>
		<div class="jet-chat__form">
			<div class="jet-chat__form-message">
				<div class="jet-chat__form-title">Consultant</div>
				<div class="jet-chat__form-text">We help you to choose the right product.</div>
			</div>
			<div class="jet-chat__form-row">
				<input type="text" class="jet-chat__form-field" name="uname" placeholder="Name">
			</div>
			<div class="jet-chat__form-row">
				<input type="email" class="jet-chat__form-field" name="umail" placeholder="Email Address">
			</div>
			<button class="jet-chat__form-submit">Start Chat</button>
		</div>
	</div>
	<div class="jet-chat-trigger__wrap">
		<button class="jet-chat-trigger"><span>Start Chat</span></button>
	</div>
</div>