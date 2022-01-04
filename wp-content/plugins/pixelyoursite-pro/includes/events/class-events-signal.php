<?php
namespace PixelYourSite;

/*
 * Signal Events we will fire this event in order to capture all actions  like clicks, video
views, downloads, comments, forms.
 * */

class EventsSignal extends EventsFactory {
    private static $_instance;

    private $events = array(
        'signal_click' , //Params: text (the current link_text),target_URL
        'signal_watch_video',//Params: video_title, video_id, video_typetrigger : Video Play, Video 25%, Video 50%,Video 90%, Video 100%
        'signal_adsense'        ,//Params: No specific parameters
        'signal_user_signup'   ,//Params:No specific parameters When a user account is created.
        'signal_page_scroll'    ,//Params: trigger: the percent that triggers the event
        'signal_time_on_page' ,//Params: No specific parameters trigger: the time delay that triggers the event
        'signal_tel'            ,//Params:No specific parameters
        'signal_email',//Params:No specific parameters
        'signal_form'           ,//Params: form_class, form_id, text (the current form_submit_label)
        'signal_download'  ,//Params: download_type, download_name, download_URL
        'signal_comment'   //Params: No specific parameters
    );

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    private function __construct() {

    }

    static function getSlug() {
        return "signal";
    }

    function getEvents() {
        return $this->events;
    }

    function getCount() {
        $count = 0;
        if($this->isEnabled()) {
            foreach ($this->events as $event) {
                if(PYS()->getOption($event."_enabled")) {
                    $count++;
                }
            }
        }
        return $count;
    }


    function isEnabled() {
        return PYS()->getOption("signal_events_enabled");
    }

    // return option for js part
    function getOptions() {
        return array(
            ""
        );
    }

    /**
     * Check is event ready for fire
     * @param $event
     * @return bool
     */
    function isReadyForFire($event) {

        if(!$this->isEnabled()) return false;

        if(!in_array($event,$this->events)) return false;

        switch($event) {
            case 'signal_user_signup' : {
                if ( PYS()->getOption( 'signal_user_signup_enabled' ) && $user_id = get_current_user_id() ) {
                    if ( get_user_meta( $user_id, 'pys_complete_registration', true ) ) {
                        return true;
                    }
                }
                return false;
            }
            default: {
                return PYS()->getOption( $event."_enabled");
            }
        }
    }

    /**
     * @param String $event
     * @return SingleEvent
     */
    function getEvent($event) {
        $payload = array("name"=>'Signal');
        $params = array();
        switch ($event) {
            case "signal_download": {
                $params['event_action'] = 'Download';
                $payload["extensions"] = PYS()->getOption( 'download_event_extensions' );
                $item =  new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_page_scroll": {

                $payload["scroll_percent"] = PYS()->getOption( 'signal_page_scroll_value' );
                $params['event_action'] = 'Scroll '. $payload["scroll_percent"]."%";
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_time_on_page": {
                $payload["time_on_page"] = PYS()->getOption( 'signal_time_on_page_value' );
                $params['event_action'] = 'Time on page '.$payload["time_on_page"]." seconds";
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_user_signup": {
                $params['event_action'] = 'User Signup';
                $item = new SingleEvent($event,EventTypes::$STATIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }

            case "signal_watch_video": {
                $params['event_action'] = 'Video';
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_adsense": {
                $params['event_action'] = 'Adsense';
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_tel": {
                $params['event_action'] = 'Tel';
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_email": {
                $params['event_action'] = 'Email';
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_form": {
                $params['event_action'] = 'Form';
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }
            case "signal_comment": {
                $params['event_action'] = 'Comment';
                $item = new SingleEvent($event,EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                $item->addParams($params);
                return $item;
            }

            default:
            {
                $item = new SingleEvent($event, EventTypes::$DYNAMIC,self::getSlug());
                $item->addPayload($payload);
                return $item;
            }
        }
    }

}

/**
 * @return EventsSignal
 */
function EventsSignal() {
    return EventsSignal::instance();
}

EventsSignal();