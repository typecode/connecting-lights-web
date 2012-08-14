<?php
/*

Template Name: Mobile Site

*/

get_header();

?>

		<img src="<?php bloginfo("template_url") ?>/img/mobile/splash.png" alt="Connecting Light" class="splash" />

		<script>
			page.features.push(function(app) {
				var $e = $("#overview"),
					$more = $e.find(".more"),
					$toggle = $e.find(".toggle"),
					$expand = $toggle.find(".expand"),
					$collapse = $toggle.find(".collapse"),
					$splash = $(".splash"),
					$footer = $("footer"),
					$modal = $("#send-message"),
					$msgtrigger = $("#send-message-trigger");

				$toggle.click(function(e) {
					e.preventDefault();
					if ($more.is(":visible")) {
						$more.slideUp(300);
						$expand.show();
						$collapse.hide();
					} else {
						$more.slideDown(300);
						$expand.hide();
						$collapse.show();
					}
				});
				
				$msgtrigger.click(function(e) {
					e.preventDefault();
					// $("html, body").animate({ scrollTop: 0 }, "slow");
					$splash.fadeOut();
					$footer.fadeOut();
					$e.fadeOut("slow", function() {
						$(this).empty().html($modal)	
					}).fadeIn("slow", function() {
						$("html, body").animate({ scrollTop: 0 }, "slow");
					});
				});
			});
		</script>

		<div class="xFull mobile-content">
			<div class="inner">
			
				<div class="overview" id="overview">
				
					<div>
		
						<p><strong>Connecting Light</strong> is a digital art installation along Hadrian’s Wall World Heritage Site. The installation consists of hundreds of large-scale, light-filled balloons transmitting colors from one-to-another, creating a communication network spanning over seventy miles.</p>
						
						<div class="more">
							<p>Audience members are invited to participate by sending personalized messages along the light-lined wall at a number of viewing locations or, this Web site and companion mobile app.</p>
							<p>Connecting Light investigates borders, imagining them not as a line of division, but as a source of connection.</p>
							<p>The installation is open to the public from Friday, August 31st to Saturday, September 1st.</p>
						</div>
					
					</div>
					
					<a href="" class="toggle">
						<span class="expand"><span>Learn more</span> <span>&#9662;</span></span>
						<span class="collapse"><span>Less</span> <span>&#9652;</span></span>
					</a>
				
				</div>

				<?php require_once(TEMPLATEPATH . "/incl/actions.php"); ?>
		
			</div>
		</div>

<?php

get_footer();

?>