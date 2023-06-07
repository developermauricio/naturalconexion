<?php

namespace ADP\HighLander;

use ADP\HighLander\Queries\TagFilterQuery;
use ADP\HighLander\Queries\ClassMethodFilterQuery;
use ADP\HighLander\Queries\FunctionFilterQuery;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class HighLander
{
    /**
     * @var array<int,ClassMethodFilterQuery>|array<int,FunctionFilterQuery>|array<int,TagFilterQuery>
     */
    protected $queries;

    protected $removed = array();

    public function __construct()
    {
        $this->queries = array();
        $this->removed = array();
    }

    /**
     * @param array<int,ClassMethodFilterQuery>|array<int,FunctionFilterQuery>|array<int,TagFilterQuery> $queries
     */
    public function setQueries($queries)
    {
        $this->queries = $queries;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if (count($this->queries) === 0) {
            return true;
        }

        global $wp_filter;
        /** @var $wp_filter \WP_Hook[] */

        if (empty($wp_filter)) {
            return true;
        }

        $this->removed = array();

        foreach ($this->queries as $query) {
            $tag = null;
            if ($query instanceof ClassMethodFilterQuery || $query instanceof FunctionFilterQuery) {
                $tag = $query->getTag();
            }

            if (isset($tag)) {
                if (isset($wp_filter[$tag])) {
                    $hookObj = &$wp_filter[$tag];
                    $this->executeHookObj($hookObj, $tag, $query);
                }
            } else {
                foreach ($wp_filter as $tag => &$hookObj) {
                    $this->executeHookObj($hookObj, $tag, $query);
                }
            }
        }

        return true;
    }

    /**
     * @param \WP_Hook $hookObj
     * @param string $tag
     * @param ClassMethodFilterQuery|FunctionFilterQuery $query
     */
    protected function executeHookObj(&$hookObj, $tag, $query)
    {
        $newCallbacks     = array();
        $removedCallbacks = array();

        foreach ($hookObj->callbacks as $priority => $callbacks) {
            $priority_callbacks       = array();
            $removedPriorityCallbacks = array();

            foreach ($callbacks as $idx => $callback) {
                $details = new CallBackDetails($callback);

                $save = true;
                if ($query instanceof TagFilterQuery) {
                    /** @var TagFilterQuery $query */
                    if ($query->isAction($query::ACTION_REMOVE_OTHERS)) {
                        $save = in_array($tag, $query->getList());
                    } elseif ($query->isAction($query::ACTION_REMOVE_ALL_IN_TAG)) {
                        $save = ! in_array($tag, $query->getList());
                    }
                } elseif ($query instanceof ClassMethodFilterQuery) {
                    /** @var ClassMethodFilterQuery $query */
                    if ($details->isType($details::TYPE_COMPLETE)) {
                        if ( ! $save && $query->isAction($query::ACTION_SAVE)) {
                            $save = in_array($details->getFunc(), $query->getList());
                        } elseif ($save && $query->isAction($query::ACTION_REMOVE)) {
                            $save = ! in_array($details->getFunc(), $query->getList());
                        }
                    }
                } elseif ($query instanceof FunctionFilterQuery) {
                    /** @var FunctionFilterQuery $query */
                    if ($details->isType($details::TYPE_FUNCTION) || $details->isType($details::TYPE_CLOSURE)) {
                        if ( ! $save && $query->isAction($query::ACTION_SAVE)) {
                            $save = in_array($details->getFunc(), $query->getList());
                        } elseif ($save && $query->isAction($query::ACTION_REMOVE)) {
                            $save = ! in_array($details->getFunc(), $query->getList());
                        }
                    }
                }

                if ($save) {
                    $priority_callbacks[$idx] = $callback;
                } else {
                    $removedPriorityCallbacks[$idx] = $callback;
                }
            }

            if ($priority_callbacks) {
                $newCallbacks[$priority] = $priority_callbacks;
            }

            if ($removedPriorityCallbacks) {
                $removedCallbacks[$priority] = $removedPriorityCallbacks;
            }
        }

        if (isset($this->removed[$tag])) {
            foreach ($this->removed[$tag] as $priority => $callbacks) {
                $priority_callbacks = array();

                foreach ($callbacks as $idx => $callback) {
                    $details = new CallBackDetails($callback);

                    $save = false;
                    if ($query instanceof ClassMethodFilterQuery) {
                        /** @var ClassMethodFilterQuery $query */
                        if ($details->isType($details::TYPE_COMPLETE)) {
                            $save = in_array($details->getFunc(),
                                    $query->getList()) && $query->isAction($query::ACTION_SAVE);
                        }
                    } elseif ($query instanceof FunctionFilterQuery) {
                        /** @var FunctionFilterQuery $query */
                        if ($details->isType($details::TYPE_FUNCTION) || $details->isType($details::TYPE_CLOSURE)) {
                            $save = in_array($details->getFunc(),
                                    $query->getList()) && $query->isAction($query::ACTION_SAVE);
                        }
                    }

                    if ($save) {
                        $priority_callbacks[$idx] = $callback;
                        unset($this->removed[$tag][$priority][$idx]);
                    }
                }

                if ($priority_callbacks) {
                    $newCallbacks[$priority] = $priority_callbacks;
                }
            }
        }

        $hookObj->callbacks = $newCallbacks;
        if ($removedCallbacks) {
            if ( ! isset($this->removed[$tag])) {
                $this->removed[$tag] = array();
            }

            foreach ($removedCallbacks as $prior => $callback) {
                $this->removed[$tag][$prior] = $callback;
            }
        }
    }

    public function restore()
    {
        global $wp_filter;
        /** @var $wp_filter \WP_Hook[] */

        if ($wp_filter) {
            foreach ($this->removed as $tag => $callbacks) {
                if (isset($wp_filter[$tag])) {
                    $hookObj = &$wp_filter[$tag];
                } else {
                    $hookObj = new \WP_Hook();
                }

                foreach ($callbacks as $prior => $callback) {
                    $hookObj->callbacks[$prior] = $callback;
                }
                ksort($hookObj->callbacks, SORT_NUMERIC);
            }
        }
        $this->removed = array();

        return true;
    }

}
