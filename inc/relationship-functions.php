<?php
/**
 * Holds helper functions to interact with Relationships.
 */

/**
 * SPECIFY OBJECTS WHOSE RELATIONSHIPS WILL BE STORED IN THIS TABLE
 */
function kts_recognized_relationship_objects() {

	// Names of core objects.
	$objects = array(
		'comment',
		'post',
		'page',
		'taxonomy',
		'user',
	);

	// Get names of taxonomies and add them to $objects array.
	$taxonomies = get_taxonomies();
	foreach ( $taxonomies as $taxonomy ) {
		$objects[] = $taxonomy;
	}

	// Add filter to enable modification of list of recognized relationship objects.
	return apply_filters( 'recognized_relationship_objects', $objects );
}


/**
 * CHECK IF BI-DIRECTIONAL RELATIONSHIP EXISTS BETWEEN TWO OBJECTS
 *
 * @param int $left_object_id    The left object ID.
 * @param int $left_object_type  The left object type.
 * @param int $right_object_type The right object type.
 * @param int $right_object_id   The rifht object ID.
 */
function kts_object_relationship_exists( $left_object_id, $left_object_type, $right_object_type, $right_object_id ) {

	// Error if $left_object_id is not a positive integer.
	if ( filter_var( $left_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( 0 === $left_object_id ) {
			$item = 0;
		} elseif ( is_object( $left_object_id ) ) {
			$item = 'object';
		} elseif ( is_array( $left_object_id ) ) {
			$item = 'array';
		}

		return new WP_Error(
			'left_object_id',
			sprintf(
			/* translators: %s: Name of a city */
				__( 'kts_add_object_relationship() expects parameter 1 to be a positive integer, %s given', 'object-relationships' ),
				$item
			)
		);
	}

	/**
	 * Error if $right_object_id is not a positive integer
	 */
	if ( filter_var( $right_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( 0 === $right_object_id ) {
			$item = 0;
		} elseif ( is_object( $right_object_id ) ) {
			$item = 'object';
		} elseif ( is_array( $right_object_id ) ) {
			$item = 'array';
		}

		return new WP_Error(
			'right_object_id',
			sprintf(
			/* translators: %s: Name of a city */
				__( 'kts_add_object_relationship() expects parameter 4 to be a positive integer, %s given', 'object-relationships' ),
				$item
			)
		);

	}

	$recognized_relationship_objects = kts_recognized_relationship_objects();
	$object_list = implode( ', ', $recognized_relationship_objects );

	/**
	 * Error if $left_object_type is not a non-null string of an appropriate value
	 */
	if ( ! in_array( $left_object_type, $recognized_relationship_objects ) ) {

		$item = $left_object_type;
		if ( is_int( $left_object_type ) ) {
			$item = 'integer';
		} elseif ( is_object( $left_object_type ) ) {
			$item = 'object';
		} elseif ( is_array( $left_object_type ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'left_object_type',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 2 to be one of %1$s, %2$s given.', 'object-relationships' ),
				$object_list,
				$item
			)
		);

	}

	/**
	 * Error if $right_object_type is not a non-null string of an appropriate value
	 */
	if ( ! in_array( $right_object_type, $recognized_relationship_objects ) ) {

		$item = $right_object_type;
		if ( is_int( $right_object_type ) ) {
			$item = 'integer';
		} elseif ( is_object( $right_object_type ) ) {
			$item = 'object';
		} elseif ( is_array( $right_object_type ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'right_object_type',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 3 to be one of %1$s, %2$s given.', 'object-relationships' ),
				$object_list,
				$item
			)
		);

	}

	// Good to go!
	global $wpdb;
	$table_name = $wpdb->prefix . 'kts_object_relationships';
	$relationship_id = 0;

	$relationship_array = array(
		'left_object_id'        => $left_object_id,
		'left_object_type'  => $left_object_type,
		'right_object_type' => $right_object_type,
		'right_object_id'   => $right_object_id,
	);

	// If so, return the relationship ID as an integer.
	$qry = "SELECT relationship_id FROM $table_name";
	$row = $wpdb->get_row( $wpdb->prepare( $qry . ' WHERE left_object_id = %d AND left_object_type = %s AND right_object_type = %s AND right_object_id = %d', $left_object_id, $left_object_type, $right_object_type, $right_object_id ) );
	if ( is_object( $row ) ) {
		$relationship_id = (int) $row->relationship_id;
	}

	if ( ! empty( $relationship_id ) ) {

		// Hook when pre-existing relationship found.
		do_action( 'existing_object_relationship', $relationship_id, $left_object_id, $left_object_type, $right_object_type, $right_object_id );

		return $relationship_id;

	} else {

		// If this relationship exists, return the relationship ID as an integer.
		$qry = "SELECT relationship_id FROM $table_name";
		$row = $wpdb->get_row( $wpdb->prepare( $qry . ' WHERE right_object_id = %d AND right_object_type = %s AND left_object_type = %s AND left_object_id = %d', $left_object_id, $left_object_type, $right_object_type, $right_object_id ) );
		if ( is_object( $row ) ) {
			$relationship_id = (int) $row->relationship_id;
		}

		if ( ! empty( $relationship_id ) ) {

			// Hook when pre-existing relationship found.
			do_action( 'existing_object_relationship', $relationship_id, $left_object_id, $left_object_type, $right_object_type, $right_object_id );

		}
	}

	// Return relationship ID (which will be 0 if none exists).
	return $relationship_id;
}


/**
 * ADD BI-DIRECTIONAL RELATIONSHIP BETWEEN TWO OBJECTS
 *
 * @param int $left_object_id    The left object ID.
 * @param int $left_object_type  The left object type.
 * @param int $right_object_type The right object type.
 * @param int $right_object_id   The rifht object ID.
 */
function kts_add_object_relationship( $left_object_id, $left_object_type, $right_object_type, $right_object_id ) {

	// Check if relationship already exists: if so return relationship ID.
	$relationship_id = kts_object_relationship_exists( $left_object_id, $left_object_type, $right_object_type, $right_object_id );

	if ( absint( $relationship_id ) === 0 ) {

		// Relationship does not exist, so insert it now.
		global $wpdb;
		$table_name = $wpdb->prefix . 'kts_object_relationships';

		$relationship_array = array(
			'left_object_id'        => $left_object_id,
			'left_object_type'  => $left_object_type,
			'right_object_type' => $right_object_type,
			'right_object_id'   => $right_object_id,
		);

		// $wpdb->insert sanitizes data
		$added = $wpdb->insert( $table_name, $relationship_array );
		$relationship_id = $wpdb->insert_id;

		// Hook after relationship added.
		do_action( 'added_object_relationship', $relationship_id, $left_object_id, $left_object_type, $right_object_type, $right_object_id );
	}

	// Return relationship ID of newly-inserted relationship.
	return $relationship_id;
}


/**
 * DELETE BI-DIRECTIONAL RELATIONSHIP BETWEEN TWO OBJECTS
 *
 * @param int $left_object_id    The left object ID.
 * @param int $left_object_type  The left object type.
 * @param int $right_object_type The right object type.
 * @param int $right_object_id   The rifht object ID.
 */
function kts_delete_object_relationship( $left_object_id, $left_object_type, $right_object_type, $right_object_id ) {

	// Error if $left_object_id is not a positive integer.
	if ( filter_var( $left_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( 0 === $left_object_id ) {
			$item = 0;
		} elseif ( is_object( $left_object_id ) ) {
			$item = 'object';
		} elseif ( is_array( $left_object_id ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'left_object_id',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 1 to be a positive integer, %1s given.', 'object-relationships' ),
				$item
			)
		);

	}

	// Error if $right_object_id is not a positive integer.
	if ( filter_var( $right_object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( 0 === $right_object_id ) {
			$item = 0;
		} elseif ( is_object( $right_object_id ) ) {
			$item = 'object';
		} elseif ( is_array( $right_object_id ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'right_object_id',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 4 to be a positive integer, %1s given.', 'object-relationships' ),
				$item
			)
		);

	}

	$recognized_relationship_objects = kts_recognized_relationship_objects();
	$object_list = implode( ', ', $recognized_relationship_objects );

	// Error if $left_object_type is not  a non-null string of an appropriate value.
	if ( ! in_array( $left_object_type, $recognized_relationship_objects ) ) {

		$item = $left_object_type;
		if ( is_int( $left_object_type ) ) {
			$item = 'integer';
		} elseif ( is_object( $left_object_type ) ) {
			$item = 'object';
		} elseif ( is_array( $left_object_type ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'left_object_type',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 2 to be one of %1$s, %2$s given.', 'object-relationships' ),
				$object_list,
				$item
			)
		);

	}

	// Error if $right_object_type is not a non-null string of an appropriate value.
	if ( ! in_array( $right_object_type, $recognized_relationship_objects ) ) {

		$item = $right_object_type;
		if ( is_int( $right_object_type ) ) {
			$item = 'integer';
		} elseif ( is_object( $right_object_type ) ) {
			$item = 'object';
		} elseif ( is_array( $right_object_type ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'left_object_type',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 3 to be one of %1$s, %2$s given.', 'object-relationships' ),
				$object_list,
				$item
			)
		);

	}

	// Good to go!
	global $wpdb;
	$table_name = $wpdb->prefix . 'kts_object_relationships';
	$relationship_id = 0;

	// Get the relationship ID as an integer.
	$qry = "SELECT relationship_id FROM $table_name";
	$row = $wpdb->get_row( $wpdb->prepare( $qry . ' WHERE left_object_id = %d AND left_object_type = %s AND right_object_type = %s AND right_object_id = %d', $left_object_id, $left_object_type, $right_object_type, $right_object_id ) );
	if ( is_object( $row ) ) {
		$relationship_id = (int) $row->relationship_id;
	}

	if ( ! empty( $relationship_id ) ) {

		// Hook before relationship deleted.
		do_action( 'pre_delete_object_relationship', $relationship_id, $left_object_id, $left_object_type, $right_object_type, $right_object_id );

		// Delete relationship.
		$wpdb->delete( $table_name, array( 'relationship_id' => $relationship_id ), array( '%d' ) );
	} else { // nothing deleted so far.

		// Get the relationship ID as an integer.
		$qry = "SELECT relationship_id FROM $table_name";
		$row = $wpdb->get_row( $wpdb->prepare( $qry . ' WHERE right_object_id = %d AND right_object_type = %s AND left_object_type = %s AND left_object_id = %d', $left_object_id, $left_object_type, $right_object_type, $right_object_id ) );
		if ( is_object( $row ) ) {
			$relationship_id = (int) $row->relationship_id;
		}

		if ( ! empty( $relationship_id ) ) {

			// Hook before relationship deleted.
			do_action( 'pre_delete_object_relationship', $relationship_id, $left_object_id, $left_object_type, $right_object_type, $right_object_id );

			// Delete relationship.
			$wpdb->delete( $table_name, array( 'relationship_id' => $relationship_id ), array( '%d' ) );
		}
	}

	// If a relationship got deleted.
	if ( ! empty( $relationship_id ) ) {

		// Hook after relationship deleted.
		do_action( 'deleted_object_relationship', $relationship_id, $left_object_id, $left_object_type, $right_object_type, $right_object_id );
	}
}


/**
 * GET IDs OF RELATED OBJECTS
 *
 * @param int $left_object_id    The left object ID.
 * @param int $left_object_type  The left object type.
 * @param int $right_object_type The right object type.
 */
function kts_get_object_relationship_ids( $object_id, $left_object_type, $right_object_type ) {

	// Error if $left_object_id is not a positive integer.
	if ( filter_var( $object_id, FILTER_VALIDATE_INT ) === false ) {

		$item = 'string';
		if ( 0 === $object_id ) {
			$item = 0;
		} elseif ( is_object( $object_id ) ) {
			$item = 'object';
		} elseif ( is_array( $object_id ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'left_object_id',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 1 to be a positive integer, %1s given.', 'object-relationships' ),
				$item
			)
		);

	}

	$recognized_relationship_objects = kts_recognized_relationship_objects();
	$object_list = implode( ', ', $recognized_relationship_objects );

	// Error if $left_object_type is not a non-null string of an appropriate value.
	if ( ! in_array( $left_object_type, $recognized_relationship_objects ) ) {

		$item = $left_object_type;
		if ( is_int( $left_object_type ) ) {
			$item = 'integer';
		} elseif ( is_object( $left_object_type ) ) {
			$item = 'object';
		} elseif ( is_array( $left_object_type ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'left_object_type',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 2 to be one of %1$s, %2$s given.', 'object-relationships' ),
				$object_list,
				$item
			)
		);

	}

	// Error if $right_object_type is not a non-null string of an appropriate value.
	if ( ! in_array( $right_object_type, $recognized_relationship_objects ) ) {

		$item = $right_object_type;
		if ( is_int( $right_object_type ) ) {
			$item = 'integer';
		} elseif ( is_object( $right_object_type ) ) {
			$item = 'object';
		} elseif ( is_array( $right_object_type ) ) {
			$item = 'array';
		}
		return new WP_Error(
			'left_object_type',
			sprintf(
			/* translators: %1$s: Object List, %1$2: Item ID */
				__( 'kts_add_object_relationship() expects parameter 3 to be one of %1$s, %2$s given.', 'object-relationships' ),
				$object_list,
				$item
			)
		);

	}

	// Good to go!
	global $wpdb;
	$table_name = $wpdb->prefix . 'kts_object_relationships';

	// Results are in the form of two objects.
	$qry = "SELECT relationship_id FROM $table_name";
	$rows1 = $wpdb->get_results( $wpdb->prepare( $qry . ' WHERE left_object_id = %d AND left_object_type = %s AND right_object_type = %s', $object_id, $left_object_type, $right_object_type ) );
	$rows2 = $wpdb->get_results( $wpdb->prepare( $qry . ' WHERE right_object_id = %d AND left_object_type = %s AND right_object_type = %s', $object_id, $left_object_type, $right_object_type ) );

	// Create array of target object IDs, starting with empty array.
	$target_ids = array();
	if ( ! empty( $rows1 ) ) {
		foreach ( $rows1 as $row ) {
			$target_ids[] = (int) $row->relationship_id; // cast each one as an integer.
		}
	}

	if ( ! empty( $rows2 ) ) {
		foreach ( $rows2 as $row ) {
			$target_ids[] = (int) $row->relationship_id; // cast each one as an integer.
		}
	}

	// Return the above array.
	return $target_ids;
}


/**
 * DELETE ALL RELATIONSHIP META WHEN RELATIONSHIP DELETED
 *
 * @param int $relationship_id The ID of the relationship to be deleted.
 */
function kts_delete_relationship_meta_when_relationship_deleted( $relationship_id ) {
	$metas = get_relationship_meta( $relationship_id );

	if ( null !== $metas && ! empty( $metas ) ) {
		foreach ( $metas as $key => $meta ) {
			delete_relationship_meta( $relationship_id, $key );
		}
	}
}
add_action( 'deleted_object_relationship', 'kts_delete_relationship_meta_when_relationship_deleted' );
