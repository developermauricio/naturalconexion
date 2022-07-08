=== Doppler Forms ===
Contributors: fromdoppler
Donate link: --
Tags: Doppler, Email marketing, integration, subscription, form, automation
Requires at least: 4.9
Tested up to: 6.0.0
Requires PHP: 5.6.4
Stable tag: 2.2.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Crea Formularios con la misma estética de tu web o blog. Conéctalos con Doppler y envía a tus contactos automáticamente a Listas de Suscriptores.

== Description ==

Este plugin te permite crear Formularios de Suscripción totalmente personalizados que puedes agregar a tu sitio web o blog.

Lo único que tienes que hacer es ingresar un título, elegir los Campos que deseas mostrar, 
la Lista a la que quieres enviar tus nuevos Suscriptores y el lugar donde aparecerá tu Formulario. ¡Eso es todo!

¿Aún no tienes cuenta en Doppler? [CREA UNA GRATIS](https://app.fromdoppler.com/#/signup?lang=es&id=es&origin=wordpress) y prueba el poder del Email & Automation Marketing. No necesitas contratos ni tarjetas de crédito.

== Funcionalidades ==

*Conexión rápida y sencilla con tu cuenta de Doppler.

*Fácil personalización del Formulario.

*Puedes asociar tu Lista de Suscriptores a Campañas automatizadas de Emails para que cada contacto reciba un Email de Bienvenida al suscribirse.

*Disponible en Español e Inglés

== Installation ==

1- Dirígete al panel de control de WordPress y selecciona la pestaña "Plugins" > Nuevo Plugin.

2- Elige el plugin llamado "Formularios Doppler" y haz clic en "Instalar ahora".

3- Elige la opción "Activar Plugin". Este se agregará a tu lista de Plugins.

4- Vuelve al panel de control y haz clic en la pestaña "Formularios de Doppler".

5- Ingresa tu Email de usuario y el [API Key](https://help.fromdoppler.com/es/api-interfaz-de-programacion-de-aplicaciones?utm_source=landing&utm_medium=integracion&utm_campaign=wordpress) de tu cuenta de Doppler. Presiona "Conectar".

== Configuración ==

1. Dirígete a "Apariencia" y haz clic en el Widget "Formularios de Doppler".

2. Arrástralo al área de Widgets y elige el lugar de tu sitio web donde quieras que se muestre.

3. Ya podrás visualizar el Formulario en tu sitio web.

== Frequently Asked Questions ==

= He instalado la versión 2.0 del plugin y mi Formulario no se muestra, ¿a qué se debe? =

Esta versión requiere que ingreses tu Email de Usuario además de tu API Key. Si aún no lo has hecho, ese puede ser el motivo.

= ¿Un Suscriptor puede suscribirse a más de una Lista de una vez? =

No, el Formulario puede ser asociado a una única Lista.

= ¿Hay un límite máximo de Suscriptores que pueden ser importados al mismo momento? =

No, no hay un límite.

= ¿Puedo cambiar el API Key que he utilizado para realizar la conexión? =

Sí, si tienes más de una cuenta de Doppler, y por ende, más de una API Key, puedes cambiarla. Presta atención a qué cuenta pertenece cada una para evitar enviar Suscriptores a una cuenta equivocada.

= ¿Los Formularios traen un diseño por defecto? =

No, puedes elegir que tenga el mismo diseño que tu sitio o blog, o bien, personalizarlo a tu gusto.

= ¿Dónde puedo encontrar la API Key de mi cuenta de Doppler? =

En [este artículo](https://help.fromdoppler.com/es/api-interfaz-de-programacion-de-aplicaciones?utm_source=landing&utm_medium=integracion&utm_campaign=wordpress) te lo explicamos.

== Screenshots ==

1. Conecta tus Formularios a Doppler.
2. Pantalla de conexión exitosa.
3. Listado de Formularios.
4. Creación de Formulario.
5. Creación de Formulario.
6. Creación de Formulario.
7. Doppler Widget.
8. Formulario de Suscripción.

== Changelog ==

= 2.2.7 =
* update: doppler form in front-end doesn't show when doppler account is disconnected

= 2.2.6 =
* added new feature to change form layout (display it horizontally or vertically)

= 2.2.5 =
* added new feature to use a doppler form with a shortcut tag

= 2.2.4 =
* fix re-connection issue account
* fix label showing version user ui

= 2.2.3 =
* fix friendly message curl timeout 
* add curl conection test case

= 2.2.2 =
* fix readme.txt

= 2.2.0 =
* add section for inserting datahub script

= 2.1.9 =
* Fix warnings 
* Added phone custom field support.

= 2.1.8 =
* Change Ajax js var for a less generic name to avoid conflicts.

= 2.1.7 =
* Fix typo in lists crud
* Visual changes to extensions page
* Visual changes to disconnect screen

= 2.1.6 =
* Changed colorpicker to WordPress default (Iris).
* Better visuals for Extensions page.
* Added remove icon to custom fields in forms.

= 2.1.5 =
* Check if a List is in use by a form or extension before delete it.
* Support for WooCommerce extension

= 2.1.4 =
* Publish extensions section

= 2.1.3 =
* Optimized requests to api

= 2.1.1 =  
* Fix fatal error with new extension class

= 2.1.0 =
* Added Manage Lists section.
* Added capacity for future extensions.
* New visuals, new menu.

= 2.0.2 =
* Form can be configured to redirect user to a page after subscription.

= 2.0.1 =
* Fix: Cuantity of queries to API reduced to avoid possible blocking.

= 2.0.0 =
* Added support for new API.
* It's possible to add Doppler custom fields to the form.

= 1.0.0 =
* Initial release.