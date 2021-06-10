<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_PTBAudioPlaylist extends Tbp_Dynamic_Item {

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
		return __( 'PTB Custom Fields (Audio Playlist)', 'tbp' );
	}

	function get_value( $args = array() ) {
		$value = '';
		if ( ! empty( $args['field'] ) ) {
			$field_name = explode( ':', $args['field'] );
			$cf_value = get_post_meta( get_the_ID(), "ptb_{$field_name[1]}", true );
			if ( isset( $cf_value['url'] ) && is_array( $cf_value['url'] ) ) {
			    $value = $this->make_playlist( $cf_value );
			}
		}

		return $value;
	}

	/**
	 * Create audio playlist
	 *
	 * Modified version of wp_playlist_shortcode() where it also support external audio.
	 *
	 * @return string
	 */
	function make_playlist( $value ) {
		if ( is_feed() ) {
			$output = "\n";
			foreach ( $value['url'] as $audio ) {
				$output .= $audio . "\n";
			}

			return $output;
		}

		$tracks = array();
		foreach ( $value['url'] as $i => $url ) {
			$attachment_id = attachment_url_to_postid( $url );
			$ftype = wp_check_filetype( $url, wp_get_mime_types() );
			$track = array(
				'src'         => $url,
				'type'        => $ftype['type'],
				'title'       => $value['title'][ $i ],
				'caption'     => $value['description'][ $i ],
			);

			$track['meta'] = array();
			$meta = false;
			if ( $attachment_id ) {
				$meta = wp_get_attachment_metadata( $attachment_id );
			}
			if ( ! empty( $meta ) ) {
				$attachment_post = get_post( $attachment_id );
				foreach ( wp_get_attachment_id3_keys( $attachment_post ) as $key => $label ) {
					if ( ! empty( $meta[ $key ] ) ) {
						$track['meta'][ $key ] = $meta[ $key ];
					}
				}
			}

			if ( $attachment_id ) {
				$thumb_id = get_post_thumbnail_id( $attachment_id );
				if ( ! empty( $thumb_id ) ) {
					list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'full' );
					$track['image']               = compact( 'src', 'width', 'height' );
					list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
					$track['thumb']               = compact( 'src', 'width', 'height' );
				} else {
					$src            = wp_mime_type_icon( $attachment->ID );
					$width          = 48;
					$height         = 64;
					$track['image'] = compact( 'src', 'width', 'height' );
					$track['thumb'] = compact( 'src', 'width', 'height' );
				}
			}

			$tracks[] = $track;
		}

		return Themify_Enqueue_Assets::audio_playlist( array(
			'type' => 'audio',
			'tracks' => $tracks,
		) );
	}

	function get_options() {
		$options = array();

		/* collect "text" field types in all post types */
		$ptb = PTB::$options->get_custom_post_types();
		foreach ( $ptb as $post_type_key => $post_type ) {
			if ( is_array( $post_type->meta_boxes ) ) {
				foreach ( $post_type->meta_boxes as $key => $field ) {
					if ( $field['type'] === 'audio' ) {
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
		);
	}
}