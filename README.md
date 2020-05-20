# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="28" height="auto"> WP Tag Order

[![Build Status](https://travis-ci.org/sectsect/wp-tag-order.svg?branch=master)](https://travis-ci.org/sectsect/wp-tag-order) [![Latest Stable Version](https://poser.pugx.org/sectsect/wp-tag-order/v/stable)](https://packagist.org/packages/sectsect/wp-tag-order) [![License](https://poser.pugx.org/sectsect/wp-tag-order/license)](https://packagist.org/packages/sectsect/wp-tag-order)

### WP Tag Order plugin uses a simple Drag-and-Drop ⬍ sortable feature to order tags, non-hierarchical custom taxonomies within individual posts.

#### :warning: This plugin is NOT compatible with Gutenberg on WordPress 5.x. Consider using [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/).

## Requirements

- WordPress 4.7+

## Installation

##### 1. Clone this Repo into your `wp-content/plugins` directory.
```sh
$ cd /path-to-your/wp-content/plugins/
$ git clone git@github.com:sectsect/wp-tag-order.git
```

##### 2. Activate the plugin through the 'Plugins' menu in WordPress.<br>
That's it:ok_hand:

## Notes

* Support `post_tag` and `non-hierarchical taxonomy`.
* Support multiple `non-hierarchical taxonomies` in a post-type.
* In the case of creating a new post, you need to save the post once to activate this feature.
* To apply for the existing post, "`Add and Remove`" once something one tag.  
Or, if you want to batch apply, Go to `Settings` -> `WP Tag Order` page, and click the `Apply` button.
* Support Multisite.
* Tested on WordPress v4.9.

## Screencast

 <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/wp-tag-order/wp-tag-order.gif" width="314" height="auto">

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
if ( $posttags && ! is_wp_error( $posttags ) ) {
    foreach ( $posttags as $tag ) {
        echo $tag->name . ' ';
    }
}
?>

<h2>get_the_terms_ordered()</h2>
<?php
$posttags = get_the_terms_ordered( $post->ID, 'post_tag' );
if ( $posttags && ! is_wp_error( $posttags ) ) {
    foreach ( $posttags as $tag ) {
        echo $tag->name . ' ';
    }
}
?>

<h2>get_the_tag_list_ordered()</h2>
<?php echo get_the_tag_list_ordered(); ?>

<h2>get_the_term_list_ordered()</h2>
<?php echo get_the_term_list_ordered( $post->ID, 'post_tag' ); ?>

<h2>the_tags_ordered()</h2>
<?php the_tags_ordered(); ?>

<h2>the_terms_ordered()</h2>
<?php the_terms_ordered( $post->ID, 'post_tag' ); ?>
```

## NOTES for Developer

* The sorted tags will be saved in `wp_postmeta` table with an array of tag id that has been serialized as custom field.

  <table>
  <thead>
  <tr>
  <th>meta_id</th>
  <th>post_id</th>
  <th>meta_key</th>
  <th>meta_value</th>
  </tr>
  </thead>
  <tbody>
  <tr>
  <td>19</td>
  <td>7</td>
  <td>wp-tag-order-post_tag</td>
  <td><code style="word-break: break-all;">s:91:"a:7:{i:0;s:1:"7";i:1;s:1:"5";i:2;s:2:"10";i:3;s:1:"4";i:4;s:1:"6";i:5;s:1:"8";i:6;s:1:"9";}";</code></td>
  </tr></tbody></table>

* This Plugin does not hosting on the [wordpress.org](https://wordpress.org/) repo in order to prevent a flood of support requests from wide audience.

## Change log  
 * **1.2.5** - [chore] Update dependencies
 * **1.2.4** - Fix security vulnerabilities
 * **1.2.3** - [chore] Update dependencies
 * **1.2.2** - Fix wrong options for sweetalert2 / Improve code for TypeScript / [chore] Update dependencies
 * **1.2.1** - [chore] Update dependencies
 * **1.2.0** - Refactoring and Rewrite using modern JS
 * **1.1.4** - Fix a Minor bug [#7](https://github.com/sectsect/wp-tag-order/issues/7)
 * **1.1.3** - [chore] Update dependencies
 * **1.1.2** - Fix PHPCS errors / Update README
 * **1.1.1** - Migrate Gulp to v4
 * **1.1.0** - Refactoring for Ajax [#5](https://github.com/sectsect/wp-tag-order/issues/5)
 * **1.0.7** - Improve script
 * **1.0.6** - Fix some Minor bugs [#3](https://github.com/sectsect/wp-tag-order/issues/3) [#4](https://github.com/sectsect/wp-tag-order/issues/4)
 * **1.0.5** - Add compatible with WordPress 4.9
 * **1.0.4** - Add PHP Unit Tests
 * **1.0.3** - Fix PHP Notice for Undefined index [#2](https://github.com/sectsect/wp-tag-order/issues/2)
 * **1.0.2** - Fix PHP Notice for has_cap [#1](https://github.com/sectsect/wp-tag-order/issues/1) & Fix incorrect URL Paths for Ajax
 * **1.0.1** - Specification change for WordPress 4.7
 * **1.0.0** - Initial Release

## License

See [LICENSE](https://github.com/sectsect/wp-tag-order/blob/master/LICENSE) file.
