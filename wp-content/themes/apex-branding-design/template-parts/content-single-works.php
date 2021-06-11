<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Apex_Branding_&_Design
 */

?>
<div class="hero work-single"><img src="<?php the_field('hero_image_single'); ?>">
    <div class="container hero-container">
        <div class="row">
            <div class="col-12 col-md-6 mx-auto">
                <?php

                the_title('<h1 class="hero-header text-center">', '</h1>');

                ?>

                <?php the_field('about_client'); ?>
            </div>
        </div>
    </div>
    <div class="hero-overlay"></div>
</div>
<div class="section stats">
    <div class="container">
        <h6 class="stats">#1</h6>
        <h6 class="stats"><?php the_field('project_type'); ?></h6>
        <h6 class="stats"><?php the_field('year'); ?></h6>
    </div>
</div>
<div class="section">
    <div class="container">
        <?php
        the_content(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'apex-branding-design'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            )
        );


        ?>
    </div>
</div>