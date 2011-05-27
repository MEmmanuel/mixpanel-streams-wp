<?php
/*
Plugin Name:    Mixpanel Streams
Plugin URI:     http://mixpanel.com 
Description:    View your visitor activity stream in real-time with Mixpanel Streams 
Version:        0.1
Author:         Mixpanel, Inc. 
Author URI:     http://mixpanel.com 
*/
$MP_DEBUG = 1;

function mpstream_debug($message) {
    
    global $MP_DEBUG;
    if ($MP_DEBUG && WP_DEBUG) { 
        error_log($message);      
    }
}

function mpstream_activate_plugin() {
    add_option('mpstream_id','');
    add_option('mpstream_enabled','0');
}

function mpstream_deactivate_plugin() {
    remove_option('mpstream_id');
    remove_option('mpstream_enabled');
}

function mpstream_track() {
    mpstream_debug("in track");
    if (get_option('mpstream_enabled') != 1) {
        return;
    }
    mpstream_debug("before token");
    $token = get_option('mpstream_id');
    mpstream_debug("Token:" . $token);
    if (!$token) {
        return;
    }
    mpstream_debug($token);
    mpstream_embed_js_lib($token);
    mpstream_add_tracking_calls();
}

function mpstream_embed_js_lib($token) {
    mpstream_debug("Embedding js lib");
    ?>
    <script type="text/javascript">
        var mpq = [];
        mpq.push(["init", "<?php echo $token; ?>"]);
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
        mpq.push(["track_pageview"]);
        mpq.push(["track", "Wordpress pageview"]);
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
            'mpstream_enabled',
            'mpstream_token'    
        )
        foreach($options as $i=>$key) {
            if (isset($_POST[$key]) {
                update_option($key, strip_tags($_POST[$key]));
            }
        }
    }
    ?>
    <div class="wrap">
    <h2>Mixpanel Streams</h2>
    <form method="post" action="options-general.php?page=<?php echo $_GET['page']; ?>">
    <!-- <form method="post"> -->

    <?php wp_nonce_field('update-options'); ?>
    <table class="form-table">

    <tr valign="top">
    <th scope="row">Status</th>
    <td><select name="mpstream_enabled"> 
      <?php
      $variants=array('1'=>'Enabled','0'=>'Disabled');
      foreach($variants as $value=>$text) {
         echo '<option value="',$value,'"';
         if(get_option('mpstream_enabled')==$value) {
            echo ' selected';
         }
         echo '>',$text,'</option>';
      }
      ?>
    </select></td>
    </tr>

    <tr valign="top">
    <th scope="row">Tracking ID</th>
    <td><input style="width:280px; text-align:center;" type="text" name="mpstream_id" value="<?php echo get_option('mpstream_id'); ?>"/></td>
    </tr>

    <tr valign="top">
    <th scope="row"> Include logged in users in statistics</th>
    <td><select name="mpstream_loggedinlogging"> 
      <?php
      $variants=array('1'=>'Yes','0'=>'No');
      foreach($variants as $value=>$text) {
         echo '<option value="',$value,'"';
         if(get_option('mpstream_loggedinlogging')==$value) {
            echo ' selected';
         }
         echo '>',$text,'</option>';
      }
      ?>
    </select></td>
    </tr>


    <tr valign="top">
    <th>Track...</th><td></td>
    </tr>

    <tr valign="top">
    <th scope="row"> - hits (page views)</th>
    <td><select name="mpstream_track_pageviews"> 
      <?php
      $variants=array('1'=>'Yes','0'=>'No');
      foreach($variants as $value=>$text) {
         echo '<option value="',$value,'"';
         if(get_option('mpstream_track_pageviews')==$value) {
            echo ' selected';
         }
         echo '>',$text,'</option>';
      }
      ?>
    </select></td>
    </tr>

    <tr valign="top">
    <th scope="row"> - search engine visitors</th>
    <td><select name="mpstream_track_search_engines"> 
      <?php
      $variants=array('1'=>'Yes','0'=>'No');
      foreach($variants as $value=>$text) {
         echo '<option value="',$value,'"';
         if(get_option('mpstream_track_search_engines')==$value) {
            echo ' selected';
         }
         echo '>',$text,'</option>';
      }
      ?>
    </select></td>
    </tr>

    <tr valign="top">
    <th scope="row"> - browsers</th>
    <td><select name="mpstream_track_browsers"> 
      <?php
      $variants=array('1'=>'Yes','0'=>'No');
      foreach($variants as $value=>$text) {
         echo '<option value="',$value,'"';
         if(get_option('mpstream_track_browsers')==$value) {
            echo ' selected';
         }
         echo '>',$text,'</option>';
      }
      ?>
    </select></td>
    </tr>

    <tr valign="top">
    <th scope="row"> - operating system</th>
    <td><select name="mpstream_track_os"> 
      <?php
      $variants=array('1'=>'Yes','0'=>'No');
      foreach($variants as $value=>$text) {
         echo '<option value="',$value,'"';
         if(get_option('mpstream_track_browsers')==$value) {
            echo ' selected';
         }
         echo '>',$text,'</option>';
      }
      ?>
    </select></td>
    </tr>

    </table>
    <p class="submit">
    <input type="hidden" name="mpstreamupdateoptions" value="1"/>
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
    </form></div><?php
}

register_activation_hook( __FILE__, 'mpstream_activate_plugin');
register_deactivation_hook( __FILE__, 'mpstream_deactivate_plugin');
add_action('wp_footer', mpstream_track);
add_action('admin_menu', 'mpstream_add_options_page');

