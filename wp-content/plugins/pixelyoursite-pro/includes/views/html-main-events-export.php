<?php

use PixelYourSite\CustomEventFactory;

header("Content-type: application/json; charset=utf-8");
header('Content-Disposition: attachment; filename="pyspro_export.json"');

$result = array();
$events = array();
foreach (CustomEventFactory::get() as $event) {
    $el = array(
        "title"=> $event->getTitle(),
        "enabled" => $event->isEnabled()
    );
    $el = array_merge($el,$event->getAllData());
    $events[] = $el;
}
$result['events'] = $events;
$result['site_url'] = site_url();
echo json_encode($result);