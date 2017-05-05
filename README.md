# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="28" height="auto"> WP Tag Order

### WP Tag Order plugin will order tags, non-hierarchical custom-taxonomy terms in individual posts with simple Drag and Drop Sortable capability. And supplies some functions to output it.

## Installation

 1. `cd /path-to-your/wp-content/plugins/`
 2. `git clone git@github.com:sectsect/wp-tag-order.git`
 3. Activate the plugin through the 'Plugins' menu in WordPress.<br>
 That's it :ok_hand:

## Notes

* Supports `post_tag` and `non-hierarchical taxonomy`.
* Supports multiple `non-hierarchical taxonomies` in a post-type.
* To apply for the existing post, "`Add and Remove`" once something one tag.  
Or, if you want to batch apply, Go to `Settings` -> `WP Tag Order` page, and click the `Apply` button.
* Supports Multisite.

## Screenshot

 <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/wp-tag-order/wp-tag-order.gif" width="300" height="auto">

## functions


| Function | Description |
| ------ | ----------- |
| `get_the_tags_ordered()`  | Based on `get_the_tags()` - [Codex](https://codex.wordpress.org/Function_Reference/get_the_tags)  |
| `get_the_terms_ordered()` | Based on `get_the_terms()` - [Codex](https://developer.wordpress.org/reference/functions/get_the_terms/)  |
| `get_the_tag_list_ordered()` | Based on `get_the_tag_list()` - [Codex](https://codex.wordpress.org/Function_Reference/get_the_tag_list)  |
| `get_the_term_list_ordered()` | Based on `get_the_term_list()` - [Codex](https://codex.wordpress.org/Function_Reference/get_the_term_list)  |
| `the_tags_ordered()` | Based on `the_tags()` - [Codex](https://codex.wordpress.org/Function_Reference/the_tags)  |
| `the_terms_ordered()` | Based on `the_terms()` - [Codex](https://codex.wordpress.org/Function_Reference/the_terms)  |

## Usage Example

``` php
<h2>get_the_tags_ordered()</h2>
<?php
$posttags = get_the_tags_ordered();
if ($posttags && ! is_wp_error($posttags)) {
	foreach ($posttags as $tag) {
		echo $tag->name . ' ';
	}
}
?>

<h2>get_the_terms_ordered()</h2>
<?php
$posttags = get_the_terms_ordered($post->ID, 'post_tag');
if ($posttags && ! is_wp_error($posttags)) {
	foreach ($posttags as $tag) {
		echo $tag->name . ' ';
	}
}
?>

<h2>get_the_tag_list_ordered()</h2>
<?php echo get_the_tag_list_ordered(); ?>

<h2>get_the_term_list_ordered()</h2>
<?php echo get_the_term_list_ordered($post->ID, 'post_tag'); ?>

<h2>the_tags_ordered()</h2>
<?php the_tags_ordered(); ?>

<h2>the_terms_ordered()</h2>
<?php the_terms_ordered($post->ID, 'post_tag'); ?>
```

## NOTES for Developer

* The sorted tags will be saved in `wp_postmeta` table with an array of tag id that has been serialized as custom field.
* This Plugin does not hosting on the [wordpress.org](https://wordpress.org/) repo in order to prevent a flood of support requests from wide audience.

## Change log  

 * **1.0.1** - Fix bug for WP v4.7
 * **1.0.0** - Initial Release

## License

See [LICENSE](https://github.com/sectsect/wp-tag-order/blob/master/LICENSE) file.
