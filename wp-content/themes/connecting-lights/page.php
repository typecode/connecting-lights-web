<?php

global $post;

$page_args = array(
	'numberposts'     => -1,
	'orderby'         => 'menu_order',
	'order'           => 'ASC',
	'post_type'       => 'page',
	'post_parent'     => $post->ID
);
$subpages = get_posts( $page_args );

get_header();

?>

	<div class="inverted">
		<div class="inner">
		
			<header>
				<h1><?php the_title(); ?></h1>
			</header>
			
			<?php if (have_posts()) : while (have_posts()) : the_post(); if ($post->post_content != "") : ?>
			<div class="content">
				<?php if ( has_post_thumbnail() ) : ?>
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail("full"); ?></a>
				<?php endif; ?>
				<div>
					<?php the_content(); ?>
				</div>
			</div>
			<?php endif; endwhile; endif; wp_reset_query(); ?>
			
			<div>
			<?php foreach( $subpages as $post ) : setup_postdata($post); ?>	
				
				<article>
	
					<header>
						<h1><?php the_title(); ?></h1>
					</header>
		
					<div class="content">
						<?php the_content(); ?>
					</div>
					
				</article>
			
			<?php endforeach; ?>
			<?php wp_reset_query(); ?>
			</div>
		</div>
	</div>

	<div class="xFull">
		<?php require_once(TEMPLATEPATH . "/incl/actions.php"); ?>
	</div>

<?php
get_footer();
?>