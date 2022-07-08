<?php
require_once '../../../wp-blog-header.php';
require_once './payu-latam.php';
get_header('shop');

if(isset($_REQUEST['TX_VALUE'])){
	$value = $_REQUEST['TX_VALUE'];
} else {
	$value = $_REQUEST['valor'];
}

echo"
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '371425360900769');
fbq('track', 'PageView');
</script>
<noscript><img height='1' width='1' style='display:none'
src='https://www.facebook.com/tr?id=371425360900769&ev=PageView&noscript=1'
/></noscript>
<!-- End Facebook Pixel Code -->";


echo "<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '143672310425062');
fbq('track', 'PageView');
fbq('track', 'Purchase', {value: 0.00, currency: 'COP'});
</script>
<noscript><img height='1' width='1' style='display:none'
src='https://www.facebook.com/tr?id=143672310425062&ev=PageView&noscript=1'
/></noscript>
<!-- End Facebook Pixel Code -->";

echo "<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '451874792780215');
fbq('track', 'PageView');
</script>
<noscript><img height='1' width='1' style='display:none'
src='https://www.facebook.com/tr?id=451874792780215&ev=PageView&noscript=1'
/></noscript>
<!-- End Facebook Pixel Code -->";

echo "<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '2870664929866084');
fbq('track', 'PageView');
</script>
<noscript><img height='1' width='1' style='display:none'
src='https://www.facebook.com/tr?id=2870664929866084&ev=PageView&noscript=1'
/></noscript>
<!-- End Facebook Pixel Code -->";


if(isset($_REQUEST['signature'])){
	$signature = $_REQUEST['signature'];
} else {
	$signature = $_REQUEST['firma'];
}

if(isset($_REQUEST['merchantId'])){
	$merchantId = $_REQUEST['merchantId'];
} else {
	$merchantId = $_REQUEST['usuario_id'];
}
if(isset($_REQUEST['referenceCode'])){
	$referenceCode = $_REQUEST['referenceCode'];
} else {
	$referenceCode = $_REQUEST['ref_venta'];
}
/*if(isset($_REQUEST['TX_VALUE'])){
	$value = $_REQUEST['TX_VALUE'];
} else {
	$value = $_REQUEST['valor'];
}*/
if(isset($_REQUEST['currency'])){
	$currency = $_REQUEST['currency'];
} else {
	$currency = $_REQUEST['moneda'];
}
if(isset($_REQUEST['transactionState'])){
	$transactionState = $_REQUEST['transactionState'];
} else {
	$transactionState = $_REQUEST['estado'];
}

$value = number_format($value, 1, '.', '');

$payu = new WC_Payu_Latam;
$api_key = $payu->get_api_key();
$signature_local = $api_key . '~' . $merchantId . '~' . $referenceCode . '~' . $value . '~' . $currency . '~' . $transactionState;
$signature_md5 = md5($signature_local);

if(isset($_REQUEST['polResponseCode'])){
	$polResponseCode = $_REQUEST['polResponseCode'];
} else {
	$polResponseCode = $_REQUEST['codigo_respuesta_pol'];
}

$agradecimiento = '';
$order = new WC_Order($referenceCode);
if($transactionState == 6 && $polResponseCode == 5){
	$estadoTx = "Transacci&oacute;n fallida";
} else if($transactionState == 6 && $polResponseCode == 4){
	$estadoTx = "Transacci&oacute;n rechazada";
} else if($transactionState == 12 && $polResponseCode == 9994){
	$estadoTx = "Pendiente, Por favor revisar si el d&eacute;bito fue realizado en el Banco";
} else if($transactionState == 4 && $polResponseCode == 1){
	$estadoTx = "Transacci&oacute;n aprobada";
	$agradecimiento = '癒Gracias por tu compra!';
} else{
	if(isset($_REQUEST['message'])){
		$estadoTx=$_REQUEST['message'];
	} else {
		$estadoTx=$_REQUEST['mensaje'];
	}
}

if(isset($_REQUEST['transactionId'])){
	$transactionId = $_REQUEST['transactionId'];
} else {
	$transactionId = $_REQUEST['transaccion_id'];
}
if(isset($_REQUEST['reference_pol'])){
	$reference_pol = $_REQUEST['reference_pol'];
} else {
	$reference_pol = $_REQUEST['ref_pol'];
}
if(isset($_REQUEST['pseBank'])){
	$pseBank = $_REQUEST['pseBank'];
} else {
	$pseBank = $_REQUEST['banco_pse'];
}
$cus = $_REQUEST['cus'];
if(isset($_REQUEST['description'])){
	$description = $_REQUEST['description'];
} else {
	$description = $_REQUEST['descripcion'];
}
if(isset($_REQUEST['lapPaymentMethod'])){
	$lapPaymentMethod = $_REQUEST['lapPaymentMethod'];
} else {
	$lapPaymentMethod = $_REQUEST['medio_pago_lap'];
}

if (strtoupper($signature) == strtoupper($signature_md5)) {
?>

	<div class="container">
	<div class="row">
		<div class="site-content col-lg-12 col-12 col-md-12">
			<div class="vc_row wpb_row vc_row-fluid">
				<div class="wpb_column vc_column_container vc_col-sm-12">
					<div class="vc_column-inner">
						<div class="wpb_wrapper">	
							<div class="wpb_single_image wpb_content_element vc_align_left">		
								<figure class="wpb_wrapper vc_figure">
									<div class="vc_single_image-wrapper   vc_box_border_grey" style="text-align: center;">
										<img width="400" height="254" src="https://naturalconexion.com/wp-content/uploads/2020/09/Logo_Color-2.png" class="vc_single_image-img attachment-full" alt="" srcset="https://naturalconexion.com/wp-content/uploads/2020/09/Logo_Color-2.png 800w, https://naturalconexion.com/wp-content/uploads/2020/09/Logo_Color-2-300x170.png 300w, https://naturalconexion.com/wp-content/uploads/2020/09/Logo_Color-2-768x436.png 768w" sizes="(max-width: 800px) 100vw, 800px">
									</div>
								</figure>
							</div>

							<div id="title-ok" class="title-wrapper  woodmart-title-color-default woodmart-title-style-simple woodmart-title-width-100 text-center woodmart-title-size-large ">
								<div class="liner-continer">
									<span class="left-line"></span>
									<h4 class="woodmart-title-container title  woodmart-font-weight-700">&iexcl;Gracias por tu compra, por apoyar el comercio justo, el campo colombiano y la cosm&eacute;tica consciente!</h4>
									<span class="right-line"></span>
								</div>			
								<!-- <div class="title-after_title">Tu orden fue recibida exitosamente. Recibir&aacute;s un email de confirmaci&oacute;n en breve.</div>-->
								<div class="title-after_title">
								    El env&iacute;o de tu pedido se realizar&aacute; al d&iacute;a siguiente de tu compra y una vez realizado la gu&iacute;a de rastreo llegar&aacute; a tu correo electr&oacute;nico, si no puedes verlo en la bandeja de entrada puedes buscarlo en la carpeta de Spam. La llegada de tu pedido depender&aacute; de Coordinadora, esta transportadora tiene un tiempo estimado de entrega de 3 a 5 d&iacute;as h&aacute;biles despu&eacute;s de realizar el env&iacute;o.
&iexcl;Esperamos tengas la mejor experiencia con nuestros productos, son hechos con mucho amor y con el maravilloso poder ancestral de la naturaleza!
								</div>	
							</div>		
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="site-content col-lg-12 col-12 col-md-12">
		    <div class="row">
				<div class="col-lg-3 col-1 col-md-1"></div>				
				<div class="col-lg-6 col-10 col-md-10">
					<h3>DATOS DE LA COMPRA</h3>
					<table>
						<tr>
							<td><strong>Estado de la transacci&oacute;n</strong></td>
							<td style="text-align: right;"><?php echo $estadoTx; ?></td>
						</tr>
						<tr>
							<td><strong>ID de la transacci&oacute;n</strong></td>
							<td style="text-align: right;"><?php echo $transactionId; ?></td>
						</tr>		
						<tr>
							<td><strong>Referencia de la venta</strong></td>
							<td style="text-align: right;"><?php echo $reference_pol; ?></td>
						</tr>		
						<tr>
							<td><strong>Referencia de la transacci&oacute;n</strong></td>
							<td style="text-align: right;"><?php echo $referenceCode; ?></td>
						</tr>
						<tr>
							<td><strong>Moneda</strong></td>
							<td style="text-align: right;"><?php echo $currency; ?></td>
						</tr>
						<tr>
							<td><strong>Entidad</strong></td>
							<td style="text-align: right;"><?php echo $lapPaymentMethod; ?></td>
						</tr>
					</table>
				</div>				
				<div class="col-lg-3 col-1 col-md-1"></div>
			</div>

			<div class="row" style="margin-top: 1rem;">
				<div class="col-lg-3 col-1 col-md-1"></div>
				<div class="col-lg-6 col-10 col-md-10">
					<h3>DETALLES DEl PEDIDO</h3>
					<table>
						<tr>
							<td><strong>PRODUCTO</strong></td>
							<td style="text-align: right;"><strong>TOTAL</strong></td>
						</tr>
						<tr>
							<td><strong>Descripci&oacute;n</strong></td>
							<td style="text-align: right;"><?php echo $description; ?></td>
						</tr>
						<tr>
							<td><strong>Valor total</strong></td>
							<td style="text-align: right;">$<?php echo $value; ?> </td>
						</tr>
					</table>
				</div>
				<div class="col-lg-3 col-1 col-md-1"></div>
			</div>
		</div>
		
		<div style="width: 100%; text-align: center; margin: -1rem 0 2rem 0;">
		    <a href="/tienda" title="" class="btn btn-color-primary btn-style-default btn-shape-rectangle btn-size-default">Volver a la tienda</a>
	    </div>
	    
	    <!-- <center>
				<table style="width: 42%; margin-top: 100px;">
					<tr align="center">
						<th colspan="2">DATOS DE LA COMPRA</th>
					</tr>
					<tr align="right">
						<td>Estado de la transacci&oacute;n</td>
						<td>< ?php echo $estadoTx; ?></td>
					</tr>
					<tr align="right">
						<td>ID de la transacci&oacute;n</td>
						<td>< ?php echo $transactionId; ?></td>
					</tr>		
					<tr align="right">
						<td>Referencia de la venta</td>
						<td>< ?php echo $reference_pol; ?></td>
					</tr>		
					<tr align="right">
						<td>Referencia de la transacci&oacute;n</td>
						<td>< ?php echo $referenceCode; ?></td>
					</tr>	
					< ?php
						if($pseBank!=null){
					?>
					<tr align="right">
						<td>CUS</td>
						<td>< ?php echo $cus; ?> </td>
					</tr>
					<tr align="right">
						<td>Banco</td>
						<td>< ?php echo $pseBank; ?> </td>
					</tr>
					< ?php } ?>
					<tr align="right">
						<td>Valor total</td>
						<td>$< ?php echo $value; ?> </td>
					</tr>
					<tr align="right">
						<td>Moneda</td>
						<td>< ?php echo $currency; ?></td>
					</tr>
					<tr align="right">
						<td>Descripción</td>
						<td>< ?php echo $description; ?></td>
					</tr>
					<tr align="right">
						<td>Entidad</td>
						<td>< ?php echo $lapPaymentMethod; ?></td>
					</tr>
				</table>
				<p/>
				<h1>< ?php echo $agradecimiento ?></h1>
			</center> -->
	</div>
</div>
<?php
} else {
	echo '<h1><center>La petici&oacute;n es incorrecta! Hay un error en la firma digital.</center></h1>';
}
get_footer('shop');
/*  ?> */
?> 
