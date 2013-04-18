<?php
/*
Plugin Name: View WP Error Log
Plugin URI: http://wordpress.org/extend/plugins/view-wp-error-log/
Description: Simply view the error log

Installation:

1) Install WordPress 3.5.2 or higher

2) Download the following file:

http://downloads.wordpress.org/plugin/view-wp-error-log.zip

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.

Version: 1.0
Author: TheOnlineHero - Tom Skroza
License: GPL2
*/

register_activation_hook( __FILE__, 'view_wp_error_log_activate' );
function view_wp_error_log_activate() {
	add_option("view_wp_error_log_no_lines", "10");
}

add_action('admin_menu', 'register_view_wp_error_log_page');
function register_view_wp_error_log_page() {
  add_menu_page('WP Error Log', 'WP Error Log', 'manage_options', 'view-wp-error-log/view-wp-error-log.php', 'view_wp_error_log_initial_page');
}

function view_wp_error_log_initial_page() { 
	if ($_POST["action"] == "Update") {
		update_option("view_wp_error_log_no_lines", $_POST["number_of_lines"]);
	}
	?>
	<div class="wrap">
  <h2>View WP Error Log</h2>  
  <div class="postbox " style="display: block; ">
  <div class="inside">
  	<form action="" method="post">
  		<table>
  			<tr>
  				<th><label for="number_of_lines">Number of lines</label></th>
  				<td>  				
	  					<select id="number_of_lines" name="number_of_lines">
	  					<option value="11" <?php if (get_option("view_wp_error_log_no_lines") == "10") {echo("selected");} ?> >10</option>
	  					<option value="21" <?php if (get_option("view_wp_error_log_no_lines") == "20") {echo("selected");} ?> >20</option>
	  					<option value="31" <?php if (get_option("view_wp_error_log_no_lines") == "30") {echo("selected");} ?> >30</option>
	  				</select>
  				</td>
  			</tr>
  		</table>
  		<p><input type="submit" name="action" value="Update"/></p>
  	</form>
    <form action="" method="post">
      <textarea cols="200" rows="40">
      	<?php 
      	echo implode("\n", view_wp_error_log_last_lines( ABSPATH."error_log", get_option("view_wp_error_log_no_lines"))); ?>
      </textarea>
    </form>
  </div>
  </div>
<?php
}

/**
 * Reads lines from end of file. Memory-safe.
 *
 * @link http://stackoverflow.com/questions/6451232/php-reading-large-files-from-end/6451391#6451391
 *
 * @param string  $path
 * @param integer $line_count
 * @param integer $block_size
 * 
 * @return array
 */
function view_wp_error_log_last_lines( $path, $line_count, $block_size = 512 ) {
	$lines = array();

	// we will always have a fragment of a non-complete line
	// keep this in here till we have our next entire line.
	$leftover = '';

	$fh = fopen( $path, 'r' );
	// go to the end of the file
	fseek( $fh, 0, SEEK_END );

	do {
		// need to know whether we can actually go back
		// $block_size bytes
		$can_read = $block_size;

		if ( ftell( $fh ) <= $block_size )
			$can_read = ftell( $fh );

		if ( empty( $can_read ) )
			break;

		// go back as many bytes as we can
		// read them to $data and then move the file pointer
		// back to where we were.
		fseek( $fh, - $can_read, SEEK_CUR );
		$data  = fread( $fh, $can_read );
		$data .= $leftover;
		fseek( $fh, - $can_read, SEEK_CUR );

		// split lines by \n. Then reverse them,
		// now the last line is most likely not a complete
		// line which is why we do not directly add it, but
		// append it to the data read the next time.
		$split_data = array_reverse( explode( "\n", $data ) );
		$new_lines  = array_slice( $split_data, 0, - 1 );
		$lines      = array_merge( $lines, $new_lines );
		$leftover   = $split_data[count( $split_data ) - 1];
	} while ( count( $lines ) < $line_count && ftell( $fh ) != 0 );

	if ( ftell( $fh ) == 0 )
		$lines[] = $leftover;

	fclose( $fh );
	// Usually, we will read too many lines, correct that here.
	return array_slice( $lines, 0, $line_count );
}
?>