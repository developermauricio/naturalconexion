<section class="dplr_settings dplr-extensions">

<div class="wrap dplr_connect text-center">
		
    <h2></h2>
		
    <a href="<?php _e('https://www.fromdoppler.com/en/?utm_source=landing&utm_medium=integracion&utm_campaign=wordpress', 'doppler-form')?>" target="_blank" id="dplr_logo" class="d-inline-block"><img src="<?= plugins_url( '/../img/logo-doppler.svg', __FILE__ ); ?>" alt="Doppler"></a>
    
    <h1 class="size-huge margin-auto mb-1">
        <?php _e("Enjoy our extensions", "doppler-form" ); ?>
    </h1>
    
    <p class="subtitle margin-auto mb-1"><?php _e("Improve your business by integrating these features with you Doppler account.<br>¡Boost your Email & Automation Marketing strategy!","doppler-form") ;?> :)</p>
                                 
        <div class="dplr-boxes">
                          
            <div>
                <div class="extension-card">
                    <figure>
                        <img src="<?php echo plugins_url( '/../img/woocommerce-logo.png', __FILE__ ); ?>" alt="<?php _e('Doppler for WooCommerce', 'doppler-form')?>"/>
                    </figure>
                    
                    <h3><?php _e('Doppler for WooCommerce', 'doppler-form')?></h3>
                    
                    <p>
                        <?php _e('Import customers to your Doppler Lists.', 'doppler-form') ?>
                    </p>
                    <div class="box-footer">
                        <?php if(!$this->extension_manager->is_active('doppler-for-woocommerce')):  ?>
                            <button class="dp-button primary-green button-medium dp-install" 
                                    <?php if(!$this->extension_manager->has_dependency('doppler-for-woocommerce')) echo 'disabled'?>
                                    data-extension="doppler-for-woocommerce">
                                    <?php _e('Install', 'doppler-form') ?>
                            </button>
                            <?php if(!$this->extension_manager->has_dependency('doppler-for-woocommerce')):?>
                            <p class="text-italic"><?php _e('You should have <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce plugin</a> installed and active first.', 'doppler-form')?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <span clasS="text-regular-grey"><img src="<?php echo plugins_url('/../img/status-ckeck-icon.svg', __FILE__ );?>"> <?php _e('Successfully Instaled', 'doppler-form');?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if($this->extension_manager->is_active('doppler-for-woocommerce')):  ?>
                <a href="<?php echo admin_url('admin.php?page='.$this->extension_manager->extensions['doppler-for-woocommerce']['settings'])?>"><?php _e('Configure extension', 'doppler-form')?> >></a>
                <?php endif; ?>
            </div>
		
			<div>
                <div class="extension-card">
                    <figure>
                        <img src="<?php echo plugins_url( '/../img/learnpress-logo.png', __FILE__ ); ?>" alt="<?php _e('Doppler for LearnPress', 'doppler-form');?>"/>
                    </figure>
                
                    <h3><?php _e('Doppler for LearnPress', 'doppler-form');?></h3>
                    <p>
                        <?php _e('Import students to your Doppler Lists.', 'doppler-form') ?>
                    </p>
                    <div class="box-footer">
                        <?php if( !$this->extension_manager->is_active('doppler-for-learnpress')):  ?>
                            <button class="dp-button primary-green button-medium dp-install" 
                                    <?php if(!$this->extension_manager->has_dependency('doppler-for-learnpress')) echo 'disabled'?>
                                    data-extension="doppler-for-learnpress">
                                    <?php _e('Install', 'doppler-form') ?>
                            </button>
                            <?php if(!$this->extension_manager->has_dependency('doppler-for-learnpress')):?>
                                <p class="text-italic"><?php _e('You should have <a href="https://wordpress.org/plugins/learnpress/">LearnPress plugin</a> installed and active first.', 'doppler-form')?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <span clasS="text-regular-grey"><img src="<?php echo plugins_url('/../img/status-ckeck-icon.svg', __FILE__ );?>"> <?php _e('Successfully Instaled', 'doppler-form');?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if($this->extension_manager->is_active('doppler-for-learnpress')):  ?>
                <a href="<?php echo admin_url('admin.php?page='.$this->extension_manager->extensions['doppler-for-learnpress']['settings'])?>"><?php _e('Configure extension', 'doppler-form')?> >></a>
                <?php endif; ?>
            </div>
            
        </div> <!-- fin 3 boxes -->	

</div>	

</section>

<div id="dplr-dialog-confirm" title="<?php _e('Are you sure you want to uninstall the extension? ', 'doppler-form'); ?>">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span> <?php _e('This will deactivate and uninstall the plugin.', 'doppler-form')?></p>
</div>