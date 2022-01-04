<?php

/**
 * Created by PhpStorm.
 * User: v0id
 * Date: 02.03.19
 * Time: 16:21
 */
class FeedFBGooglePro {

    private $_meta_keys;
    private $_meta_keys_sort;
    private $_attributes;
    private $aValues;

    public function __construct($meta_keys = null, $meta_keys_sort = null, $attributes = null) {
        $this->_meta_keys = $meta_keys;
        $this->_meta_keys_sort = $meta_keys_sort;
        $this->_attributes = $attributes;
    }

    /* FeedFBGooglePro - class for optimazing fields render in this view */

    private function _isValidAsArray($d) {
        return (count($d) && is_array($d)) ? true : false;
    }

    public function showAttributes($attributes) {
        $sResult = "";
        if ($this->_isValidAsArray($attributes)) {
            foreach ($attributes as $attr => $val) {
                $sResult .= " " . $attr . "='" . str_replace('\'', '\\\'', $val) . "' ";
            }
        }
        return $sResult;
    }

    public function showCustomOprions($aCcustom, $selval = "") {


        $sResult = "";

        if ($this->_isValidAsArray($aCcustom)) {
            foreach ($aCcustom as $text => $val) {
                $sResult .= "<option " . ( ($selval == $val) ? " selected='selected' " : "") . " value='" . htmlspecialchars($val, ENT_QUOTES) . "'>" . htmlspecialchars($text, ENT_QUOTES) . "</option>";
            }
        }
        return $sResult;
    }

    public function addCssForFeed($feed_type) {
        $sCssClass = "";
        if ($this->_isValidAsArray($feed_type)) {
            foreach ($feed_type as $ftp)
                $sCssClass .= " stl-" . $ftp;
        }
        return $sCssClass;
    }

    public function buidCountryValues($field, $fieldkey) {
        $sResult = "";

        if (count($field['custom'])) {

            global $wpwoof_values;
            $val = (empty($wpwoof_values['field_mapping']['tax_countries']['value'])) ? "" : $wpwoof_values['field_mapping']['tax_countries']['value'];
            $selected = false; //(empty($wpwoof_values['field_mapping']['tax_countries']['value']));
            if ($val && strpos($val, "-") !== false) {
                $id = (!$selected) ? explode("-", $wpwoof_values['field_mapping']['tax_countries']['value']) : "";
                $id = (is_array($id) && count($id) > 1) ? $id[1] : 0;
            } else {
                $id = $val;
            }

            $sResult .= "<div id='ID" . $fieldkey . "div'><select id='ID" . $fieldkey . "' name='field_mapping[" . $fieldkey . "][value]' onchange='showHideRedBox();'>";

            $tax_class = "-1";
            $sCloseOptGroup = "";
            $aExistsCountries = array();
            $sGlobalResult = "";
            //trace($field);
            foreach ($field['custom'] as $shcode) {
                if (empty($shcode['shcode']) && !$sGlobalResult) { //&& isset($shcode['rate'])
                    $sGlobalResult .= "<option ";
                    if (!$selected || $id == "*") {
                        $sGlobalResult .= " selected ";
                        //$selected==true;
                    }
                    $sGlobalResult .= " value='*'>";
                    $sGlobalResult .= "* - " . ( ($shcode['name']) ? $shcode['name'] : "Global" ) . " (" . $shcode['rate'] . ") ";
                    $sGlobalResult .= "</option>";
                }
                if (!in_array($shcode['shcode'], $aExistsCountries) && !empty($shcode['shcode'])) {
                    $aExistsCountries[] = $shcode['shcode'];
                    $sResult .= "<option ";
                    if (!$selected && $id == $shcode['id'] || $id == $shcode['shcode']) {
                        $sResult .= " selected ";
                        $selected == true;
                    }
                    $sResult .= " value='" . htmlspecialchars($shcode['shcode'], ENT_QUOTES) . "'>";
                    $sResult .= WpWoof_get_feed_pro_countries($shcode['shcode']) . "</option>";
                }
            }
            $sResult .= "<option " . (!$id && count($aExistsCountries) > 1 ? " selected " : "" ) . " value='' >" . __('select', 'woocommerce_wpwoof') . "</option>";
            if (!count($aExistsCountries)) {
                $sResult .= $sGlobalResult;
            }

            $sResult .= $sCloseOptGroup . "</select></div><br/>";
        }

        return $sResult;
    }

    public function wpwoof_render_empty($fieldkey, $field, $wpwoof_values) {
        ?> <?php
    }

    public function wpwoof_render_installment($fieldkey, $field, $wpwoof_values) {
        ?><p class="form-field  form-row custom_field_type">
        <?php
        // trace($field);
        $sCssClass = "";
        if (isset($field['feed_type']))
            $sCssClass = $this->addCssForFeed($field['feed_type']);

        $sAttr = ' name="wpfoof-box-media[google][installmentmonths][value]" id="_value-installmentmonths" class="select short  ' . $sCssClass . '" ';
        ?>
            <label class="<?php echo $sCssClass; ?>" ><?php echo!empty($field['header']) ? $field['header'] : $field['label']; ?></label><?php
            if (!empty($field['desc'])) {
                ?><p class="<?php echo $sCssClass; ?>"><?php echo $field['desc']; ?></p><?php
        }
            ?></p>
        <p class="form-field  form-row custom_field_type  woof-field-row">
            <?php
            $sAttr = ' name="wpfoof-box-media[google][installmentmonths][value]" id="_value-installmentmonths" class="select short  ' . $sCssClass . '" ';
            ?>
            <label class="woof-panel-label  <?php echo $sCssClass; ?>" for="installmentmonths-value">&nbsp;&nbsp;&nbsp;&nbsp;months</label>
            <input type="text"   value="<?php echo!empty($this->aValues['installmentmonths']['value']) ? htmlspecialchars($this->aValues['installmentmonths']['value'], ENT_QUOTES) : ''; ?>" <?php echo $sAttr; ?> /><?php echo $this->getHelpLinks($field); ?></p><p class="form-field  form-row custom_field_type  woof-field-row"><?php
            $sAttr = ' name="wpfoof-box-media[google][installmentamount][value]" id="_value-installmentamount" class="select short  ' . $sCssClass . '" ';
            ?><label class="woof-panel-label  <?php echo $sCssClass; ?>" for="<?php echo $fieldkey; ?>-value">&nbsp;&nbsp;&nbsp;&nbsp;amount</label>
            <input type="text"   value="<?php echo!empty($this->aValues['installmentamount']['value']) ? htmlspecialchars($this->aValues['installmentamount']['value'], ENT_QUOTES) : ''; ?>" <?php echo $sAttr; ?> />

            <?php echo $this->getHelpLinks($field); ?>
        </p><?php
    }

    public function wpwoof_render_taxonomy($fieldkey, $field, $wpwoof_values) {
        $oTool = new wpWoofTools();
        $taxSrc = admin_url('admin-ajax.php');
        $taxSrc = add_query_arg(array('action' => 'wpwoofgtaxonmy'), $taxSrc);
        $google_cats = '';
        $preselect = !empty($wpwoof_values['feed_google_category_id']) ? $oTool->convertToJSStringArray($wpwoof_values['feed_google_category_id']) : "";

        /*
          [feed_google_category] => Sporting Goods > Athletics > Baseball & Softball > Baseball Bats
          [feed_google_category_id] => 4229,4230,4231,4243
         */
            ?><hr class="wpwoof-break stl-facebook stl-google" />
        <h4 class="wpwoofeed-section-heading stl-facebook stl-google">Google Taxonomy:</h4>
        <h4 class="stl-facebook stl-google" ><br/><br/>The plugin will fill Google Taxonomy in this order:</h4>
        <p class="stl-facebook stl-google" >Product - a custom Google Taxonomy selector is added on every product</p>
        <p class="stl-facebook stl-google" >Category - a custom Google Taxonomy selector is added on every WooCommerce category</p>
        <table class="form-table stl-facebook stl-google wpwoof-addfeed-top">
            <tr class="addfeed-top-field">
                <th class="addfeed-top-label">
                    Feed:
                </th>
                <td class="addfeed-top-value">
                    <?php /* input type="text"   value="<?php echo !empty($wpwoof_values['feed_google_category']) ? htmlspecialchars($wpwoof_values['feed_google_category']) : ""; ?>"/ */ ?>
                    <input type="hidden"   class="wpwoof_google_category1_name" name="feed_google_category"     value="<?php echo!empty($wpwoof_values['feed_google_category']) ? htmlspecialchars($wpwoof_values['feed_google_category']) : ""; ?>"/>
                    <input type="hidden"   name="wpwoof_google_category1"  class="wpwoof_google_category1" style='display:none;' />
                </td>
            </tr>
        </table>
        <p class="stl-facebook stl-google" >Global - a global Google Taxonomy can be selected from the plugin's settings</p>
        <script type="text/javascript">
            jQuery(function ($) {
                loadTaxomomy(".wpwoof_google_category1");
            });
        </script><?php
        }

        public function wpwoof_item_address($fieldkey, $field, $wpwoof_values) {
                    ?><hr class="wpwoof-break stl-adsensecustom" />
        <h4 class="wpwoofeed-section-heading stl-adsensecustom">Item address:</h4>
        <h4 class="wpwoofeed stl-adsensecustom" >The plugin will fill address in this order:<br/></h4>
        <h4 class="wpwoofeed stl-adsensecustom" >The product custom field added by the plugin<br/></h4>

        <table class="form-table stl-adsensecustom wpwoof-addfeed-top">
            <tr class="addfeed-top-field">
                <th class="addfeed-top-label">
                    This value:
                </th>
                <td class="addfeed-top-value">
                    <input type="text"   name="field_mapping[item address][value]"  value="<?php
                    echo (!empty($wpwoof_values['field_mapping']['item address']['value']) ? htmlspecialchars($wpwoof_values['field_mapping']['item address']['value'], ENT_QUOTES) : '' );
                    ?>" />
                </td>
            </tr>
        </table><?php
    }

    public function wpwoofeed_custom_attribute_input($fieldkey, $field, $wpwoof_values) {
        if (isset($wpwoof_values['field_mapping'][$fieldkey]['custom_attribute'])) {
            ?>
            <input type="text" name="field_mapping[<?php echo $fieldkey ?>][custom_attribute]" value="<?php echo $wpwoof_values['field_mapping'][$fieldkey]['custom_attribute']; ?>" class="wpwoof_mapping_attribute" />
            <?php
        }
    }

    public function renderFields($fields, $meta_keys, $meta_keys_sort, $attributes, $wpwoof_values) {
        if (is_array($fields))
            foreach ($fields as $fieldkey => $field) {
                $sCssClass = "";

                if (isset($field['dependet']))
                    continue;

                if (!empty($field['callback']) && method_exists($this, $field['callback'])) {
                    $this->{ $field['callback'] }($fieldkey, $field, $wpwoof_values);
                    continue;
                }

                if (isset($field['feed_type']))
                    $sCssClass = $this->addCssForFeed($field['feed_type']);
                if (isset($field['cssclass']))
                    $sCssClass .= " " . $field['cssclass'];

                if (!empty($field['delimiter'])) {
                    ?><hr class="wpwoof-break <?php echo $sCssClass; ?>" /><?php
                }
                if (!empty($field['header'])) {
                    ?><h4 class="wpwoofeed-section-heading <?php echo $sCssClass; ?>"><?php echo $field['header'] ?></h4><?php
                    }
                    if (!empty($field['subheader'])) {
                        ?><h4 class="<?php echo $sCssClass; ?>"><?php echo $field['subheader'] ?></h4><?php
                    }
                    if (!empty($field['headerdesc'])) {
                        ?><p class="<?php echo $sCssClass; ?>" ><?php if (!empty($field['headerdesc'])) echo $field['headerdesc']; ?></p><?php
                    }
                    /* TODO: need check this section */
                    if (isset($field['inputtype']) && $field['inputtype'] == 'checkbox') {
                        ?><label class="<?php echo $sCssClass ?>">
                        <input type="checkbox" class='wpwoof-mapping' value="1" name="field_mapping[<?php echo $fieldkey; ?>]"<?php
                    echo!empty($wpwoof_values['field_mapping'][$fieldkey]) ? " checked " : '';
                    ?> /> <?php echo $field['label']; ?>
                    </label><br><br><?php
                } elseif (isset($field['inputtype']) && $field['inputtype'] == 'text') {
                    ?>
                    <table class="form-table <?php echo $sCssClass ?> wpwoof-addfeed-top">
                        <tr class="addfeed-top-field">
                            <th class="addfeed-top-label"><?php echo $field['label']; ?>:</th>
                            <td class="addfeed-top-value">
                                <input type="text" name="field_mapping[<?php echo $fieldkey; ?>]" value="<?php echo!empty($wpwoof_values['field_mapping'][$fieldkey]) ? $wpwoof_values['field_mapping'][$fieldkey] : '' ?>">
                            </td>
                        </tr><?php if (!empty($field['desc'])) { ?>
                            <tr>
                                <td></td>
                                <td><?php echo $field['desc']; ?></td></tr><?php
                    }
                    ?></table><?php
                } else if (!isset($field['define'])) {
                    ?>
                    <table class="form-table <?php echo $sCssClass ?> wpwoof-addfeed-top">
                        <tr class="addfeed-top-field">
                            <th class="addfeed-top-label"><?php echo $field['label']; ?>:</th>
                            <td class="addfeed-top-value"><?php
                                if (!empty($field['rendervalues']) && method_exists($this, $field['rendervalues'])) {
                                    echo $this->{$field['rendervalues']}($field, $fieldkey);
                                } else {
                                    ?><select <?php
                                            if (isset($field['attr']))
                                                echo $this->showAttributes($field['attr']);
                                            ?> name="field_mapping[<?php echo $fieldkey; ?>][value]"
                                        class="wpwoof_mapping wpwoof_mapping_option"><?php
                                            $html = '';
                                            if (isset($field['custom'])) {
                                                $html = $this->showCustomOprions($field['custom'],
                                                        (empty($wpwoof_values['field_mapping'][$fieldkey]['value']) ? "" : $wpwoof_values['field_mapping'][$fieldkey]['value'])
                                                );
                                            } else {
                                                if (isset($field['woocommerce_default'])) {
                                                    if (empty($wpwoof_values['field_mapping'][$fieldkey]['value'])) {
                                                        if (empty($wpwoof_values['field_mapping']) || !is_array($wpwoof_values['field_mapping'])) {
                                                            $wpwoof_values['field_mapping'] = array();
                                                        }
                                                        if (empty($wpwoof_values['field_mapping'][$fieldkey]) || !is_array($wpwoof_values['field_mapping'][$fieldkey])) {
                                                            $wpwoof_values['field_mapping'][$fieldkey] = array();
                                                        }
                                                        $wpwoof_values['field_mapping'][$fieldkey]['value'] = '' . $field['woocommerce_default']['value'];
                                                    }
                                                } else {
                                                    $html .= '<optgroup label="">';
                                                    $html .= '<option value="">select</option>';
                                                    if (isset($field['canSetCustomValue']) && $field['canSetCustomValue'])
                                                        $html .= '<option ' . (isset($wpwoof_values['field_mapping'][$fieldkey]['value']) ? selected('custom_value', $wpwoof_values['field_mapping'][$fieldkey]['value'], false) : '') . ' value="custom_value">Custom value</option>';
                                                    $html .= '</optgroup>';
                                                }
                                                $meta_keys_remove = $meta_keys;
                                                $fieldFilter = !empty($field['filterattr']) ? $field['filterattr'] : "";
                                                foreach ($meta_keys_sort['sort'] as $sort_id => $meta_fields) {
                                                    if ($sort_id == $fieldFilter || !$fieldFilter) {
                                                        $html .= '<optgroup label="' . $meta_keys_sort['name'][$sort_id] . '">';
                                                        foreach ($meta_fields as $key) {
                                                            $value = $meta_keys[$key];
                                                            unset($meta_keys_remove[$key]);
                                                            $html .= '<option value="' . $key . '" ' . (isset($wpwoof_values['field_mapping'][$fieldkey]['value']) ? selected('' . $key, $wpwoof_values['field_mapping'][$fieldkey]['value'], false) : '') . ' >' . $value['label'] . '</option>';
                                                        }
                                                        $html .= '</optgroup>';
                                                    }
                                                }
                                                if ($fieldkey=='mpn') {
                                                    $html .= '<optgroup label="">';
//                                                    if (isset($field['canSetCustomValue']) && $field['canSetCustomValue'])
                                                        $html .= '<option ' . (isset($wpwoof_values['field_mapping'][$fieldkey]['value']) ? selected('custom_value', $wpwoof_values['field_mapping'][$fieldkey]['value'], false) : '') . ' value="custom_value">Custom value</option>';
                                                    $html .= '</optgroup>';
                                                }
                                                if (!$fieldFilter || $fieldFilter == 'attribute') {
                                                    $html .= '<optgroup label="Global Product Attributes">';
                                                    foreach ($attributes['global'] as $key => $value) {
                                                        if ($key == 'product_visibility')
                                                            continue;
                                                        $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($wpwoof_values['field_mapping'][$fieldkey]['value']) ? selected('wpwoofattr_' . $key, $wpwoof_values['field_mapping'][$fieldkey]['value'], false) : '') . ' >' . $value . '</option>';
                                                    }
                                                    $html .= '</optgroup>';

                                                    if (isset($attributes['pa']) and count($attributes['pa'])) {
                                                        $html .= '<optgroup label="Product Attributes">';
                                                        foreach ($attributes['pa'] as $key => $value) {
                                                            $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($wpwoof_values['field_mapping'][$fieldkey]['value']) ? selected('wpwoofattr_' . $key, $wpwoof_values['field_mapping'][$fieldkey]['value'], false) : '') . ' >' . $value . '</option>';
                                                        }
                                                        $html .= '</optgroup>';
                                                    }
                                                    if (isset($attributes['meta']) and count($attributes['meta'])) {
                                                        $html .= '<optgroup label="Custom Fields">';
                                                        foreach ($attributes['meta'] as $key => $value) {
                                                            $html .= '<option value="wpwoofattr_' . $value . '" ' . (isset($wpwoof_values['field_mapping'][$fieldkey]['value']) ? selected('wpwoofattr_' . $value, $wpwoof_values['field_mapping'][$fieldkey]['value'], false) : '') . ' >' . $value . '</option>';
                                                        }
                                                        $html .= '</optgroup>';
                                                    }
                                                }
                                            }
                                            echo $html;
                                            ?></select><?php
                        $this->wpwoofeed_custom_attribute_input($fieldkey, $field, $wpwoof_values);
                        if (isset($field['canSetCustomValue']) && $field['canSetCustomValue'])
                            echo '<input type="text" name="field_mapping[' . $fieldkey . '][custom_value]" value="' . (isset($wpwoof_values['field_mapping'][$fieldkey]['custom_value']) ? $wpwoof_values['field_mapping'][$fieldkey]['custom_value'] : '') . '"'
                            . ' class="wpwoof-custom-value-field" ' . (isset($wpwoof_values['field_mapping'][$fieldkey]['value']) && $wpwoof_values['field_mapping'][$fieldkey]['value'] == 'custom_value' ? 'style="display: block"' : "") . '>';
                    }
                    ?>
                            </td>
                        </tr><?php if (!empty($field['desc'])) { ?>
                            <tr>
                                <td></td>
                                <td><?php echo $field['desc']; ?></td></tr><?php
                        }
                        ?></table><?php
                    }
                }
        }
        
        function renderFieldsForDropbox($fields) {
            ?>
            <select id="extraFieldList"  class="stl-facebook stl-google wpwoof_mapping wpwoof_mapping_option"  style="width:61%; margin-right: 15px; display: inline-block;" >

            <option value="wpwoofattr_custom_extra_field"  >Custom field</option>
            <?php
            if (is_array($fields))
                foreach ($fields as $fieldkey => $field) {
                    if (isset($field['dependet']))
                        continue;
                    if (!empty($field['callback']))
                        continue;

                    echo '<option value="wpwoofattr_' . $fieldkey . '"  >' . (!empty($field['header']) ? $field['header'] : $field['label']) .'&nbsp; &nbsp;'. $this->getHelpLinks($field) . '</option>';
                    if ($fieldkey == 'unit_pricing_base_measure' && (!isset($_GET['page']) || $_GET['page'] != 'wpwoof-settings'))
                        echo '<option value="wpwoofattr_installmentmonths"  >Installment&nbsp; &nbsp;G</option>';
                }
            ?>

            </select>
            <script>jQuery("#extraFieldList").fastselect();</script>
        <?php
    }

    function renderExtraFieldsForMapping($fieldkey, $aValues) {
            $html = '<select  name="'.($fieldkey=='wpwoof-def'?'wpwoof-def':'extra['.$fieldkey.']').'[value]" class="wpwoof_mapping wpwoof_mapping_option">';
            if ($fieldkey == 'mpn') $html .= '<optgroup label="ID\'s">'
                    . '<option value="id" '.(selected(!isset ($aValues['value']) || 'id'== $aValues['value'], true, false)).'>ID</option>'
                    . '<option value="_sku" '.(isset($aValues['value']) ? selected('_sku', $aValues['value'], false) : '').'>SKU</option></optgroup>';
             $html .= '<optgroup label="">';
            if ($fieldkey != 'mpn') $html .= '<option value="">select</option>';
//                                if (isset($field['canSetCustomValue']) && $field['canSetCustomValue']) 
            $html .= '<option ' . (isset($aValues['value']) ? selected('custom_value', $aValues['value'], false) : '') . ' value="custom_value">Custom value</option>';
            $html .= '</optgroup>';
            if ($fieldkey == 'gtin') $html .= '<optgroup label="ID\'s">'
                    . '<option value="id" '.(selected(!isset ($aValues['value']) || 'id'== $aValues['value'], true, false)).'>ID</option>'
                    . '<option value="_sku" '.(isset($aValues['value']) ? selected('_sku', $aValues['value'], false) : '').'>SKU</option></optgroup>';
            $html .= '<optgroup label="Global Product Attributes">';
            foreach ($this->_attributes['global'] as $key => $value) {
                if ($key == 'product_visibility')
                    continue;
                $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($aValues['value']) ? selected('wpwoofattr_' . $key, $aValues['value'], false) : '') . ' >' . $value . '</option>';
            }
            $html .= '</optgroup>';
            if (isset($this->_attributes['pa']) and count($this->_attributes['pa'])) {
                $html .= '<optgroup label="Product Attributes">';
                foreach ($this->_attributes['pa'] as $key => $value) {
                    $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($aValues['value']) ? selected('wpwoofattr_' . $key, $aValues['value'], false) : '') . ' >' . $value . '</option>';
                }
                $html .= '</optgroup>';
            }
            if (isset($this->_attributes['meta']) and count($this->_attributes['meta'])) {
                $html .= '<optgroup label="Custom Fields">';
                foreach ($this->_attributes['meta'] as $key => $value) {
                    $html .= '<option value="wpwoofattr_' . $value . '" ' . (isset($aValues['value']) ? selected('wpwoofattr_' . $value, $aValues['value'], false) : '') . ' >' . $value . '</option>';
                }
                $html .= '</optgroup>';
            }
            $html .= '</select>';
            return $html;
        }

        function renderFieldsForMappingOLD($fields, $feed_type, $aValues) {
            /* trace( $aValues,1); */
            ?>
            <table class="form-table manage_global_settings_block">
                <tr>
                    <?php
                    if (is_array($fields))
                        foreach ($fields as $fieldkey => $field) {
                            ?><?php
                            if (isset($field['dependet']) || !in_array($feed_type, $field['feed_type']))
                                continue;
                            if (!empty($field['callback']))
                                continue;

                            if (!empty($field['callback']) && method_exists($this, $field['callback'])) {
                                $this->{ $field['callback'] }($fieldkey, $field, $aValues);
                                continue;
                            }
                            if (!empty($field['delimiter'])) {
                                ?><tr><td colspan="2"><hr class="wpwoof-break" /></td></tr><?php
                                }
                                ?><tr>
                            <th><?php echo!empty($field['header']) ? $field['header'] : $field['label']; ?></th><?php
                            if (!empty($field['desc'])) {
                                ?><td></td><tr><td colspan="2"><?php echo $field['desc']; ?></td></tr><?php
                                            }
                                            if (isset($field['define']) && $field['define'] === true)
                                                continue;
                                            ?><td><select <?php
                                    if (isset($field['attr']))
                                        echo $this->showAttributes($field['attr']);
                                    ?> name="<?php echo $feed_type; ?>[<?php echo $fieldkey; ?>][value]"
                                class="wpwoof_mapping wpwoof_mapping_option"><?php
                                    $html = '';

                                    if (isset($field['woocommerce_default'])) {
                                        if (empty($aValues[$fieldkey]['value'])) {
                                            if (empty($aValues) || !is_array($aValues)) {
                                                $aValues = array();
                                            }
                                            if (empty($aValues[$fieldkey]) || !is_array($aValues[$fieldkey])) {
                                                $wpwoof_values[$fieldkey] = array();
                                            }
                                            $aValues[$fieldkey]['value'] = '' . $field['woocommerce_default']['value'];
                                        }
                                    } else {
                                        $html .= '<optgroup label="">';
                                        $html .= '<option value="">select</option>';
                                        if (isset($field['canSetCustomValue']) && $field['canSetCustomValue'])
                                            $html .= '<option ' . (isset($aValues[$fieldkey]['value']) ? selected('custom_value', $aValues[$fieldkey]['value'], false) : '') . ' value="custom_value">Custom value</option>';
                                        $html .= '</optgroup>';
                                    }
                                    /*
                                      $meta_keys_remove = $this->_meta_keys;
                                      foreach ($this->_meta_keys_sort['sort'] as $sort_id => $meta_fields) {
                                      $html .= '<optgroup label="' . $this->_meta_keys_sort['name'][$sort_id] . '">';
                                      foreach ($meta_fields as $key) {
                                      $value = $this->_meta_keys[$key];
                                      unset($meta_keys_remove[$key]);
                                      $html .= '<option value="' . $key . '" ' . (isset($aValues[$fieldkey]['value']) ? selected('' . $key, $aValues[$fieldkey]['value'], false) : '') . ' >' . $value['label'] . '</option>';
                                      }
                                      $html .= '</optgroup>';
                                      }
                                     */
                                    $html .= '<optgroup label="Global Product Attributes">';
                                    foreach ($this->_attributes['global'] as $key => $value) {
                                        if ($key == 'product_visibility')
                                            continue;
                                        $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($aValues[$fieldkey]['value']) ? selected('wpwoofattr_' . $key, $aValues[$fieldkey]['value'], false) : '') . ' >' . $value . '</option>';
                                    }
                                    $html .= '</optgroup>';
                                    if (isset($this->_attributes['pa']) and count($this->_attributes['pa'])) {
                                        $html .= '<optgroup label="Product Attributes">';
                                        foreach ($this->_attributes['pa'] as $key => $value) {
                                            $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($aValues[$fieldkey]['value']) ? selected('wpwoofattr_' . $key, $aValues[$fieldkey]['value'], false) : '') . ' >' . $value . '</option>';
                                        }
                                        $html .= '</optgroup>';
                                    }
                                    if (isset($this->_attributes['meta']) and count($this->_attributes['meta'])) {
                                        $html .= '<optgroup label="Custom Fields">';
                                        foreach ($this->_attributes['meta'] as $key => $value) {
                                            $html .= '<option value="wpwoofattr_' . $value . '" ' . (isset($aValues[$fieldkey]['value']) ? selected('wpwoofattr_' . $value, $aValues[$fieldkey]['value'], false) : '') . ' >' . $value . '</option>';
                                        }
                                        $html .= '</optgroup>';
                                    }
                                    echo $html;
                                    ?></select><?php
                    echo $this->getHelpLinks($field);

                    if (isset($field['canSetCustomValue']) && $field['canSetCustomValue'])
                        echo '<input type="text" name="' . $feed_type . '[' . $fieldkey . '][custom_value]" value="' . (isset($aValues[$fieldkey]['custom_value']) ? $aValues[$fieldkey]['custom_value'] : '') . '"'
                        . ' class="wpwoof-custom-value-field" ' . ($aValues[$fieldkey]['value'] == 'custom_value' ? 'style="display: block"' : "") . '>';
                }
            ?></td></tr></tbody><?php
            ?></table>
            <script>jQuery("select[name*='<?= $feed_type ?>[']").fastselect();</script>
            <?php
        }

        function getHelpLinks($field) {
            $out = '';
            if (!empty($field['feed_type'])) {
                $needSep = false;
//                $out .= '&nbsp; &nbsp;';
                if (in_array('facebook', $field['feed_type'])) {
                    $out .= 'FB';
                    $needSep = true;
                }
                if (in_array('google', $field['feed_type'])) {
                    if ($needSep)
                        $out .= ' | ';
                    $needSep = true;

                    if (!empty($field['helplink'])) {
                        $out .= '<a target="_blank" href="' . $field['helplink'] . '">G</a>';
                    } else {
                        $out .= 'G';
                    }
                }
                if (in_array('adsensecustom', $field['feed_type'])) {
                    if ($needSep)
                        $out .= ' | ';
                    $needSep = true;

                    if (!empty($field['helplink'])) {
                        $out .= '<a target="_blank" href="' . $field['helplink'] . '">GA</a>';
                    } else {
                        $out .= 'GA';
                    }
                }
            }
            return $out;
        }

        function renderFieldsToTab($fields, $feed_type, $aValues) {

            $this->aValues = $aValues;
            if (is_array($fields))
                foreach ($fields as $fieldkey => $field) {
                    $sCssClass = "stl_" . $feed_type;
                    if (isset($field['dependet']) || !in_array($feed_type, $field['feed_type']))
                        continue;

                    if (!empty($field['callback']) && method_exists($this, $field['callback'])) {
                        $this->{ $field['callback'] }($fieldkey, $field, !empty($aValues[$fieldkey]) ? $aValues[$fieldkey] : "");
                        continue;
                    }
                    if (!empty($field['delimiter'])) {
                        ?><hr class="wpwoof-break <?php echo $sCssClass; ?>" /><?php } ?>
                    <p class="form-row custom_field_type woof-field-row">
                    <?php
                    $sAttr = ' name="wpfoof-box-media[' . $feed_type . '][' . $fieldkey . '][value]" id="_value-' . $fieldkey . '" class="select short  ' . $sCssClass . '" ';
                    ?>
                        <label class="woof-panel-label <?php echo $sCssClass; ?>" for="<?php echo $fieldkey; ?>-value"><?php echo!empty($field['header']) ? $field['header'] : $field['label']; ?></label><?php
                    if (!empty($field['desc'])) {
                        ?><p class="<?php echo $sCssClass; ?>"><b><?php echo $field['desc']; ?></b></p><?php
                    }
                    if (isset($field['define']) && $field['define'] === true) {
                        
                    } else if (isset($field['custom'])) {
                        ?><select <?php echo $sAttr; ?>   ><?php
                        echo $this->showCustomOprions($field['custom'], empty($aValues[$fieldkey]['value']) ? '' : $aValues[$fieldkey]['value'] );
                        ?></select><?php
                    } else {
                        ?>
                        <input type="<?php echo!empty($field['inputtext']) ? $field['inputtext'] : 'text' ?>"   value="<?php echo!empty($aValues[$fieldkey]['value']) ? htmlspecialchars($aValues[$fieldkey]['value'], ENT_QUOTES) : ''; ?>" <?php echo $sAttr; ?> />
                        <?php
                    }
                    echo $this->getHelpLinks($field);
                    ?></p><?php
                }
        }

    }
    