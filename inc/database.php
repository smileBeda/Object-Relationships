<?php

/* CREATE TABLE FOR OBJECT RELATIONSHIPS */
function kts_object_relationships_create_db() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'kts_object_relationships';

	# Set database version as 1.0 in absence of value stored in options table
	$version = (int) get_option( 'kts_object_relationships_version', '1.0' );

	# Create database table
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		relationship_id bigint(20) NOT NULL auto_increment,
		left_object_id bigint(20) NOT NULL,
		left_object_type varchar(200) NOT NULL,
		right_object_type varchar(200) NOT NULL,
		right_object_id bigint(20) NOT NULL,
		PRIMARY KEY (relationship_id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
