<?php /* Template Name: Home Page */ ?>
<?php echo get_header("apex"); ?>


<div class="hero"><img class="hero-image" src="<?php echo get_template_directory_uri() ?>/assets/img/Apex-outline.svg">
        <div class="container hero-container">
            <div class="row">
                <div class="col-md-6 col-sm-12 d-flex flex-column justify-content-center">
                    <h1 class="hero-header">We help businesses elevate their brand through the power of strategy, design and creativity.</h1>
					 <div class="hero-tagline">
                        <p class="mb-3">Has your product or service outgrown your brand?</p> 
                        <p class="mb-3">It's ok, it happens! Luckily, we know how to fix it!</p>
                        <p>Check out Angie's rebrand story <i class="fas fa-arrow-right shake-horizontal ms-3"></i> <i class="fas fa-arrow-down shake-vertical ms-3"></i></p>
                     </div>
				
					<!-- <a href="/contact"><button class="btn btn-primary hero-btn">Get Started</button></a> -->
                    <!-- <button class="btn btn-secondary hero-btn" id="show-video"><i class="far fa-play-circle"></i> Watch Video</button> -->
               
                </div>
                <div class="col-md-6 col-sm-12 flex-column" id="home-case-stud"><iframe id="angie-video" type="text/html" width="640" height="360" src="https://www.youtube-nocookie.com/embed/H5-HaRzq4Wc?modestbranding=1" frameborder="0"></iframe></div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="container">
            <h1 class="text-center">Recent Work</h1>
        </div>
    </div>
    <div class="section no-gutter">
        <div class="container-fluid">
            <div class="row g-0">

                    <?php
                        $args = array(
                        'post_type' => 'works',
                        'posts_per_page' => 3
                        );
                        $the_query = new WP_Query( $args ); 
                    ?>
                    <?php if ( $the_query->have_posts() ) : ?>
                    <?php while ( 
                        $the_query->have_posts() ) : $the_query->the_post(); 
                    ?>

                        <div class="col-12 col-lg-4 client-container"><img class="client-background-image" src="<?php the_field('thumbnail'); ?>">
                            <div class="client-background-image-overlay"></div>
							
                                <?php if( get_field('url') ): ?>
                                <a target="_blank" href="<?php the_field('url'); ?>" >
                                <?php endif; ?>
                            
                            
                            <img class="img-fluid client-logo" src="<?php the_field('logo'); ?>"> 
                            <?php if( get_field('url') ): ?>
                                </a>
                                <?php endif; ?>

                        </div>
                    <?php endwhile; ?>
                    <?php // wp_reset_postdata(); ?>
                    <?php endif; ?>


               
                </div>
        </div>
    </div>
    <div class="section">
        <div class="container text-center"><a class="btn btn-primary" role="button" href="/works">Case Studies</a></div>
    </div>
    <div class="section">
        <div class="container">
            <h1 class="text-center">The Team</h1>
        </div>
    </div>
    <div class="section team no-gutter">
        <div class="container">
           
            <div class="row">
            <?php
                            $args = array(
                            'post_type' => 'team_member',
                            'posts_per_page' => 3
                            );
                            $the_query = new WP_Query( $args ); 
                        ?>

                        <?php if ( $the_query->have_posts() ) : ?>

                        <?php while ( 
                            $the_query->have_posts() ) : $the_query->the_post(); 
                        ?>

                        <div class="col-12 col-md-4 mb-4 mb-lg-0"><img class="img-fluid founder-image" src="<?php the_field('thumbnail'); ?>">

                            <?php the_title('<h3 class="team-header">', '</h1>'); ?>

                            <h5 class="team-header subheader">  <?php the_field('team_member_title'); ?></h5>
                        </div>
                        <?php endwhile; ?>
                        <?php // wp_reset_postdata(); ?>
                        <?php endif; ?>
            </div>
        </div>
    </div>


<?php echo get_footer("apex"); ?>