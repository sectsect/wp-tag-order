# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WP Tag Order is a WordPress plugin that enables drag-and-drop ordering of tags (non-hierarchical taxonomies) within individual posts. It stores tag order metadata and provides both PHP APIs and REST endpoints for managing tag order.

## Development Commands

### Package Manager
- **Use pnpm** (not npm) for all package operations

### Frontend Development
- `pnpm run dev` - Development build with watch mode
- `pnpm run build` - Production build
- `pnpm run lint` - Lint TypeScript files
- `pnpm run lint:fix` - Auto-fix linting issues
- `pnpm run lint:css` - Lint CSS files
- `pnpm run type-check` - TypeScript type checking

### PHP Development
- `composer run phpstan` - Run PHPStan static analysis
- PHPUnit tests located in `/tests` directory

## Architecture

### Core Structure
- **Main plugin file**: `wp-tag-order.php` - Entry point with PHP version checking
- **Core logic**: `/includes` directory
  - `functions.php` - Utility functions and constants
  - `class-tag-updater.php` - Programmatic API class
  - `rest-api.php` - REST endpoint handlers
  - `category-template.php` - Template function overrides
  - `index.php` - Admin interface logic

### Frontend Assets
- **Source**: `/src/assets/`
  - TypeScript files in `/ts` subdirectory
  - CSS files in `/css` subdirectory
- **Build**: Uses rspack (not webpack) for compilation
- **Output**: Compiled assets to `/assets/js` and `/assets/css`

### PHP Classes and Namespacing
- **Namespace**: `WP_Tag_Order\`
- **Key class**: `Tag_Updater` - Provides programmatic tag order management

### Data Storage
- Tag order stored in `wp_postmeta` table
- **Meta key pattern**: `wp-tag-order-{taxonomy}`
- **Value**: Serialized array of ordered tag IDs

## Requirements and Constraints

### System Requirements
- **PHP**: 8.0+ (strictly enforced with version checking)
- **WordPress**: 5.6+

### Taxonomy Support
- **Supported**: Non-hierarchical taxonomies and built-in tags
- **Not supported**: Hierarchical taxonomies (categories)

### Code Standards
- **PHP**: WordPress Coding Standards (WPCS), PHPStan analysis
- **JavaScript/TypeScript**: ESLint with Airbnb config
- **CSS**: StyleLint

## Key APIs

### PHP Functions
- `get_the_tags_ordered()` - Get ordered tags for current post
- `get_the_terms_ordered($post_id, $taxonomy)` - Get ordered terms
- `get_the_tag_list_ordered()` - Get formatted tag list
- `get_the_term_list_ordered()` - Get formatted term list

### Tag_Updater Class
```php
$updater = new \WP_Tag_Order\Tag_Updater();
$result = $updater->update_tag_order($post_id, $taxonomy, $tag_ids);
```

### REST API
- **Base**: `/wp-json/wp-tag-order/v1/`
- **Get order**: `GET /tags/order/{post_id}`
- **Update order**: `PUT /tags/order/{post_id}`

## Important Constants
- `WPTAGORDER_META_KEY_PREFIX` - 'wp-tag-order-'
- `WPTAGORDER_REST_NAMESPACE` - 'wp-tag-order/v1'
- `WPTAGORDER_OPTION_ENABLED_TAXONOMIES` - 'wpto_enabled_taxonomies'