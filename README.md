# Register Emails

Named transactional emails with merge tags, and HTML that survives Outlook.

## What it does

A plugin that sends a receipt needs three things nobody enjoys writing: merge
tags a shop owner can put in the subject line, HTML that renders the same in
Gmail and Outlook, and a way to preview the result without placing a test
order.

This registers tags and emails by name, resolves the tags against whatever
object the email is about, and builds the markup from components rather than
from a table layout you maintain by hand.

## Features

* Register a merge tag with a preview value, so the tester shows something real
* Register an email by name, with its subject and body
* Send one by name, overriding the recipient or subject at the call site
* Preview an email without sending, or triggering whatever produces it
* Build the body from components — buttons, tables, panels — not raw HTML
* Offer the available tags to an editor, so an owner can insert them

## Installation

```bash
composer require arraypress/wp-register-emails
```

## Quick start

Register what the tags mean:

```php
register_email_tag( 'shop', 'customer_name', [
	'label'    => __( 'Customer name', 'my-plugin' ),
	'callback' => fn( $order ) => $order->billing_name,
	'preview'  => 'Jane Doe',
] );
```

Then the email that uses them:

```php
register_email( 'shop', 'receipt', [
	'subject' => __( 'Your order from {site_name}', 'my-plugin' ),
	'content' => __( 'Thanks, {customer_name}.', 'my-plugin' ),
] );
```

And sending it:

```php
send_registered_email( 'shop', 'receipt', [ 'to' => $order->email ] );
```

The `preview` value is what makes the tester useful — without it, previewing
an email means having an order to preview it against.

## Tag types

A tag's `type` says how what its callback returns goes in. `text`, the
default, goes in as text — a billing name with markup in it reads as the name.
`html` goes in as it is, for a callback that builds its own markup. Any
component name — `button`, `order_items`, `alert` — draws that component from
what the callback returns. Tags work in the subject line as well as the body.

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later

## License

GPL-2.0-or-later
