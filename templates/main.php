<?php
script('ownbackup', 'script');
script('ownbackup', 'chosen.jquery.min');
style('ownbackup', 'style');
style('ownbackup', 'chosen.min');
?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>
</div>
