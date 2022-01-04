<?php

/**
 * Copyright (c) 2015-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace PYS_PRO_GLOBAL\FacebookAds\Object;

use PYS_PRO_GLOBAL\FacebookAds\ApiRequest;
use PYS_PRO_GLOBAL\FacebookAds\Cursor;
use PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface;
use PYS_PRO_GLOBAL\FacebookAds\TypeChecker;
use PYS_PRO_GLOBAL\FacebookAds\Object\Fields\NativeOfferFields;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\NativeOfferBarcodeTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\NativeOfferLocationTypeValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\NativeOfferUniqueCodesFileCodeTypeValues;
/**
 * This class is auto-generated.
 *
 * For any issues or feature requests related to this class, please let us know
 * on github and we'll fix in our codegen framework. We'll not be able to accept
 * pull request for this class.
 *
 */
class NativeOffer extends \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject
{
    /**
     * @return NativeOfferFields
     */
    public static function getFieldsEnum()
    {
        return \PYS_PRO_GLOBAL\FacebookAds\Object\Fields\NativeOfferFields::getInstance();
    }
    protected static function getReferencedEnums()
    {
        $ref_enums = array();
        $ref_enums['UniqueCodesFileCodeType'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\NativeOfferUniqueCodesFileCodeTypeValues::getInstance()->getValues();
        $ref_enums['BarcodeType'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\NativeOfferBarcodeTypeValues::getInstance()->getValues();
        $ref_enums['LocationType'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\NativeOfferLocationTypeValues::getInstance()->getValues();
        return $ref_enums;
    }
    public function createCode(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('file' => 'file', 'unique_codes_file_code_type' => 'unique_codes_file_code_type_enum');
        $enums = array('unique_codes_file_code_type_enum' => \PYS_PRO_GLOBAL\FacebookAds\Object\Values\NativeOfferUniqueCodesFileCodeTypeValues::getInstance()->getValues());
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/codes', new \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOffer(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOffer::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createNativeOfferView(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('ad_account' => 'string', 'ad_image_hashes' => 'list<string>', 'carousel_captions' => 'list<string>', 'carousel_data' => 'list<Object>', 'carousel_links' => 'list<string>', 'deeplinks' => 'list<string>', 'image_crops' => 'list<map>', 'message' => 'string', 'photos' => 'list<string>', 'place_data' => 'Object', 'published' => 'bool', 'published_ads' => 'bool', 'urls' => 'list<string>', 'videos' => 'list<string>');
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/nativeofferviews', new \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOffer(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOffer::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getViews(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/views', new \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOfferView(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOfferView::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getSelf(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/', new \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOffer(), 'NODE', \PYS_PRO_GLOBAL\FacebookAds\Object\NativeOffer::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
}
