# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="28" height="auto"> WP Tag Order

[![Build Status](https://travis-ci.org/sectsect/wp-tag-order.svg?branch=master)](https://travis-ci.org/sectsect/wp-tag-order) [![Latest Stable Version](https://poser.pugx.org/sectsect/wp-tag-order/v)](//packagist.org/packages/sectsect/wp-tag-order) [![composer.lock](https://poser.pugx.org/sectsect/wp-tag-order/composerlock)](//packagist.org/packages/sectsect/wp-tag-order) [![Total Downloads](https://poser.pugx.org/sectsect/wp-tag-order/downloads)](//packagist.org/packages/sectsect/wp-tag-order) [![Latest Unstable Version](https://poser.pugx.org/sectsect/wp-tag-order/v/unstable)](//packagist.org/packages/sectsect/wp-tag-order) [![License](https://poser.pugx.org/sectsect/wp-tag-order/license)](//packagist.org/packages/sectsect/wp-tag-order)

### Order tags (Non-hierarchical custom taxonomies) within individual posts with simple Drag-and-Drop ↕︎ sortable feature.

#### :warning: This plugin is NOT compatible with Gutenberg on WordPress 5.x. Consider using [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/).

## Requirements

- WordPress 4.7+

## Installation

##### 1. Clone this Repo into your `wp-content/plugins` directory.
```bash
$ cd /path-to-your/wp-content/plugins/
$ git clone git@github.com:sectsect/wp-tag-order.git
```

##### 2. Activate the plugin through the "Plugins" menu in WordPress.<br>
That's it:ok_hand:

## Features

* Support `post_tag` and `non-hierarchical taxonomy`.
* Support multiple `non-hierarchical taxonomies` in a post-type.
* In the case of creating a new post, you need to save the post once to activate this feature.
* To apply for the existing post, **"Add and Remove"** any one tag once.  
Or, if you want to batch apply, Go to `Settings` -> `WP Tag Order` page, and click the `Apply` button.
* Support Multisite.
* Tested on WordPress v4.9.

## Screencast

 <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/wp-tag-order/wp-tag-order.gif" width="314" height="auto">

## APIs

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

## Notes for Developers

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

* This Plugin does not hosting on the [wordpress.org](https://wordpress.org/) repo in order to prevent a flood of support requests from wide audience. Your feedback is welcome.

## Change log  

See [CHANGELOG](https://github.com/sectsect/wp-tag-order/blob/master/CHANGELOG.md) file.

## License

See [LICENSE](https://github.com/sectsect/wp-tag-order/blob/master/LICENSE) file.

<p align="center">✌️</p>
<p align="center">
<sub><sup>A little project by <a href="https://github.com/sectsect">@sectsect</a></sup></sub>
</p>
