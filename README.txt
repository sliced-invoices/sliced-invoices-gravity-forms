=== Sliced Invoices & Gravity Forms ===
Contributors: SlicedInvoices
Donate link: http://slicedinvoices.com/
Tags: gravity forms, gravity forms add on, gravity invoice, gravity forms invoice, gravity forms estimate, gravity forms quote, invoice, invoicing, quotes, estimates, invoice clients, quote request, estimate request
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 1.12.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create an invoice or quote request form using Gravity Forms. Each form entry then creates a quote (or an invoice) using the Sliced Invoices plugin.


== Description ==

**[View Live Demo](http://slicedinvoices.com/demo/quote-request-gravity-forms/)**

Imagine having a form on your website that allows your visitors to basically create their own quotes and invoices! 

= Requirements =

*   [Sliced Invoices Plugin](https://wordpress.org/plugins/sliced-invoices/) (free)
*   [Gravity Forms Plugin](http://www.gravityforms.com/purchase-gravity-forms/) (Premium)

= Set up the Form =
Once you have both plugins installed and activated, you simply need to create your Quote (or invoice) Request form that contains the following fields (required fields marked with an asterix):

*   Client Name*
*   Client Email*
*   Business Name*
*   Address
*   Extra Client Info
*   Order Number (only shown for invoices)
*   Title* (the invoice or quote title)
*   Description (the invoice or quote description)
*   Line Items (see notes in FAQ section)


= Set up the Feed =
With the form now set up, navigate to Form Settings --> Sliced Invoices to create a new form feed.
Now simply choose whether the form will create an invoice or quote and map each of the field names to the fields you have just set up in the form.

= Add the Form to your site =
With the form setup and the fields mapped, you simply need to add the form shortcode to one of your pages in the usual way. When a client fills in your Quote Request form, a new quote (or invoice) will automatically be created with all of their details added to the quote. 

You then need to simply add the line items and pricing to the quote and it is then ready to send to the client.

If the email address that the client fills in is not already linked to a client, the plugin will automatically create a new client with this email.

You can also set up confirmations and notifications as per normal in the Gravity Forms settings.

== Installation ==
1. Upload plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= Using Pre-Defined Line Items =
You can include your pre-defined line items as an option by using the List field type. To do this, add a List field with 4 columns named something like Qty, Title, Amount, Description (you can name them what you like but they must be in this order). In the Advanced tab of the List field, tick the 'Allow field to be populated dynamically' box and add 'sliced_line_items' as the Parameter name (without the quotes). 
Now in the Feed Settings section, you need to map the Line Items field to Line Items (Full) in the dropdown. 
It will now automatically add a dropdown into the list field with your pre-defined line items.

= Using Product fields for Line Items =
You can also use Gravity Forms' Product fields as line items, instead of the List field type described above.  To do this, simply check the "Use Product field(s) for Line Items" box on the feed settings page.  Sliced Invoices will search your form for all Product fields and add them as line items automatically.

= Does this work with Gravity Flow =

Yes! [Gravity Flow](https://gravityflow.io/) is a very powerful addition to Gravity Forms, and fully integrates with Sliced Invoices.


== Screenshots ==
1. Sliced Invoices settings is added to the native Gravity Forms dropdown.
1. Adding a new feed.
1. Mapping the fields on our form to our quote or invoice.
1. Creating the form using Gravity Forms.


== Changelog ==
=1.12.3 =
* FIX: issue where additional tax settings not populated on quote/invoice automatically
* FIX: issue where quote/invoice number suffix not populated automatically

=1.12.2 =
* FIX: allow html tags in "Terms & Conditions" field

=1.12.1 =
* FIX: issue where no client created if name is identical to one that already exists
* FIX: issue where pricing fields are saved with wrong decimal separator, if decimal separator is not a period (".")

=1.12.0 =
* UPDATE: insert default terms & conditions into newly generated quotes/invoices

=1.11.0 =
* NEW: add filter 'sliced_gravityforms_line_items'
* UPDATE: set line items to taxable by default

=1.10.0 =
* NEW: option to populate line items from GF Product fields (instead of just a List field)
* NEW: option to set quote/invoice status to something other than "draft"
* NEW: option to automatically send quote/invoice to client
* FIX: make sure payment options are populated when creating invoice
* UPDATE: Update .pot file

=1.09 =
* NEW: Add hook 'sliced_gravityforms_feed_processed'
* UPDATE: add explicit capabilities for use with Members plugin

=1.08 =
* UPDATE: compatibility with Sliced Invoices v3.3.0

=1.07 =
* FIX: Translation issue preventing correct processing of feed

=1.06 =
* UPDATE: allow line items to work without GF dynamic population

=1.05 =
* NEW: add support for qTranslate X (i18n multilingual)
* UPDATE: Add ability to map custom invoice numbers
* FIX: Quotes & Invoices created via feed not incrementing quote number / invoice number

=1.04 =
* UPDATE: Add ability to map custom quote numbers

=1.03 =
* UPDATE: Integration with Gravity Flow - gravityflow.io

=1.02 =
* UPDATE: Add ability to include pre-defined line items

=1.01 =
* FIX: Minor bug fixes

=1.0 =
* Initial release at WordPress.org
