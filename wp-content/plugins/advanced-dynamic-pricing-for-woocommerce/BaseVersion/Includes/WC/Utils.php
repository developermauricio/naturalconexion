<?php

namespace ADP\BaseVersion\Includes\WC;

class Utils
{
    /**
     * @param array $needleNotice
     * @param array $newNotice
     */
    public static function replaceWcNotice($needleNotice, $newNotice)
    {
        if (!is_array($needleNotice) || !is_array($newNotice)) {
            return;
        }

        if (!function_exists("wc_get_notices")) {
            return;
        }

        $needleNotice = array(
            'type' => isset($needleNotice['type']) ? $needleNotice['type'] : null,
            'text' => isset($needleNotice['text']) ? $needleNotice['text'] : "",
        );

        $newNotice = array(
            'type' => isset($newNotice['type']) ? $newNotice['type'] : null,
            'text' => isset($newNotice['text']) ? $newNotice['text'] : "",
        );


        $newNotices = array();
        foreach (wc_get_notices() as $type => $notices) {
            if (!isset($newNotices[$type])) {
                $newNotices[$type] = array();
            }

            foreach ($notices as $loopNotice) {
                if (!empty($loopNotice['notice'])
                    && $needleNotice['text'] === $loopNotice['notice']
                    && (!$needleNotice['type'] || $needleNotice['type'] === $type)
                ) {
                    if ($newNotice['type'] === null) {
                        $newNotice['type'] = $type;
                    }

                    if (!isset($newNotices[$newNotice['type']])) {
                        $newNotices[$newNotice['type']] = array();
                    }

                    $newNotices[$newNotice['type']][] = array(
                        'notice' => $newNotice['text'],
                        'data' => array(),
                    );

                    continue;
                } else {
                    $newNotices[$type][] = $loopNotice;
                }
            }
        }
        wc_set_notices($newNotices);
    }
}
