=== WooCommerce Email Customizer with Drag and Drop Email Builder ===
Contributors: flycart
Donate link:
Tags:
Requires at least: 4.4.1
Tested up to: 5.3
Stable tag: 1.5.15
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create awesome transactional emails with a drag and drop email builder from your WooCommerce online store.

== Description ==
WooCommerce Email Customizer with Drag and Drop Email Builder - Greater Style, Greater Flexibility
Your emails don't have to look drab anymore.
Make your transactional emails more stunning than ever!
Woo Email Builder gives you exciting ways to customize your transactional emails to suit your brand. With this all-powerful extension you can send beautifully styled custom emails to your customers. Choose which email you want customized, and begin exploring different colors, and formats for your it.
No advanced technical skills required. You don't have to struggle with editing your php files anymore. With Woo Email, you can customize your emails to look just the way you want them to in a few really easy steps!
Woo Email Builder is a goldmine of some great customization features and offers you the utmost flexibility you won't find anywhere else. You can also add your own custom CSS as well.
What Woo Email Builder can do for you.
Customize the content of your emails: You can alter the default email content and replace it with customized content.
Lots of styling options available: Easily modify font, body and background colors. Your emails can easily support HTML tags as well.
Extensive List of Shortcodes: Woo Email Builder has several shortcodes that can be embedded your in emails easily.
Add your Logo to your Emails: Make your transactional emails more professional by incorporating your business logo in them.
Test Your Email: Once your done customizing, you can use our “Test Email” feature to view and modify your emails before they are sent.
Get an Instant Live Preview: Woo Email Builder allows you to instantly view the changes you've made to your email. All alteration are shown on the right side.
Developer-friendly customization: All changes made are automatically saved to the email template files. This makes it easy for developers to customize it too.


Key Features:

*   Vue JS based modern email template editor
*   Preview Email changes on the fly.
*   Test the email preview


= Website =

http://flycart.org/wordpress/axis-subscriptions

= Documentation =

https://www.flycart.org/wordpress/axis-subscriptions/documentation


== Installation ==
Just use the WordPress installer or upload to the /wp-content/plugins folder. Then Activate the WooCommerce Email Builder and Customizer plugin.
More information could be found in the documentation

= Minimum Requirements =

* WordPress 4.4.1 or greater
* PHP version 5.5.0 or greater
* MySQL version 5.0 or greater

== Frequently asked questions ==

== Changelog ==

= 1.5.15 - 21/07/20 =
* Fix - CSS fix save button not showing fully
* Fix - Undefined index pass1-text
* Fix - Warning while having WooCommerce subscription plugin

= 1.5.14 - 27/03/20 =
* Improvement - WooCommerce v4 compatible

= 1.5.13 - 16/12/19 =
* Improvement - Option to add color code manually.
* Fix - Warning: get_used_coupons function is deprecated since version 3.7.

= 1.5.12 - 14/11/19 =
* Improvement - Event: apply_filters('woo_email_customizer_available_languages', $avail_lang_list);
* Improvement - Additional parameter for the event woo_email_customizer_load_language_for_sending_email.
* Improvement - Removed the icon text loading through language.
* Improvement - Ask to install the Retainful for using retainful integration.
* Fix - Fatal error: Uncaught Error: Call to a member function get_image_id() on bool.

= 1.5.11 - 04/07/19 =
* Feature - Woocommerce Subscription compatible.
* Improvement - Settings link in plugin page.
* Improvement - Footer text color changed to grey for default template as users are unable to find text.
* Improvement - Shortcode [woo_mb_order_payment_url_string] for using the url in anchor href.
* Improvement - Event: apply_filters('woo_email_customizer_iphone_disable_message_reformatting', true).

= 1.5.10 - 13/05/19 =
* Feature - Added shortcode for Loading all meta fields
* Feature - Added shortcode for [woo_mb_woocommerce_email_order_meta], [woo_mb_woocommerce_email_before_order_table]
* Improvement - Support emoji icons
* Improvement - Remove double slash while loading tinymce scripts.
* Improvement - Option to change social icon images.

= 1.5.9 - 29/03/19 =
* Improvement - Display [woo_mb_shipping_method] shortcode in inline

= 1.5.8 - 12/03/19 =
* Fix - 404 error while save a template in some server due to having HTML content in POST request.
* Fix - Side bar menu scroll issue(doesn't scroll).
* Improvement - Event woo_email_customizer_add_additional_content_in_header to add additional data in html head.
* Improvement - Event woo_email_customizer_load_language_for_sending_email to handle the language code while sending an email.
* Improvement - Loader while saving the settings/email.
* Improvement - Added a class for the email container in email body.
* Features - Import/Export option.
* Features - Update script(Auto update the plugin on having a valid licence key).

= 1.5.7 - 26/02/19 =
* Fix - Missing migration script

= 1.5.6 - 25/02/19 =
* Fix - Not saving/loading the template in wordpress 5.1

= 1.5.5 - 22/02/19 =
* Fix - Compatible with wordpress 5.1
* Improvement - Remove html special chars from URL in retainfull integration

= 1.5.4 - 31/01/19 =
* Improvement - Changed the field name applied_coupon to new_coupon for Retainful email tracking

= 1.5.3 - 21/01/19 =
* Fix - Dismiss button not working in dashboard

= 1.5.2 - 17/01/19 =
* Improvement - Style improvement
* Improvement - [woo_mb_user_name] load login name instead of user nicename

= 1.5.1 - 08/01/19 =
* Improvement - Dismiss option for banner in customizer page
* Fix - Editor issue on having multiple editor script

= 1.5.0 - 07/01/19 =
* Feature - Next order coupon integration
* Improvement - Additional shortcode [woo_mb_password_reset_url_string]' to handle password reset url in anchor tag
* Fix - 2 column billing shipping display in same line

= 1.4.35 - 15/11/18 =
* Fix - Loading issue due to broken json
* Fix - Avoid loading script other than plugin page
* Feature - RTL option for each language
* Feature - Option to set the image size(Thumbnail, full)
* Improvement - Hook for fix space in tags apply_filters('woo_email_customizer_auto_fix_empty_tags', false);
* Improvement - Hook to add css from template style apply_filters('woo_email_customizer_load_css_from_template', false);;

= 1.4.34 - 03/09/18 =
* Fix - Post status draft to pending as some plugin removes the draft data
* Fix - Remove loading the script in shop order page
* Feature - Option to show sku

= 1.4.33 - 26/07/18 =
* Support - Woocommerce Germanized
* Fix - [woo_mb_order_total] loads with subtotal
* Improvement - Span tag for normal fields

= 1.4.32 - 11/07/18 =
* Fix - Anchor tag not working in iphone
* Fix - Tooltip error
* Improvement - Style fix in mso

= 1.4.31 - 12/06/18 =
* Improvement - Pixel Resizing for Outlook, accept mso code in editor

= 1.4.30 - 05/06/18 =
* Feature - Short-code for Order number
* Feature - Google+ icon
* Improvement - Run test email through Emogifier.
* Fix - Access version check through curl instead of file_get_contents
* Fix: for not loading plugin even after activated(priority issue)

= 1.4.29 - 21/05/18 =
* Feature - Reset the individual template
* Feature - Option to enable/disable each template separately
* Fix - Language fix
* Improvement - Header resize improvement
* Fix - Broken template content while copy the template
* Improvement - Install the dummy sample template for default site language

= 1.4.28 - 23/04/18 =
* Fix - WC_Order issue when some times says invalid order_id

= 1.4.27 - 26/03/18 =
* Improvement - Added .pot file for creating translation

= 1.4.26 - 22/03/18 =
* Removed upload image button

= 1.4.25 - 27/02/18 =
* Compatible - Flexible Checkout Fields for WooCommerce.
* Improvement - Added Instagram and Pinterest in social network icons.

= 1.4.24 - 12/02/18 =
* Improvement - additional data in custom code shortcode.

= 1.4.23 - 29/01/18 =
* Fix - Style compatible for default WooCommerce template. Apply the inline styles only if the template exists.

= 1.4.22 - 03/01/18 =
* Feature - Option to change image size and container width
* Improvement - Mobile compatible
* Shortcode for payment url

= 1.4.21 - 27/11/17 =
* Feature - Copy Template
* Feature - Shortcode for Custom code

= 1.4.20 - 17/11/17 =
* Fix - Wordpress 4.9 compatible

= 1.4.19 - 16/11/17 =
* Fix - WooCommerce Checkout Field Editor not replacing the shortcode in actual emails

= 1.4.18 - 08/11/17 =
* Fix - Partial refund email template doesn't loads from WooCommerce Email Customizer

= 1.4.17 - 03/11/17 =
* Feature - ShortCode for password
* Feature - ShortCode for Order link

= 1.4.16 - 26/10/17 =
* Feature - Option to display product image
* Feature - Option to change table border color
* Feature - Option to add custom css
* Fix - Fixed style issue occur due to WooCommerce Emogrifier

= 1.4.15 - 27/09/17 =
* Feature - Template override option

= 1.4.14 - 26/09/17 =
* Feature - Support WooCommerce Pre-Orders plugin

= 1.4.11 - 17/08/17 =
* Feature - Email template for reset password through WooCommerce
* Feature - ShortCode for user activation link

= 1.4.10 - 04/08/17 =
* Fix - Undefined function

= 1.4.9 - 01/08/17 =
* Feature - Support WooCommerce Checkout Field Editor plugin

= 1.4.8 - 31/07/17 =
* Feature - Support New customer template for sign up

= 1.4.7 - 21/06/17 =
* Feature - Shortcode for customer provided note

= 1.4.6 - 20/06/17 =
* Feature - Shortcode for Billing Phone

= 1.4.5 - 16/06/17 =
* Feature - Shortcode for Customer Note
* Compatibility - WooCommerce Order Statuses Manager

= 1.4.4 - 07/06/17 =
* Fix – Add template on plugin activate
* Feature - Option to hide payment instructions

= 1.4.3 - 23/05/17 =
* Fix – Loads wrong first name and email
* Fix - User Name and email not sent in registration email

= 1.4.2 - 11/05/17 =
* Fix – jQuery conflict

== Upgrade notice ==
