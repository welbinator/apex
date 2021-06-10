<?php /* Template Name: Contact Page */ ?>
<?php echo get_header("apex"); ?>

<div class="section">
        <div class="container hero-container">
            <div class="row">
                <div class="col-12 col-md-6 mx-auto text-center" style="width: 675.5px;">
                    <h1 class="hero-header">Contact Us</h1>
                    <?php echo do_shortcode("[formidable id=2]"); ?>
                </div>
            </div>
        </div>
    </div>


<?php echo get_footer("apex"); ?>