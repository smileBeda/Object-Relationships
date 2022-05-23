<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Plugin Name: Object Relationships
 * Plugin URI: https://tryfile.com
 * Description: Enables relationships between objects to be stored in a dedicated database table
 * Version: 0.1.0
 * Author: Tim Kaye
 * Author URI: https://timkaye.org
*/

/* INCLUDE REQUIRED FILES */
require_once __DIR__ . '/inc/database.php'; // creates custom database table


/* FIRE HOOK FOR DATABASE CREATION */
register_activation_hook( __FILE__, 'kts_object_relationships_create_db' );


/* SPECIFY OBJECTS WHOSE RELATIONSHIPS WILL BE STORED IN THIS TABLE */
 function kts_recognized_relationship_objects() {

	# Names of core objects
	 $objects = array(
		'comment',
		'post',
		'taxonomy',
		'user'
	);

	# Get names of taxonomies and add them to $objects array
	$taxonomies = 	get_taxonomies();
	foreach( $taxonomies as $taxonomy ) {
		$objects[] = $taxonomy;
	}

	# Add filter to allow users to modify list of recognized relationship objects
	return apply_filters( 'recognized_relationship_objects', $objects );
}


/* ADD BI-DIRECTIONAL RELATIONSHIP BETWEEN TWO OBJECTS */
function kts_add_object_relationship( $left_object_id, $left_object_type, $right_object_type, $right_object_id ) {

	# Error if $left_object_id is not a positive integer
	if ( filter_var( $left_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( $left_object_id === 0 ) {
			$item = 0;
		}
		elseif ( is_object( $left_object_id ) ) {
			$item = 'object';
		}
		elseif ( is_array( $left_object_id ) ) {
			$item = 'array';
		}

		return new WP_Error( 'left_object_id', 'kts_add_object_relationship() expects parameter 1 to be a positive integer, ' . $item . ' given.' );
	}

	# Error if $right_object_id is not a positive integer
	if ( filter_var( $right_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( $right_object_id === 0 ) {
			$item = 0;
		}
		elseif ( is_object( $right_object_id ) ) {
			$item = 'object';
		}
		elseif ( is_array( $right_object_id ) ) {
			$item = 'array';
		}

		return new WP_Error( 'right_object_id', 'kts_add_object_relationship() expects parameter 4 to be a positive integer, ' . $item . ' given.' );
	}

	$recognized_relationship_objects = kts_recognized_relationship_objects();

	# Error if $left_object_type is not a non-null string of an appropriate value
	if ( ! in_array( $left_object_type, $recognized_relationship_objects ) ) {

		$item = $left_object_type;
		if ( is_int( $left_object_type ) ) {
			$item = 'integer';
		}
		elseif ( is_object( $left_object_type ) ) {
			$item = 'object';
		}
		elseif ( is_array( $left_object_type ) ) {
			$item = 'array';
		}

		$object_list = implode( ', ', $recognized_relationship_objects );

		return new WP_Error( 'left_object_type', 'kts_add_object_relationship() expects parameter 2 to be one of ' . $object_list . ', ' .$item . ' given.' );
	}

	# Error if $right_object_type is not a non-null string of an appropriate value
	if ( ! in_array( $right_object_type, $recognized_relationship_objects ) ) {

		$item = $right_object_type;
		if ( is_int( $right_object_type ) ) {
			$item = 'integer';
		}
		elseif ( is_object( $right_object_type ) ) {
			$item = 'object';
		}
		elseif ( is_array( $right_object_type ) ) {
			$item = 'array';
		}

		$object_list = implode( ', ', $recognized_relationship_objects );

		return new WP_Error( 'right_object_type', 'kts_add_object_relationship() expects parameter 3 to be one of ' . $object_list . ', ' .$item . ' given.' );
	}

	# Good to go!
	global $wpdb;
	$table_name = $wpdb->prefix . 'kts_object_relationships';

	$relationship_array = array(
		'left_object_id'		=> $left_object_id,
		'left_object_type'	=> $left_object_type,
		'right_object_type'=> $right_object_type,
		'right_object_id'	=> $right_object_id
	);

	# Check if this relationship already exists
	$sql1 = $wpdb->prepare( "SELECT * FROM $table_name WHERE left_object_id = %d AND left_object_type = %s AND right_object_type = %s AND right_object_id = %d", $left_object_id, $left_object_type, $right_object_type, $right_object_id );

	# If so, return the relationship ID as an integer
	if ( ! empty( $sql1 ) ) {
		$row = $wpdb->get_row( $sql1 );
		return (int) $row->relationship_id;
	}

	# Also query database table right to left
	$sql2 = $wpdb->prepare( "SELECT * FROM $table_name WHERE right_object_id = %d AND right_object_type = %s AND left_object_type = %s AND left_object_id = %d", $left_object_id, $left_object_type, $right_object_type, $right_object_id );

	# If this relationship exists, return the relationship ID as an integer
	if ( ! empty( $sql2 ) ) {
		$row = $wpdb->get_row( $sql2 );
		return (int) $row->relationship_id;
	}

	# Relationship does not exist, so insert it now ($wpdb->insert sanitizes data)
	$wpdb->insert( $table_name, $relationship_array );

	# Hook after relationship added
	do_action( 'added_object_relationship', $wpdb->insert_id, $left_object_id, $left_object_type, $right_object_type, $right_object_id );

	# Return relationship ID of last inserted relationship
	return $wpdb->insert_id;

}


/* DELETE BI-DIRECTIONAL RELATIONSHIP BETWEEN TWO OBJECTS */
function kts_delete_object_relationship( $left_object_id, $left_object_type, $right_object_type, $right_object_id ) {

	# Error if $left_object_id is not a positive integer
	if ( filter_var( $left_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( $left_object_id === 0 ) {
			$item = 0;
		}
		elseif ( is_object( $left_object_id ) ) {
			$item = 'object';
		}
		elseif ( is_array( $left_object_id ) ) {
			$item = 'array';
		}

		return new WP_Error( 'left_object_id', 'kts_add_object_relationship() expects parameter 1 to be a positive integer, ' . $item . ' given.' );
	}

	# Error if $right_object_id is not a positive integer
	if ( filter_var( $right_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( $right_object_id === 0 ) {
			$item = 0;
		}
		elseif ( is_object( $right_object_id ) ) {
			$item = 'object';
		}
		elseif ( is_array( $right_object_id ) ) {
			$item = 'array';
		}

		return new WP_Error( 'right_object_id', 'kts_add_object_relationship() expects parameter 4 to be a positive integer, ' . $item . ' given.' );
	}

	$recognized_relationship_objects = kts_recognized_relationship_objects();

	# Error if $left_object_type is not  a non-null string of an appropriate value
	if ( ! in_array( $left_object_type, $recognized_relationship_objects ) ) {

		$item = $left_object_type;
		if ( is_int( $left_object_type ) ) {
			$item = 'integer';
		}
		elseif ( is_object( $left_object_type ) ) {
			$item = 'object';
		}
		elseif ( is_array( $left_object_type ) ) {
			$item = 'array';
		}

		$object_list = implode( ', ', $recognized_relationship_objects );

		return new WP_Error( 'left_object_type', 'kts_add_object_relationship() expects parameter 2 to be one of ' . $object_list . ', ' .$item . ' given.' );
	}

	# Error if $right_object_type is not a non-null string of an appropriate value
	if ( ! in_array( $right_object_type, $recognized_relationship_objects ) ) {

		$item = $right_object_type;
		if ( is_int( $right_object_type ) ) {
			$item = 'integer';
		}
		elseif ( is_object( $right_object_type ) ) {
			$item = 'object';
		}
		elseif ( is_array( $right_object_type ) ) {
			$item = 'array';
		}

		$object_list = implode( ', ', $recognized_relationship_objects );

		return new WP_Error( 'left_object_type', 'kts_add_object_relationship() expects parameter 3 to be one of ' . $object_list . ', ' .$item . ' given.' );
	}

	# Good to go!
	global $wpdb;
	$table_name = $wpdb->prefix . 'kts_object_relationships';

	# Hook before relationship deleted
	do_action( 'pre_delete_object_relationship', $left_object_id, $left_object_type, $right_object_type, $right_object_id );

	# $wpdb->query does not sanitize data, so use $wpdb->prepare
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE left_object_id = %d AND left_object_type = %s AND right_object_type = %s AND right_object_id = %d", $left_object_id, $left_object_type, $right_object_type, $right_object_id ) );
	
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE right_object_id = %d AND right_object_type= %s AND left_object_type = %s AND left_object_id = %d", $left_object_id, $left_object_type, $right_object_type, $right_object_id ) );

	# Hook after relationship deleted
	do_action( 'deleted_object_relationship', $left_object_id, $left_object_type, $right_object_type, $right_object_id );
}


/* GET IDS OF RELATED OBJECTS */
function kts_get_object_relationship_ids( $left_object_id, $left_object_type, $right_object_type ) {

	# Error if $left_object_id is not a positive integer
	if ( filter_var( $left_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( $left_object_id === 0 ) {
			$item = 0;
		}
		elseif ( is_object( $left_object_id ) ) {
			$item = 'object';
		}
		elseif ( is_array( $left_object_id ) ) {
			$item = 'array';
		}

		return new WP_Error( 'left_object_id', 'kts_add_object_relationship() expects parameter 1 to be a positive integer, ' . $item . ' given.' );
	}

	$recognized_relationship_objects = kts_recognized_relationship_objects();

	# Error if $left_object_type is not a non-null string of an appropriate value
	if ( ! in_array( $left_object_type, $recognized_relationship_objects ) ) {

		$item = $left_object_type;
		if ( is_int( $left_object_type ) ) {
			$item = 'integer';
		}
		elseif ( is_object( $left_object_type ) ) {
			$item = 'object';
		}
		elseif ( is_array( $left_object_type ) ) {
			$item = 'array';
		}

		$object_list = implode( ', ', $recognized_relationship_objects );

		return new WP_Error( 'left_object_type', 'kts_add_object_relationship() expects parameter 2 to be one of ' . $object_list . ', ' .$item . ' given.' );
	}

	# Error if $right_object_type is not a non-null string of an appropriate value
	if ( ! in_array( $right_object_type, $recognized_relationship_objects ) ) {

		$item = $right_object_type;
		if ( is_int( $right_object_type ) ) {
			$item = 'integer';
		}
		elseif ( is_object( $right_object_type ) ) {
			$item = 'object';
		}
		elseif ( is_array( $right_object_type ) ) {
			$item = 'array';
		}

		$object_list = implode( ', ', $recognized_relationship_objects );

		return new WP_Error( 'left_object_type', 'kts_add_object_relationship() expects parameter 3 to be one of ' . $object_list . ', ' .$item . ' given.' );
	}

	# Good to go!
	global $wpdb;
	$table_name = $wpdb->prefix . 'kts_object_relationships';

	# Query database table from left to right
	$sql1 = $wpdb->prepare( "SELECT * FROM $table_name WHERE left_object_id = %d AND left_object_type = %s AND right_object_type = %s", $left_object_id, $left_object_type, $right_object_type );

	# Query database table right to left
	$sql2 = $wpdb->prepare( "SELECT * FROM $table_name WHERE right_object_id = %d AND right_object_type = %s AND left_object_type = %s", $left_object_id, $left_object_type, $right_object_type );

	# Results are in the form of two objects
	$rows1 = $wpdb->get_results( $sql1 );
	$rows2 = $wpdb->get_results( $sql2 );

	# Create array of object IDs (or empty array)
	$target_ids = [];
	if ( ! empty( $rows1 ) ) {
		foreach( $rows1 as $row ) {
			$target_ids[] = (int) $row->right_object_id; // cast each one as an integer
		}
	}

	if ( ! empty( $rows2 ) ) {
		foreach( $rows2 as $row ) {
			$target_ids[] = (int) $row->left_object_id; // cast each one as an integer
		}
	}

	# Return the above array
	return $target_ids;
}
