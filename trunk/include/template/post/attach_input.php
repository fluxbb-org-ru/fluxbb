			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_file['Attachments'] ?></legend>
					<div class="infldset">
<?php if (defined('MAX_FILE_SIZE')): ?>
						<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE ?>" />
<?php endif; ?>
<?php
	if (!empty($attachments)):
?>
						<p><ul class="attachments">
<?php foreach ($attachments as $attachment): ?>
							<li>
								<a href="file.php?action=info&amp;id=<?php echo $attachment['id'] ?>" class="<?php echo mime_to_class($attachment['mime']) ?>" title="<?php echo pun_htmlspecialchars($attachment['title']=='' ? $attachment['filename'] : $attachment['title']) ?>"><?php echo pun_htmlspecialchars($attachment['filename']) ?></a>
								<label><input type="checkbox" name="del_attach_id[]" value="<?php echo $attachment['id'] ?>" />&nbsp;<?php echo $lang_file['Delete'] ?></label>
							</li>
<?php endforeach; endif; ?>
						</ul></p>
<?php
?>
						<input type="file" name="attach[]" size="50"  tabindex="<?php echo $cur_index++ ?>" /><br /><br />
						<input type="file" name="attach[]" size="50"  tabindex="<?php echo $cur_index++ ?>" /><br /><br />
						<input type="file" name="attach[]" size="50"  tabindex="<?php echo $cur_index++ ?>" /><br /><br />
						<input type="file" name="attach[]" size="50"  tabindex="<?php echo $cur_index++ ?>" /><br /><br />
						<input type="file" name="attach[]" size="50"  tabindex="<?php echo $cur_index++ ?>" /><br /><br />
					</div>
				</fieldset>
