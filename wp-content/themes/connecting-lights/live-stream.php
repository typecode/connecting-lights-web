<?php
/*

Template Name: Live Stream

*/

get_header();
?>

	<div class="live-stream">
		<iframe width="640" height="360" frameborder="0" scrolling="no" src="http://www.streamingtank.tv/mission/?id=503ce9d0186d6b4131000001&title=Main"></iframe>
	
		<?php require_once(TEMPLATEPATH . "/incl/actions.php"); ?>
	</div>

<?php
get_footer();
?>