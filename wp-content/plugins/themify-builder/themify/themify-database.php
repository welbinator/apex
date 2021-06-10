<?php
/***************************************************************************
 *
 * 	----------------------------------------------------------------------
 * 						DO NOT EDIT THIS FILE
 *	----------------------------------------------------------------------
 * 
 *  				     Copyright (C) Themify
 * 
 *	----------------------------------------------------------------------
 *
 ***************************************************************************/

/* 	Database Functions
/***************************************************************************/

/**
 * Save Data
 * @param Array $data
 * @return String
 * @since 1.0.0
 * @package themify
 */
function themify_set_data( $data ) {
	if ( empty( $data ) || ! is_array( $data ) ) {
            $data = array();
	}
        else{
            unset($data['save'],$data['page']);
            foreach ( $data as $name => $value ) {
                if ($value==='' || $value==='default' || $value==='[]') {
                    unset( $data[$name] );
                }
            }
        }
	update_option( 'themify_data', $data );
	return themify_get_data(true);
}

/**
 * Return cached data
 * @return array|String
 */
function themify_get_data($reinit=false) {
    static $data=null;
    if ($data===null || $reinit!==false) {
        $data = themify_sanitize_data(get_option( 'themify_data', array() ));
        if($reinit===false){
            $data = apply_filters( 'themify_get_data', $data );
        }
    }
    return $data;
}

/**
 * Abstract away normalizing the data
 * @param $data
 * @return array
 */
function themify_sanitize_data( $data ) {
	if ( is_array( $data ) && !empty( $data )) {
		foreach( $data as $name => $value ){
			if ( in_array( $name, array( 'setting-custom_css', 'setting-header_html', 'setting-footer_html', 'setting-footer_text_left', 'setting-footer_text_right', 'setting-homepage_welcome', 'setting-store_info_address' ),true )
				|| ( false !== stripos( $name, 'setting-hooks' ) )
			) {
				$data[$name] = str_replace( "\'", "'", $value );
			} else {
				$data[$name] = stripslashes( $value );
			}
		}
		return $data;
	}
	return array();
}