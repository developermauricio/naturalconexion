=== Buy Now Pay Later - ADDI ===
Contributors: pabloandresm936
Author: Addi
Author URI: https://co.addi.com/
Tags: comments, spam
Requires at least: 5.2
Tested up to: 6.1
Requires PHP: 7.0
Stable tag: 1.6.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Addi te permite generar creditos en linea siendo una nueva pasarela de pago de Woocommerce.

== Description ==

Ofrece a tus clientes la posibilidad de comprar a cuotas lo que quieran, cuando quieran, pagando después con Addi. En minutos. SIN INTERESES. Sin complicaciones.

== Installation ==


1. Suba el archivo "woocommerce-gateway-addi" al directorio"/wp-content/plugins/", o suba el archivo comprimido buy-now-pay-later-addi.zip en el cargador de archivos.
2. Active el plugin a través del menú "Plugins" de WordPress.

Nota: Para garantizar que recibes correctamente la respuesta por parte de ADDI, te invitamos a verificar lo siguiente:
      Dentro del archivo .htaccess del servidor, las siguientes reglas deben estar presentes</p>
      
      CGIPassAuth On
      RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
      SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0

== Screenshots ==

1. Agrega Addi como método de pago de woocommerce. 
2. Configura el plugin.
3. El nuevo método de pago se mostrará en el checkout de tu sitio!.

== Changelog ==
= 1.6.7 2023-05-10 =
* Fix price validation

= 1.6.6 2023-05-08 =
* Remove widgetTrackerAmplitude triggers from woocommerce checkout
* Add Tags to readme

= 1.6.5 2023-04-13 =
* Remove Tags from readme

= 1.6.4 2023-04-03 =
* Fixed a redirection issue for unsuccessful orders
* Fixed widget issue for variable products

= 1.6.3 2023-03-23 =
* Updated the template for flex

= 1.6.2 2023-03-14 =
* Updated the template for flex

= 1.6.1 2023-03-08 =
* Updated the checkout labels

= 1.6 2023-03-01 =
* Added automatic cancellation setting

= 1.5.14 2023-01-27 =
* Fix for the widget breaking up the add to cart button

= 1.5.13 2023-01-24 =
* Updated the versions of our widgets

= 1.5.12 2023-01-04 =
* Fixed issues with the redirect URL after a purchase and the values passed to the product widget

= 1.5.11 2022-11-24 =
* Fixed visual issues for the widget and checkout.

= 1.5.10 2022-11-17 =
* Fixed visual issues with the new template.

= 1.5.9 2022-11-01 =
* Added the new templates for Addi Flex.

= 1.5.8 2022-08-12 =
* Fixed some styling issues.

= 1.5.7 2022-08-10 =
* Fixed an issue for the display of the items price when including taxes.

= 1.5.6 2022-07-18 =
* Fixed PHP for some allies

= 1.5.5 2022-07-18 =
* Fixed callbacks issues with the ADDI API

= 1.5.4 2022-07-11 =
* Fixed some compatibility issues

= 1.5.3 2022-06-16 =
* Updated the template for "Día sin IVA"

= 1.5.2 2022-06-14 =
* Fixed bug with the addi banner

= 1.5.1 2022-06-09 =
* Fix bug with the min/max purchase amount

= 1.5 2022-06-06 =
* Updated the checkout templates for BR

= 1.4.3 2022-04-20 =
* Limpieza de código.
* modificacion habilitar/deshabilitar pro defecto estados personalizados.
* corrección traducciones.
* correción de errores.
* ajustes en el archivo readme.

= 1.4.2 2022-04-19 =
* Limpieza de código.
* estados personalizados de Addi.
* correción de errores.
* ajustes en el archivo readme.

= 1.4.1 2022-03-24 =
* Limpieza de código.
* se corrigen posibles errores relacionados a funciones deprecadas de wpdb.
* se añade calculo a algoritmo de precio para widget.
* ajustes en el archivo readme.

= 1.4.0 2022-03-04 =
* Limpieza de código.
* control de errores especificos al procesar pago con Addi. Mensajes actualizados.
* ajustes en el archivo readme.

= 1.3.9 2022-02-17 =
* Limpieza de código.
* se corrigen errores en la conversión de precios para comercios con descuentos.
* ajustes en el archivo readme.

= 1.3.8 2022-01-19 =
* Limpieza de código.
* se corrigen errores.
* ajustes en el archivo readme.

= 1.3.7 2022-01-17 =
* Limpieza de código.
* se valida si el campo cedula esta diligenciado o no para ser tomado.
* ajustes en el archivo readme.

= 1.3.6 2021-12-09 =
* Limpieza de código.
* fix checkout rendering (CO).
* ajustes en el archivo readme.

= 1.3.5 2021-12-02 =
* Limpieza de código.
* Nuevo opción de posicionamiento de widget en la página de producto.
* Nueva configuración en plugin.
* ajustes en el archivo readme.

= 1.3.4 2021-11-30 =
* Limpieza de código.
* Nuevo diseño para checkout 2.0 para CO y BR.
* ajustes en el archivo readme.

= 1.3.3 2021-11-24 =
* Limpieza de código.
* Se añade fix para nuevo diseño de checkout en CO.
* ajustes en el archivo readme.

= 1.3.2 2021-11-24 =
* Limpieza de código.
* Se añade nuevo diseño de checkout para CO / BR.
* ajustes en el archivo readme.

= 1.3.1 2021-10-28 =
* Limpieza de código.
* Se añade correción errores entre el widget, banner y nuevo script.
* ajustes en el archivo readme.

= 1.3.0 2021-10-15 =
* Limpieza de código.
* Se añade nueva configuración para banner en home.
* Se añade nuevo script para widget y banner.
* traducción al portugués.
* ajustes en el archivo readme.

= 1.2.9 2021-09-10 =
* Limpieza de código.
* actualización campo por defecto fondo icono Addi.
* ajustes en el archivo readme.

= 1.2.8 2021-09-08 =
* Limpieza de código.
* re-branding
* ajustes en el archivo readme.

= 1.2.7 2021-08-25 =
* Limpieza de código.
* se añade nuevas traducciones al portugués.
* ajustes en el archivo readme.

= 1.2.6 2021-08-18 =
* Limpieza de código.
* se añade applicationId en las notas del pedido y en el detalle.
* ajustes en el archivo readme.

= 1.2.5 2021-08-11 =
* Limpieza de código.
* correción de errores.
* se corrige etiqueta php para imprimir tope producto (minAmount, maxAmount).
* se añade nuevo orden de estilos Addi , nuevos campos personalizados de checkout, recibir estilos json.
* se añade soporte variación productos.
* se añade nuevos campos para la base de datos.
* se añade traducciones al portugués.
* ajustes en el archivo readme.

= 1.2.4 2021-07-28 =
* Limpieza de código.
* correción de errores con el campo personalizado "cedula" del checkout.
* se corrige expresión regular para detectar precio de oferta en producto.
* se añade configuración nueva para el widget de Addi que controla las variaciones de precio y el precio oferta.
* se añade configuracion de estilos para el widget de Addi.
* se añade nuevos campos para la base de datos.
* se añade traducciones al portugués.
* ajustes en el archivo readme.

= 1.2.3 2021-07-22 =
* Limpieza de código.
* correción de errores para Brazil / Colombia.
* ajustes en el archivo readme.

= 1.2.2 2021-07-19 =
* Limpieza de código.
* se añade validacion precio oferta para widget de Addi cuando existe una aplicacion de tercero instalada.
* se añade validacion de campo id cuando el condicional booleano es un digito binario.
* ajustes en el archivo readme.

= 1.2.1 2021-07-12 =
* Limpieza de código.
* se añade verificación de tema para colocar logo blanco o negro sea el caso.
* se añade validacion y visualización de descuento y restricción de compra dado el caso.
* se añade validación de precio de oferta y precio regular para el widget.
* se corrige traducción a portugués.
* ajustes en el archivo readme.

= 1.2.0 2021-06-28 =
* Limpieza de código.
* se añade compatibilidad con plugin Yith Checkout Manager.
* se añade compatibilidad con plugin Checkout Field Editor for Woocommerce v 2.
* ajustes en el archivo readme.

= 1.1.9 2021-06-24 =
* Limpieza de código.
* se añade correción de errores para campo billing_id
* se ajusta margin del widget de addi
* ajustes en el archivo readme.

= 1.1.8 2021-06-24 =
* Limpieza de código.
* se añade validacion de existencias de campos en el checkout para shipping / billing
* se añade manejo de datos por cookies en vez de localstorage con javascript.
* ajustes en el archivo readme.

= 1.1.7 2021-06-23 =
* Limpieza de código.
* se añade nuevo valor para campo cédula (billing_id)
* se añade icono de advertencia y notice cuando se encuentra aplicaciones de tercero instalados.
* se añade nuevo campo de documento.
* se añde nuevos estilos desde el admin de wordpress.
* se añade nueva traducción a portugués de Brazil.
* ajustes en el archivo readme.

= 1.1.6 2021-06-11 =
* Limpieza de código.
* se añade cambio para mostrar descripción en checkout de Brasil.
* ajustes en el archivo readme.

= 1.1.5 2021-06-09 =
* Limpieza de código.
* se añade llenado de campos en el checkout de manera automática.
* se añade verificacion si crédito fue aprobado para redireccionar a orden recibida.
* se añade validación si crédito fue rechazado para redireccionar al checkout con los datos del cliente.
* se agrega estilos para widget de Addi.
* ajustes en el archivo readme.

= 1.1.4 2021-06-09 =
* Limpieza de código.
* se añade cambio de estado de orden cuando es aprobado desde el callback en vez de desde la clase de Addi.
* ajustes en el archivo readme.

= 1.1.3 2021-06-08 =
* Limpieza de código.
* se añade verificación de tablas de base de datos al momento de actualizar el plugin.
* ajustes en el archivo readme.

= 1.1.2 2021-06-08 =
* Limpieza de código.
* se añade verificación de tablas de base de datos al momento de actualizar el plugin.
* ajustes en el archivo readme.

= 1.1.1 2021-06-04 =
* Limpieza de código.
* se añade query param a url de checkout.
* se añade icono de Addi ajustado para opción de checkout.
* ajustes en el archivo readme.

= 1.1.0 2021-06-03 =
* Limpieza de código.
* se añade traducción de nuevos campos.
* se añade opcion de agregar widget de addi en página de productos.
* se añade icono en el checkout.
* se añade nueva tabla de configuraciones.
* ajustes en el archivo readme.

= 1.0.9 2021-05-31 =
* Limpieza de código.
* se añade verificación de campo cpf para Brasil.
* ajustes en el archivo readme.

= 1.0.8 2021-05-31 =
* Limpieza de código.
* se añade verificación de campos en el checkout para el campo cédula.
* ajustes en el archivo readme.

= 1.0.7 2021-05-28 =
* Limpieza de código.
* se añade verificación de plugins que afectan hook de campos personalizados.
* ajustes en el archivo readme.

= 1.0.6 2021-05-27 =
* Limpieza de código.
* se añade verificación de third party application antes de agregar nuevo campo en Brazil.
* ajustes en el archivo readme.

= 1.0.5 2021-05-11 =
* Limpieza de código.
* se añade verificación de URL para sitios en Brasil.
* ajustes en el archivo readme.

= 1.0.4 2021-05-07 =
* Limpieza de código.
* se añade descripción de metodo de pago para sitios en Brasil.
* ajustes en el archivo readme.

= 1.0.3 2021-05-05 =
* Limpieza de código.
* se añade control de logs.
* se añade instrucciones para configurar servidor.
* ajustes en el archivo readme.

= 1.0.2 2021-04-26 =
* Limpieza de código.
* se añade soporte multilenguaje al sitio para Brasil.
* ajustes en el archivo readme.

= 1.0.1 2021-04-15 =
* Limpieza de código.
* se corrigen errores relacionados a la dirección de envío, nombres y ciudad.
* ajustes en el archivo readme.

= 1.0.0 =
* Primer lanzamiento.
* ajustes en el archivo readme.

