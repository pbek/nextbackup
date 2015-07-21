<?php
script('ownbackup', ['script', 'chosen.jquery.min']);
style('ownbackup', ['style', 'chosen.min']);
?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php if ($_["isAdminUser"]): ?>
				<?php print_unescaped($this->inc('part.content')); ?>
			<?php else: ?>
				<div class="section">
					You have to be admin to use <strong>OwnBackup</strong>!
				</div>
			<?php endif ?>
		</div>
	</div>
</div>
