Gravity Forms Post-receive Web Hook Add-on
==========================================

Sends Gravity Forms form submission data to a remote URI endpoint after form submission **without Zapier**.

If you don't want to [use Zapier to pass your form values along to another server](https://zapier.com/zapbook/gravity-forms/webhook/), keep reading.

## Method

On the submission of a Gravity Form, check the form's ID. If a set of form IDs stored in the WP database contains the Gravity Forms form ID, use a pre-determined URL (keyed to the form ID) to send the form submission data to the URL via POST (HTTP). 

## Installation 

1. Clone this plugin to your `wp-content/plugins` directory. 
1. Enable the plugin. 
1. Under "Forms" in the Wordpress admin area, click "Web Hook".
1. On the plugin's settings page, enter the Gravity Forms form ID and URL endpoint (where Gravity Forms will send the submission data after your form submits and passes validation).

The data is sent via the POST (HTTP) protocol and the built in [`WP_Http()`](https://developer.wordpress.org/reference/classes/wp_http/) Wordpress POST method. A "normal" PHP listener/endpoint will see the data as a standard `$_POST` array. _Currently, the plugin has only been tested with a PHP listener/endpoint._

The data arrives as key/value pairs in the format: `my_field_label => my_field_value` Spaces are replaced by an underscore(`_`).

The plugin utilizes the [Gravity Forms gform_after_submission hook](https://www.gravityhelp.com/documentation/article/gform_after_submission/). 

## Why is this a thing?

* Sometimes, you need to send data to two (or more) places at once. Or, sometimes you're not allowed to store data on your own WP server, but you can store it on someone else's. Ask me how I know.
* [Gravity Forms says that this exact functionality is available](http://docs.gravityflow.io/article/114-the-outgoing-webhook-step) but for the life of me, I cannot figure out where to enter those settings (see [link](http://docs.gravityflow.io/article/114-the-outgoing-webhook-step)). So, I made this plugin. 

## Gotchas 

At this time, file fields are not supported. The storage path to the file on the WP server will be sent, but not the actual binary (file) itself. Additionally, checkboxes return an array (not a string). As such they may have issues. All other (string based) fields seem to be working fine.
