<li class="SpeechBubble message  message-removed" <? if($hide):?> style="display:none" <? endif;?>  >
	<div class='speech-bubble-message-removed' >
		<?php if($showundo):?>
			<?php echo wfMsg($comment->isMain() ? 'wall-'.( $comment->isAdminDelete() ? 'deleted':'removed' ). '-thread-undo':'wall-removed-reply-undo', '<a data-id="'.$comment->getId().'"  class="message-undo-remove" >'.wfMsg('wall-message-undoremove').'</a>'); ?>
			<?php else:?>
				<?php echo wfMsg('wall-removed-reply'); ?>
			<?php endif;?>
	</div>
</li>
