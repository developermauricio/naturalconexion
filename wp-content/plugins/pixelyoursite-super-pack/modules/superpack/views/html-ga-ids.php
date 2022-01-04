<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

?>

<?php
$isWpmlActive = isWPMLActive();
if($isWpmlActive) {
    $savedLang = (array)PixelYourSite\GA()->getOption("pixel_lang");
    $languageCodes = array_keys(apply_filters( 'wpml_active_languages',null,null));
    if(!$savedLang) $savedLang = array();
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
                <?php printLangList($activeLang,$languageCodes,"ga"); ?>
                <hr>
            </div>
        </div>
    <?php endif;
}

foreach ( PixelYourSite\GA()->getPixelIDs() as $index => $pixel_id ) : ?>
	<?php
	
	if ( $index === 0 ) {
		continue; // skip default ID
	}
    if($isWpmlActive) {
        if(count($savedLang) > $index) {
            $activeLang = $savedLang[$index];
        } else {
            $activeLang = implode("_",$languageCodes);
        }
    }

	
	?>

    <div class="row mt-3">
        <div class="col-3"></div>
        <div class="col-7">
            <p><?php PixelYourSite\GA()->render_pixel_id( 'tracking_id', 'Google Analytics tracking ID', $index ); ?></p>
            <?php
                if($isWpmlActive &&  !empty( $languageCodes )) {
                    printLangList($activeLang, $languageCodes, "ga");
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

<?php endforeach; ?>

<div class="row mt-3" id="pys_superpack_ga_tracking_id" style="display: none;">
	<div class="col-3"></div>
	<div class="col-7">
        <p><input type="text" name="" id="" value="" placeholder="Google Analytics tracking ID" class="form-control"></p>
        <?php
            if($isWpmlActive && !empty( $languageCodes )) {
                printLangList(implode("_",$languageCodes), $languageCodes, "ga",true);
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
                id="pys_superpack_add_ga_tracking_id">
            Add Extra Google Analytics Tracking ID
        </button>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        
        $('#pys_superpack_add_ga_tracking_id').click(function (e) {

            e.preventDefault();

            var $row = $('#pys_superpack_ga_tracking_id').clone()
                .insertBefore('#pys_superpack_ga_tracking_id')
                .attr('id', '')
                .css('display', 'flex');

            $('input[type="text"]', $row)
                .attr('name', 'pys[ga][tracking_id][]');
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
        });
        
    });
</script>