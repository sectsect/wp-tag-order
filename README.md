# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="28" height="auto"> WP Tag Order

[![Build Status](https://travis-ci.org/sectsect/wp-tag-order.svg?branch=master)](https://travis-ci.org/sectsect/wp-tag-order) [![Latest Stable Version](https://poser.pugx.org/sectsect/wp-tag-order/v)](//packagist.org/packages/sectsect/wp-tag-order) [![composer.lock](https://poser.pugx.org/sectsect/wp-tag-order/composerlock)](//packagist.org/packages/sectsect/wp-tag-order) [![Total Downloads](https://poser.pugx.org/sectsect/wp-tag-order/downloads)](//packagist.org/packages/sectsect/wp-tag-order) [![Latest Unstable Version](https://poser.pugx.org/sectsect/wp-tag-order/v/unstable)](//packagist.org/packages/sectsect/wp-tag-order) [![License](https://poser.pugx.org/sectsect/wp-tag-order/license)](//packagist.org/packages/sectsect/wp-tag-order)

### Order tags independently in each posts (not site-globally) on WordPress with simple Drag-and-Drop ↕︎ sortable feature.

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
* Support Multisite.

## Notes

* In the case of creating a new post, you need to save the post once to activate this feature.
* To apply for the existing post, **"Add and Remove"** any tag once.  
Or, if you want to batch apply to multiple posts,  
Go to `Settings` -> `WP Tag Order` page, and click the `Apply` button.
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

### `get_the_tags_ordered()`

``` php
<?php
$terms = get_the_tags_ordered();
if ( $terms && ! is_wp_error( $terms ) ) :
?>
<ul>
    <?php foreach ( $terms as $term ) : ?>
        <li>
            <a href="<?php echo get_term_link( $term->slug ); ?>">
                <?php echo $term->name; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
endif;
?>
```

### `get_the_terms_ordered()`

``` php
<?php
$terms = get_the_terms_ordered( $post->ID, 'post_tag' );
if ( $terms && ! is_wp_error( $terms ) ) :
?>
<ul>
    <?php foreach ( $terms as $term ) : ?>
        <li>
            <a href="<?php echo get_term_link( $term->slug ); ?>">
                <?php echo $term->name; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
endif;
?>
```

### `get_the_tag_list_ordered()`

```php
<?php echo get_the_tag_list_ordered(); ?>
```

### `get_the_term_list_ordered()`

```php
<?php echo get_the_term_list_ordered( $post->ID, 'post_tag' ); ?>
```

### `the_tags_ordered()`

```php
<?php the_tags_ordered(); ?>
```

### `the_terms_ordered()`

```php
<?php the_terms_ordered( $post->ID, 'post_tag' ); ?>
```

## Notes for Developers

* The ordered array of tags is serialized and stored in the `wp_postmeta` table.

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
