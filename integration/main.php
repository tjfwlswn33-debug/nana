<?php
include_once __DIR__ . '/_common.php';
define('_MAIN_', true);
include_once(G5_PATH.'/head.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/main.css">', 0);
?>

<div id="main_body">

<?php
$main_content = get_site_content('site_main');
if($main_content) {
	echo $main_content;
} else {
?>
	<div id="no_design_main">
		<div class="txt-center">
			<iframe src="https://everla.tistory.com/91" frameborder="0" style="width:100%;height:450px;"></iframe>
		</div>
	</div>
<?php } ?>
</div>


<script src="<?php echo htmlspecialchars(G5_URL . '/skin/board/nana_character/shell.js?v=4', ENT_QUOTES, 'UTF-8'); ?>" data-nana-shell-url="<?php echo htmlspecialchars(G5_URL . '/index.php', ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<?php
include_once(G5_PATH.'/tail.php');
?>
