<?php

/*
Once Upon A Time is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Once Upon A Time is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Once Upon A Time. If not, see https://www.gnu.org/licenses/gpl-3.0.en.html.
*/

/**
 * Plugin Name:       Once Upon A Time
 * Description:
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Marta Czuczman
 * Author URI:        https://panimarta.pl
 * Text Domain:       once-upon-a-time
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
  die;
}
/*
 * Enqueue plugin CSS and JavaScript files.
 */
function once_upon_a_time_enqueue() {
  $plugin_url = plugin_dir_url( __FILE__ );
  wp_enqueue_style( 'style', $plugin_url . 'style.css', null, '1.0.0' );
  wp_enqueue_script( 'script', $plugin_url . 'script.js', null, '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'once_upon_a_time_enqueue' );

/**
 * Load plugin textdomain.
 */
function once_upon_a_time_load_plugin_textdomain() {
   load_plugin_textdomain( 'once-upon-a-time', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'once_upon_a_time_load_plugin_textdomain' );

function once_upon_a_time_register_post_type() {
  $labels = array(
    'name'          => __( 'Events', 'once-upon-a-time' ),
    'singular_name' => __( 'Event', 'once-upon-a-time' ),
    'add_new_item'  => __( 'Add New Event', 'once-upon-a-time' ),
    'menu_name'     => __('Events', 'once-upon-a-time'),
    'add_new'       => __('Add New', 'once-upon-a-time'),
    'all_items'     => __('All Events', 'once-upon-a-time'),
  );

  $args = array(
    'labels'       => $labels,
    'public'       => true,
    'hierarchical' => true,
    'has_archive'  => true,
    'supports'     => array('title', 'editor', 'excerpt', 'thumbnail'),
    'show_in_rest' => true,
    'rewrite'      => array( 'slug' => __('event', 'once-upon-a-time')),
    'with_front'   => true,
  );

  register_post_type( 'event', $args );
}
add_action( 'init', 'once_upon_a_time_register_post_type' );

/**
 * Activate the plugin.
 * Register custom post type.
 */
function once_upon_a_time_activate() {
  once_upon_a_time_register_post_type();
  flush_rewrite_rules();
  once_upon_a_time_enqueue();
}
register_activation_hook( __FILE__, 'once_upon_a_time_activate' );

/**
 * Deactivate the plugin.
 * Unregister custom post type.
 */
function once_upon_a_time_deactivate() {
  unregister_post_type( 'event' );
  flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'once_upon_a_time_deactivate' );

/*
 * Add meta boxes, so that the user can fill in event date and address.
 */
function once_upon_a_time_add_meta_box() {
  add_meta_box(
    'event_meta_box', // $id
    __( 'Event info', 'once-upon-a-time' ), // $title
    'once_upon_a_time_show_meta_box', // $callback
    'event', // $screen
    'normal', // $context
    'high' // $priority
  );
}
add_action( 'add_meta_boxes', 'once_upon_a_time_add_meta_box' );

function once_upon_a_time_get_meta_selected( $name, $x ) {
  global $post;
  $meta = get_post_meta( $post->ID, 'event_fields', true );
  if ( is_array( $meta ) && isset( $meta[$name] ) ) {
    return selected( $meta[$name], $x );
  } else {
    return '';
  }
}

function once_upon_a_time_get_meta_body( $name ) {
  global $post;
  $meta = get_post_meta( $post->ID, 'event_fields', true );
  if ( is_array( $meta ) && isset( $meta[$name] ) ) {
    return $meta[$name];
  } else {
    return '';
  }
}

function once_upon_a_time_get_date_range( ) {
  global $post;
  $months = array(__('January', 'once-upon-a-time'), __('February', 'once-upon-a-time'), __('March', 'once-upon-a-time'), __('April', 'once-upon-a-time'), __('May', 'once-upon-a-time'), __('June', 'once-upon-a-time'), __('July', 'once-upon-a-time'), __('August', 'once-upon-a-time'), __('September', 'once-upon-a-time'), __('October', 'once-upon-a-time'), __('November', 'once-upon-a-time'), __('December', 'once-upon-a-time'));

  $start_date_timestamp = get_post_meta( $post->ID, 'event_start_date_timestamp', true );
  $start_date = new DateTime(); $start_date->setTimestamp($start_date_timestamp);

  /* end date is by default set to start date. */
  $end_date_timestamp = get_post_meta( $post->ID, 'event_end_date_timestamp', true );
  $end_date = new DateTime(); $end_date->setTimestamp($end_date_timestamp);

  $start_day = once_upon_a_time_get_meta_body( 'start_day' );
  $start_month = once_upon_a_time_get_meta_body( 'start_month' );
  $start_year = once_upon_a_time_get_meta_body( 'start_year' );

  $date_range = '';
  if ( $start_date_timestamp === $end_date_timestamp ) {
    $date_range = $start_day . ' ' . $months[$start_month - 1] . ' ' . $start_year;
  } else {
    $end_day = once_upon_a_time_get_meta_body( 'end_day' );
    $end_month = once_upon_a_time_get_meta_body( 'end_month' );
    $end_year = once_upon_a_time_get_meta_body( 'end_year' );

    if ( $start_year === $end_year ) {
      if ( $start_month === $end_month ) {
        $date_range = $start_day . ' &ndash; ' . $end_day . ' ' . $months[$start_month - 1] . ' ' . $start_year;
      } else {
        $date_range = $start_day . ' ' . $months[$start_month - 1] . ' &ndash; ' . $end_day . ' ' . $months[$end_month - 1] . ' ' . $start_year;
      }
    } else {
      $date_range = $start_day . ' ' . $months[$start_month - 1] . ' ' . $start_year . ' &ndash; ' . $end_day . ' ' . $months[$end_month - 1] . ' ' . $end_year;
    }
  }
  return $date_range;
}

function once_upon_a_time_show_meta_box() {
  global $post;
  $meta = get_post_meta( $post->ID, 'event_fields', true ); ?>

  <input type="hidden" name="event_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

  <?php
  $days = range(1, 31, 1);
  $months = array(__('January', 'once-upon-a-time'), __('February', 'once-upon-a-time'), __('March', 'once-upon-a-time'), __('April', 'once-upon-a-time'), __('May', 'once-upon-a-time'), __('June', 'once-upon-a-time'), __('July', 'once-upon-a-time'), __('August', 'once-upon-a-time'), __('September', 'once-upon-a-time'), __('October', 'once-upon-a-time'), __('November', 'once-upon-a-time'), __('December', 'once-upon-a-time'));
  $years = range( date('Y'), date('Y') + 5, 1);
  ?>

  <p>
  <h3><?php esc_html_e("Start of the Event", 'once-upon-a-time'); ?></h3>
  <label for="event_fields[start_day]"><?php esc_html_e("Day", 'once-upon-a-time'); ?>:</label>
  <select name="event_fields[start_day]" id="event_fields[start_day]">
  <option value="" selected disabled hidden><?php esc_html_e("Choose", 'once-upon-a-time'); ?></option>
  <?php foreach ($days as $day) {
  echo '<option value="' . $day . '"' . once_upon_a_time_get_meta_selected( 'start_day', $day ) . '>' . $day . '</option>';
  } ?>
  </select>

  <label for="event_fields[start_month]"><?php esc_html_e("Month", 'once-upon-a-time'); ?>:</label>
  <select name="event_fields[start_month]" id="event_fields[start_month]">
  <option value="" selected disabled hidden><?php esc_html_e("Choose", 'once-upon-a-time'); ?></option>
  <?php $month_number = 1; /* month number starting with 1 */
  foreach ($months as $month) {
  echo '<option value="' . $month_number . '"' . once_upon_a_time_get_meta_selected( 'start_month', $month_number ) . '>' . $month . '</option>';
  $month_number++;
  } ?>
  </select>

  <label for="event_fields[start_year]"><?php esc_html_e("Year", 'once-upon-a-time'); ?>:</label>
  <select name="event_fields[start_year]" id="event_fields[start_year]">
  <option value="" selected disabled hidden><?php esc_html_e("Choose", 'once-upon-a-time'); ?></option>
  <?php foreach ($years as $year) {
  echo '<option value="' . $year . '"' . once_upon_a_time_get_meta_selected( 'start_year', $year ) . '>' . $year . '</option>';
  } ?>
  </select>
  </p>

  <p>
  <h3><?php esc_html_e("End of the Event (optional)", 'once-upon-a-time'); ?></h3>
  <label for="event_fields[end_day]"><?php esc_html_e("Day", 'once-upon-a-time'); ?>:</label>
  <select name="event_fields[end_day]" id="event_fields[end_day]">
  <option value="" selected disabled hidden><?php esc_html_e("Choose", 'once-upon-a-time'); ?></option>
  <?php foreach ($days as $day) {
  echo '<option value="' . $day . '"' . once_upon_a_time_get_meta_selected( 'end_day', $day ) . '>' . $day . '</option>';
  } ?>
  </select>

  <label for="event_fields[end_month]"><?php esc_html_e("Month", 'once-upon-a-time'); ?>:</label>
  <select name="event_fields[end_month]" id="event_fields[end_month]">
  <option value="" selected disabled hidden><?php esc_html_e("Choose", 'once-upon-a-time'); ?></option>
  <?php $month_number = 1; /* month number starting with 1 */
  foreach ($months as $month) {
  echo '<option value="' . $month_number . '"' . once_upon_a_time_get_meta_selected( 'end_month', $month_number ) . '>' . $month . '</option>';
  $month_number++;
  } ?>
  </select>

  <label for="event_fields[end_year]"><?php esc_html_e("Year", 'once-upon-a-time'); ?>:</label>
  <select name="event_fields[end_year]" id="event_fields[end_year]">
  <option value="" selected disabled hidden><?php esc_html_e("Choose", 'once-upon-a-time'); ?></option>
  <?php foreach ($years as $year) {
  echo '<option value="' . $year . '"' . once_upon_a_time_get_meta_selected( 'end_year', $year ) . '>' . $year . '</option>';
  } ?>
  </select>
  </p>

  <p>
  <h3><?php esc_html_e("Event Address", 'once-upon-a-time'); ?></h3>
  <label class="mr-3" for="event_fields[address]"><?php esc_html_e("Address", 'once-upon-a-time'); ?>:</label>
  <input type="text" name="event_fields[address]" id="event_fields[address]" class="regular-text" value="<?php echo once_upon_a_time_get_meta_body('address'); ?>">
  </p>
<?php }

function once_upon_a_time_save_meta( $post_id ) {
  // verify nonce
  if ( !isset($_POST['event_meta_box_nonce']) or !wp_verify_nonce( $_POST['event_meta_box_nonce'], basename(__FILE__) ) ) {
    return $post_id;
  }
  // check autosave
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return $post_id;
  }
  // check permissions
  if ( 'page' === $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) ) {
      return $post_id;
    } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
      return $post_id;
    }
  }

  $old = get_post_meta( $post_id, 'event_fields', true );
  $new = $_POST['event_fields'];

  if ( $new ) {
    $start_date = $new['start_year'] . '-' . $new['start_month'] . '-' . $new['start_day'];
    $start_date_timestamp = (new DateTime($start_date))->getTimestamp();
    update_post_meta($post_id, 'event_start_date_timestamp', $start_date_timestamp);

    /* If end date was set, put it into database. If not, copy start date into end date. */
    if ( isset($new['end_year']) && isset($new['end_month']) && isset($new['end_day']) ) {
      $end_date = $new['end_year'] . '-' . $new['end_month'] . '-' . $new['end_day'];
      $end_date_timestamp = (new DateTime($end_date))->getTimestamp();
      update_post_meta($post_id, 'event_end_date_timestamp', $end_date_timestamp);
    } else {
      update_post_meta($post_id, 'event_end_date_timestamp', $start_date_timestamp);
    }
  }

  if ( $new && $new !== $old ) {
    update_post_meta( $post_id, 'event_fields', $new );
  } elseif ( '' === $new && $old ) {
    delete_post_meta( $post_id, 'event_fields', $old );
  }
}
add_action( 'save_post', 'once_upon_a_time_save_meta' );

function once_upon_a_time_get_events($events, $future, $title = '') {
  $events_string = '';
  if ( $events->have_posts() ) :
    $events_string .=
    '<div class="once-upon-a-time-' . ($future ? 'upcoming' : 'past') . '-events">' .
    '<h2>' . $title . '</h2>' .
    '<div id="once-upon-a-time-read-more-text" style="display: none;">' . __('Read more', 'once-upon-a-time') . '</div>' .
    '<div id="once-upon-a-time-read-less-text" style="display: none;">' . __('Read less', 'once-upon-a-time') . '</div>';
    while ( $events->have_posts() ) :
      $events->the_post();
      $upcoming_event_address = '';
      $meta = get_post_meta( get_the_ID(), 'event_fields', true );
      if ( is_array( $meta ) && ($meta['address'] != '') ) {
        $upcoming_event_address = $meta['address'];
      }
      $events_string .=
      '<div class="once-upon-a-time">';
      if ( has_post_thumbnail(get_the_ID()) ) {
        $events_string .=
        '<img alt="' . get_the_title() . '" src="' . get_the_post_thumbnail_url(get_the_ID(), 'medium') . '"' . '>';
      }
      $events_string .=
      '<div class="once-upon-a-time-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>' .
      '<div class="once-upon-a-time-date-range">' . once_upon_a_time_get_date_range( ) . '</div>' .
      '<div class="once-upon-a-time-address">' . $upcoming_event_address . '</div>';
      if ( has_excerpt() ) {
        $events_string .= '<div class="once-upon-a-time-excerpt">' . get_the_excerpt() . '</div>';
      }
      $events_string .= '<div class="once-upon-a-time-read-more">' . __('Read more', 'once-upon-a-time') . '</div>';
      $events_string .= '<div class="once-upon-a-time-description">' . get_the_content() . '</div>';
      $events_string .= '</div>';
    endwhile;
    $events_string .= '</div>';
  endif;
  return $events_string;
}

function once_upon_a_time_get_past_events( $title ) {
  $args = array(
    'post_type'   => 'event',
    'post_status' => 'publish',
    'orderby'     => 'meta_value_num',
    'meta_key'    => 'event_start_date_timestamp',
    'order'       => 'DESC',
    'posts_per_page' => -1,
    'meta_query'  => array(
      array(
        'key'     => 'event_end_date_timestamp',
        'value'   => strtotime('today midnight'),
        'compare' => '<',
      ),
    ),
  );

  $events = new WP_Query( $args );
  $past_events = once_upon_a_time_get_events($events, false, $title);
  wp_reset_postdata();
  return $past_events;
}

function once_upon_a_time_get_upcoming_events( $title ) {
  $args = array(
    'post_type'   => 'event',
    'post_status' => 'publish',
    'orderby'     => 'meta_value_num',
    'meta_key'    => 'event_start_date_timestamp',
    'order'       => 'ASC',
    'meta_query'  => array(
      array(
        'key'     => 'event_end_date_timestamp',
        'value'   => strtotime('today midnight'),
        'compare' => '>=',
      ),
    ),
  );

  $events = new WP_Query( $args );
  $upcoming_events = once_upon_a_time_get_events($events, true, $title);
  wp_reset_postdata();
  return $upcoming_events;
}

/*
 * [once_upon_a_time_past_events title=""] shortcode lists all past events.
 */
function once_upon_a_time_get_past_events_shortcode( $atts ) {
  $a = shortcode_atts( array(
		'title' => '',
	), $atts );

  return once_upon_a_time_get_past_events( $a['title'] );
}
add_shortcode('once_upon_a_time_past_events', 'once_upon_a_time_get_past_events_shortcode');

/*
 * [once_upon_a_time_upcoming_events title=""] shortcode lists all events which take place
 * in the future.
 */
function once_upon_a_time_get_upcoming_events_shortcode( $atts ) {
  $a = shortcode_atts( array(
		'title' => '',
	), $atts );
  return once_upon_a_time_get_upcoming_events( $a['title'] );
}
add_shortcode('once_upon_a_time_upcoming_events', 'once_upon_a_time_get_upcoming_events_shortcode');

?>
