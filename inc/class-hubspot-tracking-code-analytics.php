<?php
class HubSpotTrackingCodeAnalytics 
{
    function HubSpotTrackingCodeAnalytics () 
    {
        add_action('wp_footer', array(&$this, 'hubspot_analytics_insert'));
    }

    //=============================================
    // Insert tracking code
    //=============================================
    function hubspot_analytics_insert ()
    {
        global $current_user;
        wp_reset_query();

        get_currentuserinfo();
        $options = array();
        $options = get_option('hs_settings');

        if ( isset($options['hs_portal']) && $options['hs_portal'] != '' )
        {
            // Identify the current user if logged in
            if ( $current_user->user_email ) 
            {
                $hs_identify_name 	= $current_user->user_login;
                $hs_identify_email 	= $current_user->user_email;
                $hs_identify_id 	= md5($current_user->user_email);
            } 
            else 
            {
                $commenter = wp_get_current_commenter();
                if ( $commenter['comment_author_email'] ) 
                {
                    $hs_identify_name 	= $commenter['comment_author'];
                    $hs_identify_email 	= $commenter['comment_author_email'];
                    $hs_identify_id 	= md5($commenter['comment_author_email']);
                }
            }

            if ( isset($hs_identify_email) ) 
            {
                echo "\n" . '<!-- DO NOT COPY THIS SNIPPET! -- HubSpot User Identification Code -->' . "\n";
                echo '<script type="text/javascript">'."\n";
                echo "(function(d,w) {\n";
                // Wrap `identify` call in hubspotutk check to help prevent accidental copy-paste
                if ( isset($_COOKIE['hubspotutk']) ) 
                {
                    echo "var match = d.cookie.match('(^|;) ?hubspotutk=([^;]*)(;|$)');\n";
                    echo "if (match && match[2] == \"" . $_COOKIE['hubspotutk'] . "\") {\n";
                }
                echo "w._hsq = w._hsq || [];\n";
                echo "w._hsq.push([\"identify\", {\n";
                echo "  \"email\" : \"" . $hs_identify_email . "\",\n";
                echo "  \"name\" : \"" . $hs_identify_name . "\",\n";
                echo "  \"id\" : \"" . $hs_identify_id . "\"\n";
                echo "}]);\n";
                if ( isset($_COOKIE['hubspotutk']) ) 
                {
                    echo "}\n";
                }
                echo "})(document, window);\n";
                echo '</script>' . "\n";
                echo '<!-- End of HubSpot User Identification Code -- DO NOT COPY THIS SNIPPET! -->' . "\n";
            }

            echo "\n".'<!-- Start of Async HubSpot Analytics Code for WordPress v' . HUBSPOT_TRACKING_CODE_PLUGIN_VERSION . ' -->' . "\n";
            echo '<script type="text/javascript">' . "\n";
            echo 'var _hsq = _hsq || [];' . "\n";
            // Pass along the correct content-type
            if ( is_single () ) 
            {
                echo '_hsq.push(["setContentType", "blog-post"]);' . "\n";  
            } 
            else if ( is_archive () || is_search() )
            {
                echo '_hsq.push(["setContentType", "listing-page"]);' . "\n";
            } 
            else 
            {
                echo '_hsq.push(["setContentType", "standard-page"]);' . "\n";
            }

            echo '(function(d,s,i,r) {' . "\n";
            echo '  if (d.getElementById(i)){return;}' . "\n";
            echo '  var n = d.createElement(s),e = document.getElementsByTagName(s)[0];' . "\n";
            echo '  n.id=i;n.src = \'//js.hs-analytics.net/analytics/\'+(Math.ceil(new Date()/r)*r)+\'/' . trim($options['hs_portal']) . '.js\';' . "\n";
            echo '  e.parentNode.insertBefore(n, e);' . "\n";
            echo '})(document, "script", "hs-analytics", 300000);' . "\n";
            echo '</script>' . "\n";
            echo '<!-- End of Async HubSpot Analytics Code -->' . "\n";
        }
    }
}
?>
