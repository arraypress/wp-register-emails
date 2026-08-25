# Register Emails

Merge tags, HTML email components and named transactional emails for a
WordPress plugin.

## Install

```bash
composer require arraypress/wp-register-emails
```

Requires PHP 8.3.

## Use

Register the tags an email can use:

```php
register_email_tag( 'shop', 'customer_name', [
	'label'    => __( 'Customer name', 'my-plugin' ),
	'callback' => fn( $order ) => $order->billing_name,
	'preview'  => 'Jane Doe',
] );

register_email_tag( 'shop', 'view_order', [
	'type'     => 'button',
	'label'    => __( 'View order button', 'my-plugin' ),
	'callback' => fn( $order ) => [
		'text' => __( 'View your order', 'my-plugin' ),
		'url'  => $order->url(),
	],
] );
```

Register the email:

```php
register_email( 'shop', 'order_confirmation', [
	'label'    => __( 'Order confirmation', 'my-plugin' ),
	'subject'  => __( 'Order {order_id} confirmed', 'my-plugin' ),
	'content'  => '<p>' . __( 'Thanks, {customer_name}.', 'my-plugin' ) . '</p>{view_order}',
	'settings' => fn() => get_option( 'myplugin_order_email', [] ),
] );
```

Send it:

```php
$sent = send_registered_email( 'shop', 'order_confirmation', [
	'to'   => $order->email,
	'data' => $order,
] );

if ( is_wp_error( $sent ) ) {
	// It did not go, and this says why.
}
```

Or build one without registering anything:

```php
compose_email()
	->to( $customer->email )
	->subject( __( 'Your download is ready', 'my-plugin' ) )
	->content( '<p>{download_link}</p>' )
	->tags( 'shop' )
	->about( $order )
	->send();
```

## Letting people edit them

`settings` is a callback returning whatever the site owner has configured —
`subject`, `content`, `enabled`, `context`. It wins over the defaults
registered above, and an override passed at the call site wins over both.

So the plugin author writes the email once and the site owner edits it,
without either having to know about the other. An email they turned off is
not sent and is not an error: it is a setting, and reporting it as a failure
fills a log with somebody's preference.

## The merge-tag chooser

This library owns the tags. [wp-field-kit](https://github.com/arraypress/wp-field-kit)'s
`email_editor` field draws the chooser and knows nothing about what any tag
means, so one function is the whole seam between them:

```php
'body' => [
	'type'  => 'email_editor',
	'label' => __( 'Order confirmation', 'my-plugin' ),
	'tags'  => email_tags_for_editor( [ 'shop' ] ),
],
```

## Tag options

| Option        | What it does                                                  |
| ------------- | -------------------------------------------------------------- |
| `label`       | What the chooser calls it.                                      |
| `description` | A line under it in the chooser.                                 |
| `type`        | `text`, or the name of a component.                             |
| `callback`    | Given whatever the email is about; returns the value.           |
| `options`     | Component arguments the callback does not supply.               |
| `preview`     | What to show when there is no real data. A string or an array.  |

A tag whose `type` names a component draws that instead of putting in a word,
and its callback returns the component's arguments. A callback returning a
bare string fills the component's main argument — the text of a button, the
message of an alert. A component whose main argument is a *list* has no such
value, and says so: the callback has to return an array, and gets an error
naming the tag if it does not.

## Components

`activity_log`, `alert`, `button`, `code_block`, `coupon`, `divider`,
`downloads_list`, `event_details`, `info_box`, `key_value_list`,
`order_items`, `product_list`, `progress_bar`, `raw_html`, `reward_balance`,
`shipping_tracker`, `spacer`, `stats_grid`, `subscription_status`, `table`,
`testimonial`.

One of your own is a class implementing the `Component` interface, added
through the `emails_components` filter. It says what it draws and what its
main argument is called; nothing else has to be told about it.

## What it gets right

**A tag that fails does not become a blank.** It used to be caught, written to
the error log and replaced with an empty string — in two places — so a
customer received an order confirmation with a gap where the total should be
and the only record was a line in a file nobody reads. Failures are collected,
`send()` refuses, and the caller is told which tag and why. An email that
never arrives is recoverable; that one is not.

**A tag nobody registered does not go out looking like a tag.** `Hi
{customer_name}` reaching a customer exactly like that is the most
recognisable email bug there is. Unknown tags are removed, and listed.

**A component knows its own arguments.** There were three hand-maintained
lists that had to agree — type to class, which types were components, and each
one's main argument — so adding a component meant editing three places and
forgetting one gave a component that rendered as nothing, or a callback whose
output was ignored, or a string in the argument a list belonged in. That last
one made an order-items table iterate over a word: half a table, a PHP warning
in the middle of the email, and it still sent.

**Only the tags an email uses are asked for.** A site with four hundred
registered tags ran four hundred `str_contains()` over every email.

**Header values never carry a line break.** Not because it would inject one —
PHPMailer refuses a custom header containing CR or LF and strips them out of
names and subjects, so nothing lands. It refuses by throwing, which
`wp_mail()` reports as a plain `false`: the email silently does not go.

**Two plugins can both bundle this.** The filters are named after the tag they
filter, and the name used to be `email_template_tag_{name}` for everybody — so
one plugin's callback rewrote the other's order confirmation. The names carry
the build's own prefix now, derived from the namespace Strauss rewrites.

**The year is the site's, not the server's.** A copyright line that says last
year for the first few hours of the new one is a small thing that looks like
nobody is home.

## Upgrading from 1.x

**`send_email_template()` is `send_registered_email()`** and returns
`true|WP_Error` rather than a bare bool.

**`register_email_template()` is `register_email()`.** Its `tag_groups` and
`settings_callback` are `tag_groups` and `settings`.

**The singleton is gone.** `Tags` and `Emails` are plain static registries;
`Registry::get_instance()` has no replacement because it has no job.

**`arraypress/wp-currencies` is a suggestion**, not a requirement.

## Testing

```bash
composer test          # phpunit
composer lint          # phpcs, defect sniffs
composer format:check  # phpcs, formatting
```
