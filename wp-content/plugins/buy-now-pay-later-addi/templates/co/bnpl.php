<div class='addi_description_container'>
    <div style='height: 50px;border-bottom: 1px solid #C9CDD1;background: #091A42; border-radius: 8px 8px 0 0;color: white;font-size: 14px;line-height: 19.74px;padding: 7px 30px 0 18px;'>
        <span>Después de hacer clic en &#8220;Paga con Addi&#8221;, será redirigido a Addi.</span>
    </div>
    <div class='container' style='padding: 20px;'>
        <div class="bnpl-header">
            <div class="logo">
                <img style='margin-bottom: 11px;' src='<?php echo plugins_url( '../../assets/title.png' , __FILE__ ); ?>'>
            </div>
            <div style='margin-bottom:10px;'><b style='color: #4D525C;font-size: 18px;'>Es simple, rápido y seguro</b></div>
        </div>
        <div class='mini-frame addi-co-description-block'>
            <img style=' height: 28px;width: 28px;' src='<?php echo plugins_url( '../../assets/icons03.png' , __FILE__ ); ?>'>
            <span class="addi-co-description-detail">Sin tarjeta de crédito y en minutos.</span>
        </div>
        <div class='mini-frame addi-co-description-block with-margin'>
            <img style=' height: 28px;width: 28px;' src='<?php echo plugins_url( '../../assets/icons02.png' , __FILE__ ); ?>'>
            <span class="addi-co-description-detail">Solo necesitas tu cédula y WhatsApp para aplicar.</span>
        </div>
        <div class='mini-frame addi-co-description-block with-margin'>
            <img style=' height: 28px;width: 28px;' src='<?php echo plugins_url( '../../assets/icons01.png' , __FILE__ ); ?>'>
            <span class="addi-co-description-detail">Proceso 100% online y sin papeleo.</span>
        </div>
    </div>
    <?php
    if ($discount || $widgetversion == 'ADDI_TEMPLATE_NC_DISC') {
        $discount_msg = "% de descuento";
        // Check for template name here
        if ($widgetversion == 'ADDI_TEMPLATE_NC_DISC') {
            $discount_msg = "% descuento extra para nuevos usuarios de Addi";
            $discount = 0.05;
        }
        echo "<div class='discount-container'><p class='discount-label'>" .
            ($discount * 100) . $discount_msg . "</p></div>
                                <div class='discount-sub-container'><span class='discount-sub-label'>*Verás aplicado el descuento luego de hacer click en &#8220;Paga con Addi&#8221;</span></div>";
    }

    if ($min_amount && $total < intval($min_amount_int)) {
        echo "<div class='constraint-container'>Solo disponible para compras mayores a $" .
            $min_amount . "</div>";
    }

    if ($max_amount && $total > intval($max_amount_int)) {
        echo "<div class='constraint-container'>Solo disponible para compras menores a $".
            $max_amount . "</div>";
    }
    ?>
</div>
