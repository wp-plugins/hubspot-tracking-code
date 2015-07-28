<?php

if ( !defined('HUBSPOT_TRACKING_CODE_PLUGIN_VERSION') ) 
{
    header('HTTP/1.0 403 Forbidden');
    die;
}

/**
 * Updates an option in the multi-dimensional option array
 *
 * @param   string   $option        option_name in wp_options
 * @param   string   $option_key    key for array
 * @param   string   $option        new value for array
 *
 * @return  bool            True if option value has changed, false if not or if update failed.
 */
function hubspot_tracking_code_update_option ( $option, $option_key, $new_value ) 
{
    $options_array = get_option($option);

    if ( isset($options_array[$option_key]) )
    {
        if ( $options_array[$option_key] == $new_value )
            return false; // Don't update an option if it already is set to the value
    }

    if ( ! is_array( $options_array ) ) {
        $options_array = array();
    }

    $options_array[$option_key] = $new_value;
    update_option($option, $options_array);

    $options_array = get_option($option);
    return update_option($option, $options_array);
}

/**
 * Logs a debug statement to /wp-content/debug.log
 *
 * @param   string
 */
function hubspot_log_debug ( $message )
{
    if ( WP_DEBUG === TRUE )
    {
        if ( is_array($message) || is_object($message) )
            error_log(print_r($message, TRUE));
        else 
            error_log($message);
    }
}

?>