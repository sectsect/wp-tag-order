# WP Tag Order

[![PHP Unit Tests](https://github.com/sectsect/wp-tag-order/actions/workflows/phpunit.yml/badge.svg)](https://github.com/sectsect/wp-tag-order/actions/workflows/phpunit.yml) [![PHPStan](https://github.com/sectsect/wp-tag-order/actions/workflows/phpstan.yml/badge.svg)](https://github.com/sectsect/wp-tag-order/actions/workflows/phpstan.yml) [![PHP Coding Standards](https://github.com/sectsect/wp-tag-order/actions/workflows/phpcs.yml/badge.svg)](https://github.com/sectsect/wp-tag-order/actions/workflows/phpcs.yml) [![Latest Stable Version](https://poser.pugx.org/sectsect/wp-tag-order/v)](//packagist.org/packages/sectsect/wp-tag-order)

### Order tags independently in each posts (not site-globally) on WordPress with simple Drag-and-Drop ↕︎ sortable feature.

<img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/wp-tag-order/wp-tag-order.gif" width="314" height="auto">

> [!IMPORTANT]
> This plugin is NOT compatible with Gutenberg on WordPress 5.x.
> Consider using [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/).

> [!IMPORTANT]
> Mutation events will no longer be supported in Google Chrome and Edge in July 2024 (Google Chrome on July 23, 2024, and Microsoft Edge the week of July 25, 2024).
> As a result, WP Tag Order versions 3.6.0 or less will not work with Chrome v127 and later, which will be released on **July 23, 2024**.  
> You have to update to **v3.7.0 or later**.  
> If you still need PHP7 support, see Troubleshooting below.  
> See [Chrome for Developers Blog](https://developer.chrome.com/blog/mutation-events-deprecation) for more details.

## Get Started

1. Clone this Repo into your `wp-content/plugins` directory.
  ```bash
  cd /path-to-your/wp-content/plugins
  git clone git@github.com:sectsect/wp-tag-order.git
  ```
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Go to `Settings` -> `WP Tag Order` page to select which taxonomies to enable ordering for.

## Features

- 🏷️ Custom tag ordering for posts
- 🔍 Works with default WordPress tag and custom taxonomy systems
- 🔢 Drag-and-Drop interface for easy tag reordering
- 📊 Supports multiple post types and taxonomies
- 🌐 **NEW: REST API Support**
  - Retrieve ordered tags via GET endpoint
  - Update tag order programmatically
  - Supports authentication and permissions
- 🚀 Lightweight and performance-optimized

## Notes

- When creating a new post, you need to save it once to enable tag ordering.
- To apply ordering to existing posts, **"Add and Remove"** any tag once.
- To bulk-update multiple posts at once, go to `Settings` -> `WP Tag Order` page and click 'Apply' under the Advanced Settings.
* Tested on WordPress v6.3.1.

## Requirements

- WordPress 5.6+
- PHP 8.0+

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

#### `get_the_tags_ordered()`

``` php
<?php
$terms = get_the_tags_ordered();
if ( $terms && ! is_wp_error( $terms ) ) :
?>
<ul>
    <?php foreach ( $terms as $term ) : ?>
        <li>
            <a href="<?php echo get_term_link( $term->slug, 'post_tag' ); ?>">
                <?php echo $term->name; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
```

#### `get_the_terms_ordered()`

``` php
<?php
$terms = get_the_terms_ordered( $post->ID, 'post_tag' );
if ( $terms && ! is_wp_error( $terms ) ) :
?>
<ul>
    <?php foreach ( $terms as $term ) : ?>
        <li>
            <a href="<?php echo get_term_link( $term->slug, 'post_tag' ); ?>">
                <?php echo $term->name; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
```

#### `get_the_tag_list_ordered()`

```php
<?php echo get_the_tag_list_ordered(); ?>
```

#### `get_the_term_list_ordered()`

```php
<?php echo get_the_term_list_ordered( $post->ID, 'post_tag' ); ?>
```

#### `the_tags_ordered()`

```php
<?php the_tags_ordered(); ?>
```

#### `the_terms_ordered()`

```php
<?php the_terms_ordered( $post->ID, 'post_tag' ); ?>
```

## REST API

The WP Tag Order plugin provides two REST API endpoints for managing tag order:

### Get Tag Order
- **Endpoint**: `/wp-json/wp-tag-order/v1/tags/order/{post_id}`
- **Method**: `GET`
- **Parameters**:
  - `post_id` (required): The ID of the post
  - `taxonomy` (optional): Taxonomy name (defaults to 'post_tag')
- **Permissions**: Publicly accessible
- **Response**: Array of ordered tags with full term details

#### Example Request
```
GET /wp-json/wp-tag-order/v1/tags/order/123
```

<details>
 <summary>w/ cURL</summary>

```bash
curl --location 'https://your-wordpress-site.com/wp-json/wp-tag-order/v1/tags/order/123'
```
</details>

### Update Tag Order
- **Endpoint**: `/wp-json/wp-tag-order/v1/tags/order/{post_id}`
- **Method**: `PUT` or `PATCH`
- **Parameters**:
  - `post_id` (required): The ID of the post
  - `taxonomy` (required): Taxonomy name
  - `tags` (required): Comma-separated list of tag IDs in desired order
- **Permissions**: Requires user authentication and post edit capabilities
- **Response**: Success status and message

#### Example Request
```
PUT /wp-json/wp-tag-order/v1/tags/order/123
{
  "taxonomy": "post_tag",
  "tags": "5,3,1,4,2"
}
```

<details>
 <summary>w/ cURL</summary>

```bash
curl --location --request PUT 'https://your-wordpress-site.com/wp-json/wp-tag-order/v1/tags/order/123' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer YOUR_JWT_TOKEN' \
--data '{
  "taxonomy": "post_tag",
  "tags": "5,3,1,4,2"
}'
```
</details>

#### Example Response

```json
{
  "success": true,
  "code": "tags_order_updated",
  "message": "Tag order updated successfully.",
  "data": {
    "status": 200,
    "post_id": 123,
    "taxonomy": "pickup_tag",
    "tags": [
      5,
      3,
      1,
      4,
      2
    ]
  }
}
```

### Authentication
- GET requests are publicly accessible
- PUT/PATCH requests require:
  - User to be logged in
  - User to have edit permissions for the specific post
  - Post must be of an allowed type (default: 'post' or 'page')

## For Developers

- The ordered tag data is serialized and stored in the `wp_postmeta` table under keys like `wp-tag-order-{taxonomy}`.

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

- This Plugin does not hosting on the [wordpress.org](https://wordpress.org/) repo in order to prevent a flood of support requests from wide audience. Your feedback is welcome.

## Troubleshooting

### Still need support for PHP 7.

I have a branch [php7](https://github.com/sectsect/wp-tag-order/tree/php7) to support PHP 7 and End-of-Life for JaveScript Mutation events.  
The branch will not be maintained anymore, so I recommend you migrate to PHP 8.


## Change log  

See [CHANGELOG](https://github.com/sectsect/wp-tag-order/blob/master/CHANGELOG.md) file.

## License

See [LICENSE](https://github.com/sectsect/wp-tag-order/blob/master/LICENSE) file.

<p align="center">✌️</p>
<p align="center">
<sub><sup>A little project by <a href="https://github.com/sectsect">@sectsect</a></sup></sub>
</p>
