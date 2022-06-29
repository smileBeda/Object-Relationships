# Object-Relationships
Enables relationships between objects to be stored in a dedicated database table

This plugin arose out of a need to overcome a limitation in ClassicPress (and WordPress), which makes it impossible to have one taxonomy as a parent of another (at least without losing the associated admin UI). It provides a simple table and three helper functions. The table relates two objects by storing each object's ID and object type in the same row. The table itself has no concept of "from" or "to". Instead, the helper functions treat each relationship is bi-directional (which, of course, all relationships are!) and so query the table both from left to right and right to left.

The current helper functions are as follows: `kts_add_object_relationship()` and `kts_delete_object_relationship()` both take the following four arguments: `$left_object_id, $left_object_type, $right_object_type, $right_object_id`, where `$left_object_id` and `$right_object_id` are both integers and `$left_object_type` and `$right_object_type` are both strings that provide the name of a recognized object type. Since the relationship is bi-directional, it is immaterial which object is treated as "left" and which is treated as "right" in each query.

When successful, or when it detects that a relationship already exists, `kts_add_object_relationship()` returns the `relationship_id`, while also preventing the insertion of duplicate relationships.

`kts_get_object_relationship_ids()` takes the first three of the above arguments and enables searching for the matching IDs of a specific object type when both that object type and the related object's ID and object type are known. It returns an array of IDs.

There is a filter, `recognized_relationship_objects`, that makes it possible to modify the list of objects that may be related using this table. There are also action hooks, `added_object_relationship`, `pre_delete_object_relationship`, and `deleted_object_relationship`, whose roles should be self-explanatory.

There is now an accompany meta database table, which enables use of the following helper functions: `add_relationship_meta()`, `update_relationship_meta()`, `delete_relationship_meta()`, and `get_relationship_meta()`.

There are probably many ways in which this plugin can be improved. (No doubt someone would prefer it to be object-oriented!) Pull requests are welcome.

### Examples
```
// Returns the relationship row ID of the related post 6 and page 264, if it exists. O if not.
$exists = kts_object_relationship_exists( 6, 'post', 'page', 264 );
// Returns relationship ID after inserting new relationship between post 6 and page 173. Will NOT insert the relationship again if it already exists (relationship has to be distinct).
$add = kts_add_object_relationship( 6, 'post', 'page', 173 );
error_log( print_r( $add, true ) );
// Returns empty after deleting the relationship row of post 6 and page 173.
$delete = kts_delete_object_relationship( 6, 'post', 'page', 173 );
error_log( print_r( $delete, true ) );
// Returns array of [(int) key => (int) Relationship ID] found between EITHER post 6 and any page or page 6 and any post. This probably needs some more spec to also return only those relationships where a _specific_ type === id in a relationship
$get = kts_get_object_relationship_ids( 6, 'post', 'page' );
error_log( print_r( $get, true ) );
```