<?php
namespace PixelYourSite;

abstract class EventsFactory {


    static function getSlug(){
        return "";
    }
    abstract function getCount();
    abstract function isEnabled();
    abstract function getOptions();

    abstract  function getEvents();
    /**
     * Check is event ready for fire
     * @param $event
     * @return bool
     */
    abstract function isReadyForFire($event);

    /**
     * @param String $event
     * @return SingleEvent
     */
    abstract function getEvent($event);


    function generateEvents() {
        if(!$this->isEnabled())  return array();

        $eventsList = array();
        foreach ($this->getEvents() as $eventName) {

            if($this->isReadyForFire($eventName)) {
                $events = $this->getEvent($eventName);
                if($events == null) continue;
                if(!is_array($events))  $events = array($events); // some type of events can return array

                foreach ($events as $event) {
                    foreach ( PYS()->getRegisteredPixels() as $pixel ) {
                        if(method_exists($pixel,'generateEvents')) {
                            $pixelEvents =  $pixel->generateEvents( $event );
                            foreach ($pixelEvents as $pixelEvent) {
                                if(apply_filters("pys_validate_pixel_event",true,$pixelEvent,$pixel)) {
                                    $eventsList[$pixel->getSlug()][] = $pixelEvent;
                                }
                            }
                        } else {
                            // deprecate
                            $pixel_event = clone $event;
                            $isSuccess = $pixel->addParamsToEvent( $pixel_event );
                            if(!$isSuccess || !apply_filters("pys_validate_pixel_event",true,$pixel_event,$pixel)) continue;
                            $eventsList[$pixel->getSlug()][] = $pixel_event;
                        }

                    }
                }
            }
        }

        return $eventsList;
    }
}