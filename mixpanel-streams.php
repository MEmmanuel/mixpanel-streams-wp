<?php
/*
Plugin Name:    Mixpanel Streams
Plugin URI:     http://github.com/mixpanel/mixpanel-streams-wp
Description:    Watch and learn about your users in real-time with Mixpanel Streams.
Version:        0.1
Author:         Mixpanel, Inc. 
Author URI:     http://mixpanel.com 
*/
$MP_DEBUG = 0;

function mpstream_debug($message) {
    global $MP_DEBUG;
    if ($MP_DEBUG && WP_DEBUG) { 
        error_log("Mixpanel Streams Debug: " . $message);      
    }
}

function mpstream_activate_plugin() {
    add_option('mpstream_token', '');
}

function mpstream_track() {
    $token = get_option('mpstream_token');
    if (!$token) {
        mpstream_debug("Aborting footer include due to missing token");
        return;
    }
    mpstream_embed_js_lib();
    mpstream_add_tracking_calls();
}

function mpstream_initialize() {
    mpstream_debug("Initializing mpq in header");
    $token = get_option('mpstream_token');
    if (!$token) {
        mpstream_debug("Aborting initialization due to missing token");
        return;
    }
    ?>
    <script type="text/javascript">
        var mpq = [];
        mpq.push(["init", "<?php echo $token; ?>"]);
    </script>
    <?php
}

function mpstream_embed_js_lib() {
    mpstream_debug("Embedding js lib");
    ?>
    <script type="text/javascript">
        (function() {
            var mp = document.createElement("script"); mp.type = "text/javascript"; mp.async = true;
            mp.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + "//api.mixpanel.com/site_media/js/api/mixpanel.js";
            var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(mp, s);
        })();
    </script>
    <?php
}

function mpstream_add_tracking_calls() {
    ?>
    <script type="text/javascript">
        mpq.push(["track_forms", "#commentform", "Add comment"]);
    </script>
    <?php
}

function mpstream_add_options_page() {
    mpstream_debug("In options page");
    add_options_page(
        'Mixpanel Analytics',
        'Mixpanel Streams',
        'manage_options',
        __FILE__,
        'mpstream_options_page_content'
    );
}
    
function mpstream_options_page_content() {
    if (isset($_POST['mpstream_update_options'])) {
        mpstream_debug('Saving posted options: ' . var_export($_POST, true));
        $options = array(
            'mpstream_token'    
        );
        foreach($options as $i=>$key) {
            if (isset($_POST[$key])) {
                update_option($key, strip_tags($_POST[$key]));
            }
        }
    }
    ?>
    <div class="wrap">
    <h2>Mixpanel Streams</h2>
    <form method="post"> 
        <table class="form-table">
            <tr valign="top">
                <th scope="row" style="width: 100px;">Project token</th>
                <td style="width: 280px">
                    <input style="width:260px; padding: 2px 10px;" type="text" name="mpstream_token" value="<?php echo get_option('mpstream_token'); ?>"/>
                </td>
                <td>
                    Your token can be found at <a href="http://mixpanel.com/projects" target="_blank">http://mixpanel.com/projects</a>.
                </td>
            </tr>

        </table>
        <p class="submit">
            <input type="hidden" name="mpstream_update_options" value="1"/>
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    </div>
    <?php
}

register_activation_hook( __FILE__, 'mpstream_activate_plugin');
add_action('wp_head', mpstream_initialize);
add_action('wp_footer', mpstream_track);
add_action('admin_menu', 'mpstream_add_options_page');
?>
