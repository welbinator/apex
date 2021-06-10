<?php
/**
 * Maps PTB's "video" field type to "wp_editor" field in Builder
 *
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_PTBVideoPlaylist extends Tbp_Dynamic_Item {

	function is_available() {
		return function_exists( 'run_ptb' );
	}

	function get_category() {
		return 'ptb';
	}

	function get_type() {
		return array( 'wp_editor' );
	}

	function get_label() {
		return __( 'PTB Custom Fields (Video Playlist)', 'tbp' );
	}

	function get_value( $args = array() ) {
		global $wp_embed;

		$args = wp_parse_args( $args, array(
			'columns' => 1,
		) );

		if ( empty( $args['field'] ) ) {
			return '';
		}

		$value = '';
		$field_name = explode( ':', $args['field'] );
		$cf_value = get_post_meta( get_the_ID(), "ptb_{$field_name[1]}", true );
		if ( isset( $cf_value['url'] ) && is_array( $cf_value['url'] ) ) {
			$value = '<div class="ptb_extra_video ptb_extra_grid ptb_extra_columns_' . $args['columns'] . ' ptb_extra_video_preview">';

			foreach ( $cf_value['url'] as $index => $video ) {
				if ( ! $video ) {
					continue;
				}

				$title = ! empty( $cf_value['title'][ $index ] ) ? $cf_value['title'][ $index ] : '';
				$description = ! empty( $cf_value['description'][ $index ] ) ? $cf_value['description'][ $index ] : '';
				$remote = strpos( $video, 'vimeo.com') !== false || strpos( $video, 'youtu.be' ) !== false || strpos( $video, 'youtube.com') !== false;
				$value .= '<div class="ptb_extra_item ptb_extra_video_item">';
					$value .= '<h3 class="ptb_extra_video_title">' . $title .'</h3>';
					$value .= '<div class="ptb_extra_video_overlay_wrap">';
						if ( $remote ) {
							$value .= '<div class="fluid-width-video-wrapper">';
								$value .= $wp_embed->run_shortcode('[embed]' . $video . '[/embed]');
							$value .= '</div>';
						} else {
							$value .= '
							<video preload="metadata" controls="controls">
								<source src="' . $video . '#t=0.1">
							</video>';
						}
					$value .= '</div><!-- .ptb_extra_video_overlay_wrap -->';
					$value .= '<div class="ptb_extra_video_description">' . esc_html( $description ) . '</div>';
				$value .= '</div><!-- .ptb_extra_video_item -->';
			}

			$value .= '</div><!-- .ptb_extra_video -->';
		}

		return $value;
	}

	function get_options() {
		$options = array();

		/* collect "text" field types in all post types */
		$ptb = PTB::$options->get_custom_post_types();
		foreach ( $ptb as $post_type_key => $post_type ) {
			if ( is_array( $post_type->meta_boxes ) ) {
				foreach ( $post_type->meta_boxes as $key => $field ) {
					if ( $field['type'] === 'video' ) {
					    $label = PTB_Utils::get_label( $post_type->plural_label );
					    $name = PTB_Utils::get_label( $field['name'] );
					    $options[ "{$post_type_key}:{$key}" ] = sprintf( '%s: %s', $label, $name );
					}
				}
			}
		}

		return array(
			array(
				'label' => __( 'Field', 'tbp' ),
				'id' => 'field',
				'type' => 'select',
				'options' => $options,
			),
			array(
				'label' => __( 'Columns', 'tbp' ),
				'id' => 'columns',
				'type' => 'select',
				'options' => array(
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
				),
			),
		);
	}
}