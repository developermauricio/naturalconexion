<?php
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}
?>

<div class="dplr-tab-content">

    <?php $this->display_success_message() ?>

    <?php $this->display_error_message() ?>

    <p class="size-medium">
        <?php _e('Send the information of your Contacts in WooCommerce to Doppler. To do this, select the Doppler Field equivalent to each of the WooCommerce Fields. <br/>Need to create Custom Fields in Doppler?','doppler-for-woocommerce'); ?>
        <a href="<?php _e('https://help.fromdoppler.com/en/how-to-create-a-customized-field?utm_source=landing&utm_medium=integracion&utm_campaign=woocommerce', 'doppler-for-woocommerce')?>" class="green-link"><?php _e('Learn how', 'doppler-for-woocommerce')?></a>.
    </p>

    <form id="dplrwoo-form-mapping" action="" method="post">

    <?php wp_nonce_field( 'map-fields' );?>

    <?php

    $maps? $used_fields = array_filter($maps): $used_fields = array();

    if(is_array($wc_fields)){

        foreach($wc_fields as $fieldtype=>$arr){

            if( $fieldtype!='' && $fieldtype!='order' && (count($arr)>0) ):

                ?>
                <table class="grid panel w-100 mw-8">
                    <thead>
                        <tr class="panel-header">
                            <th colspan="2" class="text-white semi-bold">
                                <?php
                                switch($fieldtype){
                                    case 'billing':
                                        _e('Billing fields', 'doppler-for-woocommerce');
                                        break;
                                    case 'shipping':
                                        _e('Shipping fields', 'doppler-for-woocommerce');
                                        break;
                                    case 'account':
                                        _e('Account fields', 'doppler-for-woocommerce');
                                        break;
                                    default:
                                        echo esc_html($fieldtype);
                                        break;
                                }
                                ?>
                            </th>
                        </tr>
                        <tr>
                                <th class="text-left pt-1 pb-1"><?php _e('WooCommerce Fields','doppler-for-woocommerce') ?></th>
                                <th class="text-left pt-1 pb-1"><?php _e('Doppler Fields', 'doppler-for-woocommerce') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                
                <?php

                foreach($arr as $fieldname=>$fieldAtributes){
                    isset($fieldAtributes['type'])? $woo_field_type = $fieldAtributes['type'] : $woo_field_type = 'string';
                    if($fieldname!=='billing_email'):
                        ?>
                            <tr>
                                <td><?php echo $fieldAtributes['label']?></td>
                                <td>
                                    <select class="dplrwoo-mapping-fields" name="dplrwoo_mapping[<?php echo $fieldname?>]" data-type="<?php if (isset($fieldAtributes['type'])) echo $fieldAtributes['type'] ?>">
                                        <option></option>
                                        <?php 
                                        foreach ($dplr_fields as $field){
                                            
                                            if( $this->check_field_type($woo_field_type,$field->type) && !in_array($field->name,$used_fields) || $maps[$fieldname] === $field->name ){
                                                ?>
                                                <option value="<?php echo esc_attr($field->name)?>" <?php if( $maps[$fieldname] === $field->name ) echo 'selected' ?> data-type="<?php echo esc_attr($field->type) ?>">
                                                    <?php echo esc_html($field->name)?>
                                                </option>
                                                <?php
                                            }
                                        
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        
                        <?php
                    endif;
                }

                ?>
                    </tbody>
                </table>

                <?php

            endif;
        }

    }

    ?>
        </tbody>

    </table>

    <button id="dplrwoo-mapping-btn" class="dp-button button-medium primary-green">
        <?php _e('Save and Synchronize', 'doppler-for-woocommerce') ?>
    </button>

    </form>

</div>