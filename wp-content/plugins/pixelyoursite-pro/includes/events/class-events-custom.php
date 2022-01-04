<?php
namespace PixelYourSite;
class EventsCustom extends EventsFactory {
    private static $_instance;
    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    private function __construct() {

    }

    static function getSlug() {
        return "custom";
    }

    function getEvents(){
        return CustomEventFactory::get( 'active' );
    }

    function getCount()
    {
        if(!$this->isEnabled()) {
            return 0;
        }
        return count($this->getEvents());
    }

    function isEnabled()
    {
        return PYS()->getOption( 'custom_events_enabled' );
    }

    function getOptions()
    {
        return array();
    }

    /**
     * @param CustomEvent $event
     * @return bool
     */
    function isReadyForFire($event)
    {
        switch ($event->getTriggerType()) {
            case 'post_type' : {
                return $event->getPostTypeValue() == get_post_type();
            }
            case 'page_visit': {
                $triggers = $event->getPageVisitTriggers();
                return !empty( $triggers ) && compareURLs( $triggers );
            }

            case 'url_click': {
                $triggers = $event->getURLClickTriggers();
                $urlFilters = $event->getURLFilters();
                return !empty( $triggers )&& (empty( $urlFilters ) || compareURLs( $urlFilters ));
            }

            case 'css_click': {
                $triggers = $event->getCSSClickTriggers();
                $urlFilters = $event->getURLFilters();
                return !empty( $triggers )&& (empty( $urlFilters ) || compareURLs( $urlFilters ));
            }

            case 'css_mouseover': {
                $triggers = $event->getCSSMouseOverTriggers();
                $urlFilters = $event->getURLFilters();
                return !empty( $triggers )&& (empty( $urlFilters ) || compareURLs( $urlFilters ));
            }

            case 'scroll_pos': {
                $triggers = $event->getScrollPosTriggers();
                $urlFilters = $event->getURLFilters();
                return !empty( $triggers ) && (empty( $urlFilters ) || compareURLs( $urlFilters ));
            }
        }
        return false;
    }
    /**
     * @param CustomEvent $event
     * @return PYSEvent
     */
    function getEvent($event)
    {
        $payload = array('trigger_type' => $event->getTriggerType());
        $eventObject = null;

        switch ($event->getTriggerType()) {
            case 'post_type' :
            case 'page_visit': {
                $singleEvent = new SingleEvent('custom_event',EventTypes::$STATIC,self::getSlug());
                $singleEvent->args = $event;
                $eventObject = $singleEvent;
            } break;

            case 'url_click': {
                foreach ($event->getURLClickTriggers() as $trigger)
                    $payload['trigger_value'] = $trigger;
                $singleEvent = new SingleEvent('custom_event',EventTypes::$TRIGGER,self::getSlug());
                $singleEvent->addPayload($payload);
                $singleEvent->args = $event;
                $eventObject = $singleEvent;
            } break;
            case 'css_click': {
                foreach ($event->getCSSClickTriggers() as $trigger)
                    $payload['trigger_value'][] = $trigger['value'];
                $singleEvent = new SingleEvent('custom_event',EventTypes::$TRIGGER,self::getSlug());
                $singleEvent->args = $event;
                $singleEvent->addPayload($payload);
                $eventObject = $singleEvent;
            }break;
            case 'css_mouseover': {
                foreach ($event->getCSSMouseOverTriggers() as $trigger)
                    $payload['trigger_value'][] = $trigger['value'];
                $singleEvent = new SingleEvent('custom_event',EventTypes::$TRIGGER,self::getSlug());
                $singleEvent->args = $event;
                $singleEvent->addPayload($payload);
                $eventObject = $singleEvent;
            } break;
            case 'scroll_pos': {
                foreach ($event->getScrollPosTriggers() as $trigger)
                    $payload['trigger_value'][] = $trigger['value'];
                $singleEvent = new SingleEvent('custom_event',EventTypes::$TRIGGER,self::getSlug());
                $singleEvent->args = $event;
                $singleEvent->addPayload($payload);
                $eventObject = $singleEvent;
            } break;
        }

        if(isset($eventObject) && $event->hasTimeWindow()) {
            $eventObject->addPayload(["hasTimeWindow" => $event->hasTimeWindow()]);
            $eventObject->addPayload(["timeWindow" => $event->getTimeWindow()]);
        }

        if($event->getDelay() > 0) {
            $eventObject->addPayload(["delay" => $event->getDelay()]);
        }

        return $eventObject;
    }
}
/**
 * @return EventsCustom
 */
function EventsCustom() {
    return EventsCustom::instance();
}

EventsCustom();