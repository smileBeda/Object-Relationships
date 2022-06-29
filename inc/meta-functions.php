<?php 
/**
 * Holds helper functions to intereact with relationships
 */

/**
 * Add a Relationship meta
 *
 * @param int    $relationship_id The ID of the relationship to which to add meta.
 * @param string $meta_key        The meta key to add.
 * @param mixed  $meta_value      The meta value to add.
 * @param bool   $unique          If the meta to add is unique.
 */
function add_relationship_meta( $relationship_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'kts_object_relationship', $relationship_id, $meta_key, $meta_value, $unique );
}

/**
 * Update a Relationship meta
 *
 * @param int    $relationship_id The ID of the relationship to which to add meta.
 * @param string $meta_key        The meta key to add.
 * @param mixed  $meta_value      The meta value to add.
 * @param mixed  $prev_value      The previous value of the meta.
 */
function update_relationship_meta( $relationship_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'kts_object_relationship', $relationship_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete a Relationship meta
 *
 * @param int    $relationship_id The ID of the relationship to which to add meta.
 * @param string $meta_key        The meta key to add.
 * @param mixed  $meta_value      The meta value to add.
 */
function delete_relationship_meta( $relationship_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'kts_object_relationship', $relationship_id, $meta_key, $meta_value );
}

/**
 * Get a Relationship meta
 *
 * @param int    $relationship_id The ID of the relationship to which to add meta.
 * @param string $meta_key        The meta key to add.
 * @param string $single          If the meta value returned is single or array.
 */
function get_relationship_meta( $relationship_id, $meta_key = '', $single = '' ) {
	return get_metadata( 'kts_object_relationship', $relationship_id, $meta_key, $single );
}
