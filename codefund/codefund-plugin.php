<?php
/*
Plugin Name: CodeFund
Description: Show your ads from CodeFund on your blog.
Version: 0.1.0
Author: <a href="http://codefund.io">CodeFund</a>
*/
?>

<?php

add_action( 'wp_head', 'embed_script' );
add_action( 'wp_footer', 'replace_js');
add_action( 'loop_start', 'loop_start' );

function embed_script() {
  $options = get_option('codefund_options');
  ?>
  <script src="https://codefund.io/scripts/<?php echo $options['property_id']; ?>/embed.js"></script>
  <script>
    var $ = jQuery;
    $(function() {
      IDs = [];
      $(document).find(".codefund_additional_ad").each(function(){ IDs.push($(this).attr('id')); });
      (function serve_async(){
        setTimeout(function(){
          current_id = IDs.shift();
          _codefund.targetId = current_id;
          if(current_id != undefined){
            _codefund.serve();
            serve_async();
          }
        }, 300);
      })();
    });
  </script>

  <?php
}

function loop_start( $query )
{
    if( $query->is_main_query() )
    {
        add_filter( 'the_content', 'append_ads' );
        add_action( 'loop_end', 'end_loop' );
    }
}

function append_ads( $content )
{
    static $nr = 0;
    $options = get_option('codefund_options');
    if( $options['advertiser_text']) {
      $advertiser_text  = $options['advertiser_text'];
    }
    else {
      $advertiser_text = "Sponsor";
    }

    if($nr == 0) {
      $div = '<div><div><b>' .$advertiser_text . '</b><div><div id="codefund_ad"></div></div>';
      ++$nr;
    }
    else if (0 === ++$nr % $options['frequency']){
      $div = '<div><div><b>' .$advertiser_text . '</b></div><div id="codefund_ad_' . ($nr / $options['frequency']) .  '" class="codefund_additional_ad"></div></div>';
    }

    $content .= $div;
    return $content;
}

function end_loop()
{
    remove_action( 'the_post', 'append_ads' );
}

add_action('admin_menu', 'codefund_add_options_page');
// Add sub page to the Settings Menu
function codefund_add_options_page() {
	add_options_page('CodeFund', 'CodeFund', 'administrator', __FILE__, 'codefund_options_form');
}

function codefund_options_form() {
  ?>
    <div class="wrap">
      <div class="icon32" id="icon-options-general"><br></div>
      <h2>CodeFund</h2>
      <form action="options.php" method="post">
      <?php settings_fields('codefund_options'); ?>
      <?php do_settings_sections(__FILE__); ?>
      <p class="submit">
        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
      </p>
      </form>
    </div>
  <?php
  }
  add_action('admin_init', 'options_init' );
  // Register our settings. Add the settings section, and settings fields
  function options_init(){
    register_setting('codefund_options', 'codefund_options', 'codefund_options_validate' );
    add_settings_section('main_section', 'Main Settings', 'section_text_fn', __FILE__);
    add_settings_field('codefund_property_id', 'Property ID', 'property_id_fn', __FILE__, 'main_section');
    add_settings_field('codefund_frequency', 'Display Every X Number of Posts', 'frequency_fn', __FILE__, 'main_section');
    add_settings_field('codefund_advertiser_text', 'Advertiser Text', 'advertiser_text_fn', __FILE__, 'main_section');
  }

  function property_id_fn() {
    $options = get_option('codefund_options');
    echo "<input id='codefund_property_id' name='codefund_options[property_id]' size='40' type='text' value='{$options['property_id']}' />";
  }

  function frequency_fn() {
    $options = get_option('codefund_options');
    echo "<input id='codefund_frequency' name='codefund_options[frequency]' size='40' type='text' value='{$options['frequency']}' />";
  }

  function advertiser_text_fn() {
    $options = get_option('codefund_options');
    echo "<input id='codefund_advertiser_text' name='codefund_options[advertiser_text]' size='40' type='text' value='{$options['advertiser_text']}' />";
  }

?>