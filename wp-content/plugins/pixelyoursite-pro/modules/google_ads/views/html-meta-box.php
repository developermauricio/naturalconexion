<?php
namespace PixelYourSite;

global $post;

$meta = get_post_meta( $post->ID, '_pys_conversion_label_settings', true );
if(!$meta) {
    $meta = ['enable' => false,'label' => '','id'=>''];
}
$ids = Ads()->getPixelIDs();
?>

<div class="pys-ads-conversion_label" style="margin: 15px 0;">
    <label for="pys_conversion_label_disable" style="font-weight: normal;">
        <input name="pys_ads_conversion_label[enable]"
               type="checkbox"
            <?php checked( $meta['enable'] ); ?>
               id="pys_conversion_label_disable"
               value="1"> Enable
    </label>
    <label style="display: block;margin-bottom: 5px;margin-top:10px;font-weight: bold">Conversion label</label>
    <input type="text" name="pys_ads_conversion_label[label]" value="<?=$meta['label']?>" placeholder="Conversion label"/>

    <label style="display: block;margin-bottom: 5px;margin-top:10px;font-weight: bold">
        Google Ads Tag
    </label>
    <select name="pys_ads_conversion_label[id]">
        <?php
        foreach ($ids as $id) : ?>
            <option value="<?=$id?>" <?php selected($id,$meta['id'])?>><?=$id?></option>
        <?php
        endforeach;
        ?>
    </select>

</div>
