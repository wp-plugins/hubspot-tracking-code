<?php

//=============================================
// HubSpotTrackingCode Class
//=============================================
class HubSpotTrackingCode
{
    /**
     * Class constructor
     */
    function __construct ()
    {
        if ( is_admin() )
        {
            if ( ! defined('DOING_AJAX') || ! DOING_AJAX )
            {
                $hubspot_wp_admin = new HubSpotTrackingCodeAdmin();
                add_action('admin_notices', array($this, 'plugin_activation'));
            }
        }
        else
        {
        	global $hubspot_analytics;
        	$hubspot_analytics = new HubSpotTrackingCodeAnalytics();
        }
    }

    function plugin_activation ()
    {
    	$options = get_option('hs_settings');

    	if ( isset($_GET['page']) && $_GET['page'] == 'hubspot-tracking-code-admin.php' )
    		return FALSE;

    	$html = '';    	
    	if ( ! isset($options['hs_portal']) || ( isset($options['hs_portal']) && ! $options['hs_portal'] ) )
    	{	
	    	$html = '<div class="updated" style="border-color: #f47621">';
				$html .= '<p>';
					$html .= __("Almost done! <a href='options-general.php?page=hubspot-tracking-code-admin.php'>Enter your HubSpot Hub ID</a> and you'll be ready to rock.");
				$html .= '</p>';
			$html .= '</div>';
		}
		else if ( isset($options['hs_portal']) && ! is_numeric($options['hs_portal']) )
    	{
    		$html = '<div class="updated" style="border-color: #f47621">';
				$html .= '<p>';
					$html .= __("Your HubID should be a number, and it looks like the HubID you entered contains some shady characters... <a href='options-general.php?page=hubspot-tracking-code-admin.php'>Re-enter your HubSpot Hub ID</a> and you'll be ready to rock.");
				$html .= '</p>';
			$html .= '</div>';
    	}

		if ( $html )
			echo $html;
    }
}

//=============================================
// Leadin Init
//=============================================

global $hubspot_wp_admin, $hubspot_analytics;