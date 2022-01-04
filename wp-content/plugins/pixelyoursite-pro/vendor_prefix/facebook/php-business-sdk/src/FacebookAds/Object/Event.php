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
use PYS_PRO_GLOBAL\FacebookAds\Object\Fields\EventFields;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventCategoryValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventEventStateFilterValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventOnlineEventFormatValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventTimeFilterValues;
use PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventTypeValues;
/**
 * This class is auto-generated.
 *
 * For any issues or feature requests related to this class, please let us know
 * on github and we'll fix in our codegen framework. We'll not be able to accept
 * pull request for this class.
 *
 */
class Event extends \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject
{
    /**
     * @return EventFields
     */
    public static function getFieldsEnum()
    {
        return \PYS_PRO_GLOBAL\FacebookAds\Object\Fields\EventFields::getInstance();
    }
    protected static function getReferencedEnums()
    {
        $ref_enums = array();
        $ref_enums['Category'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventCategoryValues::getInstance()->getValues();
        $ref_enums['OnlineEventFormat'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventOnlineEventFormatValues::getInstance()->getValues();
        $ref_enums['Type'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventTypeValues::getInstance()->getValues();
        $ref_enums['EventStateFilter'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventEventStateFilterValues::getInstance()->getValues();
        $ref_enums['TimeFilter'] = \PYS_PRO_GLOBAL\FacebookAds\Object\Values\EventTimeFilterValues::getInstance()->getValues();
        return $ref_enums;
    }
    public function getComments(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/comments', new \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getFeed(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/feed', new \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getLiveVideos(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/live_videos', new \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function createLiveVideo(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array('content_tags' => 'list<string>', 'description' => 'string', 'enable_backup_ingest' => 'bool', 'encoding_settings' => 'string', 'fisheye_video_cropped' => 'bool', 'front_z_rotation' => 'float', 'is_audio_only' => 'bool', 'is_spherical' => 'bool', 'live_encoders' => 'list<string>', 'original_fov' => 'unsigned int', 'privacy' => 'string', 'projection' => 'projection_enum', 'published' => 'bool', 'schedule_custom_profile_image' => 'file', 'spatial_audio_format' => 'spatial_audio_format_enum', 'status' => 'status_enum', 'stereoscopic_mode' => 'stereoscopic_mode_enum', 'stop_on_delete_stream' => 'bool', 'stream_type' => 'stream_type_enum', 'title' => 'string');
        $enums = array('projection_enum' => array('CUBEMAP', 'EQUIRECTANGULAR', 'HALF_EQUIRECTANGULAR'), 'spatial_audio_format_enum' => array('ambiX_4'), 'status_enum' => array('LIVE_NOW', 'SCHEDULED_CANCELED', 'SCHEDULED_LIVE', 'SCHEDULED_UNPUBLISHED', 'UNPUBLISHED'), 'stereoscopic_mode_enum' => array('LEFT_RIGHT', 'MONO', 'TOP_BOTTOM'), 'stream_type_enum' => array('AMBIENT', 'REGULAR'));
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_POST, '/livevideos', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPhotos(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/photos', new \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPicture(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/picture', new \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getPosts(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/posts', new \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getRoles(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/roles', new \PYS_PRO_GLOBAL\FacebookAds\Object\Profile(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\Profile::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getTicketTiers(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/ticket_tiers', new \PYS_PRO_GLOBAL\FacebookAds\Object\AbstractCrudObject(), 'EDGE', array(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getVideos(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/videos', new \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode(), 'EDGE', \PYS_PRO_GLOBAL\FacebookAds\Object\NullNode::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
    public function getSelf(array $fields = array(), array $params = array(), $pending = \false)
    {
        $this->assureId();
        $param_types = array();
        $enums = array();
        $request = new \PYS_PRO_GLOBAL\FacebookAds\ApiRequest($this->api, $this->data['id'], \PYS_PRO_GLOBAL\FacebookAds\Http\RequestInterface::METHOD_GET, '/', new \PYS_PRO_GLOBAL\FacebookAds\Object\Event(), 'NODE', \PYS_PRO_GLOBAL\FacebookAds\Object\Event::getFieldsEnum()->getValues(), new \PYS_PRO_GLOBAL\FacebookAds\TypeChecker($param_types, $enums));
        $request->addParams($params);
        $request->addFields($fields);
        return $pending ? $request : $request->execute();
    }
}
