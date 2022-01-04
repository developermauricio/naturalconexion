<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

?>

<?php
$isWpmlActive = isWPMLActive();

if($isWpmlActive) : // Show lang select for main pixel
    $savedLang = (array)PixelYourSite\Facebook()->getOption("pixel_lang");
    if(!$savedLang) $savedLang = array();
    $languageCodes = array_keys(apply_filters( 'wpml_active_languages',null,null));

    if(count($savedLang) > 0 && $savedLang[0] != "") { // load pixel settings for first pixel
        $activeLang = $savedLang[0];
    } else {
        $activeLang = implode("_",$languageCodes);
    }
    // print lang checkbox list
    if ( !empty( $languageCodes ) ) : ?>
        <div class="row my-3">
            <div class="col-3"></div>
            <div class="col-7">
                <?php printLangList($activeLang,$languageCodes,"facebook"); ?>
                <hr>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php foreach ( PixelYourSite\Facebook()->getPixelIDs() as $index => $pixel_id ) : ?>
	<?php
	
	if ( $index === 0 ) {
		continue; // skip default ID
	}
	if($isWpmlActive) {
        if (count($savedLang) > $index) {
            $activeLang = $savedLang[$index];
        } else {
            $activeLang = implode("_", $languageCodes);
        }
    }

	?>

    <div class="row mt-3">
        <div class="col-3"></div>
        <div class="col-7">
            <p><?php PixelYourSite\Facebook()->render_pixel_id( 'pixel_id', 'Facebook Pixel ID', $index ); ?></p>
            <?php if(PixelYourSite\isWooCommerceActive() &&
                        method_exists(PixelYourSite\Facebook(),'render_text_area_array_item')) : ?>

                <p><?php PixelYourSite\Facebook()->render_text_area_array_item("server_access_api_token","Api token",$index) ?></p>
                <p><?php PixelYourSite\Facebook()->render_text_input_array_item("test_api_event_code","Code",$index); ?>
                    <small class="form-text"><strong>Remove it after testing</strong></small>
                </p>
                <?php if($isWpmlActive && !empty( $languageCodes )) {
                    printLangList($activeLang,$languageCodes,"facebook");
                }

             endif; ?>
            <hr>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-sm remove-row">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
        </div>
    </div>

<?php endforeach; ?>

<div class="row mt-3" id="pys_superpack_facebook_pixel_id" style="display: none;">
	<div class="col-3"></div>
	<div class="col-7">

        <p><input type="text" name="pys[facebook][pixel_id][]" id="" value="" placeholder="Facebook Pixel ID" class="form-control"></p>
        <p><textarea          name="pys[facebook][server_access_api_token][]" id="" placeholder="Facebook Api token" class="form-control"></textarea></p>
        <p><input type="text" name="pys[facebook][test_api_event_code][]" id="" value="" placeholder="Facebook Api test event code" class="form-control"></p>
	    <?php
            if(isWPMLActive() && !empty( $languageCodes )) {
                printLangList(implode("_",$languageCodes),$languageCodes,"facebook",true);
            }
        ?>
        <hr>
    </div>
	<div class="col-2">
		<button type="button" class="btn btn-sm remove-row">
			<i class="fa fa-trash-o" aria-hidden="true"></i>
		</button>
	</div>
</div>

<div class="row my-3">
    <div class="col-3"></div>
    <div class="col-7">
        <button class="btn btn-sm btn-block btn-primary" type="button"
                id="pys_superpack_add_facebook_pixel_id">
            Add Extra Facebook Pixel ID
        </button>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        
        $('#pys_superpack_add_facebook_pixel_id').click(function (e) {

            e.preventDefault();
            
            var $row = $('#pys_superpack_facebook_pixel_id').clone()
                .insertBefore('#pys_superpack_facebook_pixel_id')
                .attr('id', '')
                .css('display', 'flex');
            var $lang = $row.find(".pixel_lang");
                $lang.val($lang.data("value"));
        });

        $(document).on('click', '.remove-row', function () {
            $(this).closest('.row').remove();
        });

        $("body .row:not(#pys_superpack_facebook_pixel_id)").on("click",".pixel_lang_check_box",function () {

            var parent = $(this).parent();
            console.log( parent);
            var langval = "";
            parent.find(".pixel_lang_check_box input:checked").each(function (index) {
                if(index!=0) {
                    langval+="_";
                }
                langval+=$(this).val();
            });
            if(langval == "") langval = "empty";
            parent.find(".pixel_lang").val(langval);
            console.log( parent.find(".pixel_lang").val());
        });
        
    });
</script>