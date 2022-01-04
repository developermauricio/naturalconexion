=== WooCommerce Smart COD ===
Contributors: fullstackhouse
Donate link: https://www.paypal.me/stratosvetsos
Tags: WooCommerce, Cash on Delivery, COD, COD Extra Fee, Smart COD, WooCommerce COD, Multiple Fees
Requires at least: 3.0.1
Tested up to: 5.8
Stable tag: 1.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

All the COD restrictions and extra fees you'll ever need, in a single plugin.

== Description ==

This plugin extends WooCommerce Cash on Delivery Gateway, providing the capability to add not one, but multiple different extra fees and restrictions based on customer's location, selected shipping method and many other factors.

It also supports many different restriction cases, covering every possible scenario you'll ever need.

= WooCommerce Smart COD PRO =
[WooCommerce Smart COD PRO](https://woosmartcod.com), **a business class, cash on delivery management plugin**. 
Reliable, secure and fully customizable, with a highly engaged and **dedicated support team!**

Some of the **additional robust features** are:

* **Risk Free COD (advance payment to secure COD)**
* **Unlimited extra fees (as many combination scenarios, as you need)**
* **WCFM Marketplace Multivendor support**
* **WooCommerce Currency Switcher (WOOCS) support**
* **Adjustable COD fee, on order-pay page (after failed order)**
* **Upload restrictions with CSV**
* **Restrict by cart amount range**
* **Restrict by product weight**
* **Restrict by customer**
* **Restrict by customer email**
* **Restrict by customer phone**
* **Restrict by stock**
* **Restrict by coupon**
* **Restrict on backorders**
* **Restrict by cart quantity range**
* **All available restrictions, now applicable on extra fees**
* **Hide/show fee on cart**

With our [PRO version](https://woosmartcod.com), you can manage cash on delivery payment gateway, securely and effectively. 

= Restrictions =
Restriction can be enable or disable. The switch between enable and disable is extremely easy.
The restrictions available are:

* Shipping Zone
* Shipping Method inside Shipping Zone
* Country
* State
* Postal Code (Supports Ranges)
* City
* User Role
* Products in cart (Supports Variations)
* Categories of the products in cart
* Cart Amount
* Shipping Class

You can define an informational message to display before the payment methods, when the COD method is not available for a customer.
You can define different messages per restrict reason.

= Extra Fees =
The extra fee can be variable and except the "standard" one, you can define fees per:

* Shipping Zone
* Country
* Shipping Method
* Shipping Zone and Shipping Method (combined)

You can enable or disable this extra fee based on the customer's cart amount.
You can use a fixed price or a percentage of the customer's cart amount.
You also have a nice rounding option.
You can enable tax for this fee.

= Details =

A usual scenario that troubles every WooCommerce Shop Admin is that he can't charge the Cash on Delivery payment method with an extra fee.
That's a very crucial requirement for the e-shops since almost everyone is charging extra this method.

This plugin except of covering the above scenario, it goes many steps further, providing a high variety of restrictions and variable extra fees. It's the only COD plugin you'll ever need.

= Developers =
The plugin extends the already existing WooCommerce Cash on Delivery Gateway, so there is no need to enable or disable gateways.
The code is clean, fast and OO.
There are three filters you can use:
* One to alter the extra fee ( wc_smart_cod_fee ).
* One to alter the current cod restriction ( wc_smart_cod_available ).
* One to change the cod fee title ( wc_smart_cod_fee_title ).

== Installation ==

1. Upload plugin to your website and activate it
2. Go as always to WooCommerce / Settings / Checkout / Cash On Delivery
3. Setup your desired settings, click 'Save Changes', and you are ready to go!

== Screenshots ==

1. assets/screenshot-1.png
2. assets/screenshot-2.png
3. assets/screenshot-3.png

== Changelog ==

= 1.6 =
* Fix - Bad require on admin partial
* Fix - Add method_exists before restriction check

= 1.5 =
* Fix - Don't show extra fee, if it is 0
* Fix - Fix deprecated ternary operation
* Fix - Admin ui fixes

= 1.4.9.6 =
* Fix - Fix issue with states on admin.

= 1.4.9.5 =
* Tweak - Improved the way the DOMDocument parses the payment div to attach the custom message.
* Fix - Add support for the newly introduced WooCommerce settings, "shipping method enable"
* Fix - Fix the way the plugin calculates the total cart amount. Until now it would add the extra fee cost if any.
* Feature - You can select what defines the total cart amount by checking / unchecking taxes and shipping costs.
* Fix - Restore broken tiptip descriptions on admin page.

= 1.4.9.4 =
* Feature - Added a new filter to change the cod fee title ( wc_smart_cod_fee_title ).

= 1.4.9.3 =
* Fix - Fix extra fee outside of cod.

= 1.4.9.2 =
* Fix - Fix warnings on checkout, WC issue when payment method is one, translatable fee.

= 1.4.9.1 =
* Fix - Minor fixes on order pay page

= 1.4.9 =
* Fix - Fix issues with wrong shipping zone - method calculation.

= 1.4.8 =
* Fix - Minor fixes, custom select2 version missing, remove version check notice, fix some public code running outside checkout page.

= 1.4.7 =
* Feature - Add restriction based on shipping method inside shipping zone
* Feature - Optimize extra fee - shipping method inside shipping zone
* Tweak - Rewrite shipping method getter function.

= 1.4.6 =
* Fix - Optimize shipping method calculation

= 1.4.5 =
* Feature - Shipping Class Restriction
* Feature - City Restriction
* Feature - Product Restriction now supports variable products
* Feature - Extra Fee now can be taxable
* Feature - Postal Codes now supports ranges
* Fix - Warnings on custom messages

= 1.4.4 =
* Fix - Fix issue with wrong calculation on amount restriction.
* Feature - Now you can have different messages per restrict reason.

= 1.4.3 =
* Fix - Fix issue with notice on empty extra fee.

= 1.4.2 =
* Fix - Fix issue with cod getting disabled, when 'enable for shipping methods' is empty.

= 1.4.1 =
* Fix - Fix issue with extra fee appears in restricted shipping method.

= 1.4.0 =
* Tweak - Select2 compatibility for older woocommerce versions
* Feature - Restriction mode is now independent per field ( enable / disable )
* Feature - Price now can be percentage for every price field with rounding option
* Feature - Added restriction for states / counties
* Feature - Added restriction for user roles

= 1.3.6 =
* Tweak - Product and product categories are now fetched asynchronously to resolve timeout issues.

= 1.3.5 =
* Feature - Restrict cod gateway based on customer's cart product's categories.
* Feature - Restrict cod gateway based on customer's cart products.

= 1.3.4 =
* Tweak - Tweak the way the plugin calculates the availability based on postal codes.
* Feature - Restrict cod gateway based on customer's cart amount.
* Feature - Added filter for developers: wc_smart_cod_fee

= 1.3.3 =
* Fix - Fix warning on empty foreach.

= 1.3.2 =
* Fix - Fix issue with cod check method running outside checkout, creating problem with wp menus.

= 1.3.1 =
* Feature - Added custom information message when the cod is not available.

= 1.3 =
* Feature - Added restriction mode option - exclude or include -
* Feature - Added different price per country
* Fix - Fix issue when delimiter was a ","

= 1.2 =
* Feature - Different amount charge, based on shipping method.
* Feature - Different amount charge, based on shipping zone and shipping method.
* Feature - Added a quick link to WooCommerce Cod Settings as a notice, when the plugin get's activated.
* Fix - When you select a country or postal code which doesn't have cod available, while you previous where on country or postal code which have the cod available, the extra fees will be applied on ajax request.
* Tweak - Clean up settings on database when a shipping zone get's deleted
* Tweak - Don't store settings on database when they are not required.

= 1.1.2 =
* Add support for older PHP versions ( < 5.4 ).

= 1.1.1 =
* Add support for older WooCommerce versions ( < 2.6 ).

= 1.0 =
* Deploy of the first version of the plugin.
