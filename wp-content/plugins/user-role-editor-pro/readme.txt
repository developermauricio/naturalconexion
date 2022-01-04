=== User Role Editor Pro ===
Contributors: Vladimir Garagulya (https://www.role-editor.com)
Tags: user, role, editor, security, access, permission, capability
Requires at least: 4.4
Tested up to: 5.5.1
Stable tag: 4.57.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

User Role Editor Pro WordPress plugin makes user roles and capabilities changing easy. Edit/add/delete WordPress user roles and capabilities.

== Description ==

User Role Editor Pro WordPress plugin allows you to change user roles and capabilities easy.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. Multiple roles could be assigned to user simultaneously.
You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor-pro.zip" archive content to the "/wp-content/plugins/user-role-editor-pro" directory.
3. Activate "User Role Editor Pro" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Settings"-"User Role Editor" and adjust plugin options according to your needs. For WordPress multisite URE options page is located under Network Admin Settings menu.
5. Go to the "Users"-"User Role Editor" menu item and change WordPress roles and capabilities according to your needs.

In case you have a free version of User Role Editor installed: 
Pro version includes its own copy of a free version (or the core of a User Role Editor). So you should deactivate free version and can remove it before installing of a Pro version. 
The only thing that you should remember is that both versions (free and Pro) use the same place to store their settings data. 
So if you delete free version via WordPress Plugins Delete link, plugin will delete automatically its settings data. Changes made to the roles will stay unchanged.
You will have to configure lost part of the settings at the User Role Editor Pro Settings page again after that.
Right decision in this case is to delete free version folder (user-role-editor) after deactivation via FTP, not via WordPress.

== Changelog ==
= [4.57.1] 07.09.2020 =
* Core version 4.56.1
* New: WordPress multisite: "Network admin->User Role Editor->Update Network": 
*  - 'ure_after_network_roles_update' action hook was added.  It is executed after all roles were replicated from the main site to the all other subsites of the network.
*  - 'ure_after_network_addons_update' action hook was added. It is executed after selected add-ons settings were replicated from the main site to the all other subsites of the network.
* Core version was updated to 4.56.1:
* New: WordPress multisite: Main site: Users->User Role Editor->Apply to All: 'ure_after_network_roles_update' action hook was added. It is executed after all roles were replicated from the main site to the all other subsites of the network.
* Fix: "Granted Only" filter did not work at the "Users->User Role Editor" page.
* Fix: Warning was fixed: wp-content/plugins/user-role-editor/js/ure.js: jQuery.fn.attr('checked') might use property instead of attribute.

= [4.57] 11.08.2020 =
* Core version 4.56
* New: Frond-end menu access add-on: default option "Show to" was extended to the selection between "Show to" and "Hide from" (If UI does not work as expected, force browser page refresh in order to load the latest version of front-end-menu-access.js).
* Update: Admin menu access add-on: empty menu/submenu items are removed automatically.
* Fix: Other roles access add-on: '+' was replaced with '.' in string concatenation expression.
* Update: minor code updates/optimizations 
* Core version was updated to 4.56:
* New: User capabilities 'install_languages', 'resume_plugins', 'resume_themes', 'view_site_health_checks' were added to the list of supported WordPress built-in user capabilities.
* Update: Single site WordPress installation: URE automatically grants all existing user capabilities to WordPress built-in 'administrator' role before opening its page "Users->User Role Editor". 
* Fix: Extra slash was removed between URE_PLUGIN_URL and the image resource when outputting URE_PLUGIN_URL .'/images/ajax-loader.gif' at 'Users->User Role Editor' page.
* Info: Marked as compatible with WordPress 5.5.

= [4.56.2] 06.06.2020 =
* Core version 4.55.1
* Core version was updated to 4.55.1:
* Security fix: User with 'edit_users' capability could assign to another user a role not included into the editable roles list. This fix is required to install ASAP for all sites which have user(s) with 'edit_users' capability granted not via 'administrator' role.
* Update: URE_Uninstall class properties were made 'protected' to be accessible in URE_Uninstall_Pro class included into the Pro version.

Full list of changes is available in changelog.txt file.
