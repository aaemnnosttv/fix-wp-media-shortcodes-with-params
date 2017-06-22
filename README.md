# fix-wp-media-shortcodes-with-params

Fixes WordPress media shortcodes used for embeds that have parameters in the src.

## Problem

WordPress' `[audio]` and `[video]` shortcodes for embedding media currently use [`wp_check_filetype()`](https://developer.wordpress.org/reference/functions/wp_check_filetype/) internally which fails to identify the media's type for any src URL which contains query parameters.

See core trac [#30377](https://core.trac.wordpress.org/ticket/30377)

## Solution

This plugin is a band-aid which temporarily alters the media URLs when the shortcode is called to allow the type to be successfully checked, and restores the original unaltered URL in the final HTML returned by the shortcode.

Should `wp_check_filetype()` ever be updated to support URLs with a query string, the plugin will self-deactivate.

## Considerations

The plugin only affects shortcodes which are used to render the embeded media within the content.  
**It does not fix media previews or players in wp-admin**.

There are no settings, options, filters, or anything. Simply install, activate, and enjoy.
