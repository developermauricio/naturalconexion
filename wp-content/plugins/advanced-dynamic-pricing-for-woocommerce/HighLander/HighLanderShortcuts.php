<?php

namespace ADP\HighLander;

use ADP\HighLander\Queries\ClassMethodFilterQuery;
use ADP\HighLander\Queries\FunctionFilterQuery;
use ADP\HighLander\Queries\TagFilterQuery;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class HighLanderShortcuts
{
    /**
     * @param array<int,string> $hooks
     * @param callable $callback
     * @param array ...$args
     *
     * @return mixed
     */
    public static function processWithoutHooks($hooks, $callback, ...$args)
    {
        if ( ! is_callable($callback) && ! isset($callback[0], $callback[1])) {
            return null;
        }

        $highLander = new HighLander();
        $query      = new TagFilterQuery();
        $query->setList($hooks)->setAction($query::ACTION_REMOVE_ALL_IN_TAG);
        $highLander->setQueries(array($query));

        global $wp_filter;
        /** @var $wp_filter \WP_Hook[] */

        $tmpWpFilter = $wp_filter;
        $highLander->execute();
        $result    = call_user_func_array($callback, $args);
        $wp_filter = $tmpWpFilter;

        return $result;
    }


    public static function thereCanBeOnlyOne($list)
    {
        $highLander = new HighLander();

        $queries = array();
        foreach ($list as $tag => $methods) {
            $classMethodsList = array();
            $functionsList    = array();
            foreach ($methods as $method) {
                if (is_array($method) && count($method) === 2 && isset($method[0], $method[1])) {
                    $className          = is_object($method[0]) ? get_class($method[0]) : strval($method[0]);
                    $methodName         = strval($method[1]);
                    $classMethodsList[] = array($className, $methodName);
                } elseif (is_string($method) || $method instanceof \Closure) {
                    $functionsList[] = $method;
                }
            }

            if ($classMethodsList) {
                $classMethodsQuery = new ClassMethodFilterQuery();
                $classMethodsQuery->setList($classMethodsList)->setAction($classMethodsQuery::ACTION_SAVE)->useTag($tag);
                $queries[] = $classMethodsQuery;
            }

            if ($functionsList) {
                $functionsQuery = new FunctionFilterQuery();
                $functionsQuery->setList($functionsList)->setAction($functionsQuery::ACTION_SAVE)->useTag($tag);
                $queries[] = $functionsQuery;
            }
        }

        $tagQuery = new TagFilterQuery();
        $tagQuery->setList(array_keys($list))->setAction($tagQuery::ACTION_REMOVE_ALL_IN_TAG);

        $queries = array_merge(array($tagQuery), $queries);

        $highLander->setQueries($queries);
        $highLander->execute();
    }

    public static function removeFilters($list)
    {
        $highLander = new HighLander();

        $queries = array();
        foreach ($list as $tag => $methods) {
            $classMethodsList = array();
            $functionsList    = array();
            foreach ($methods as $method) {
                if (is_array($method) && count($method) === 2 && isset($method[0], $method[1])) {
                    $className          = is_object($method[0]) ? get_class($method[0]) : strval($method[0]);
                    $methodName         = strval($method[1]);
                    $classMethodsList[] = array($className, $methodName);
                } elseif (is_string($method) || $method instanceof \Closure) {
                    $functionsList[] = $method;
                }
            }

            if ($classMethodsList) {
                $classMethodsQuery = new ClassMethodFilterQuery();
                $classMethodsQuery->setList($classMethodsList)->setAction($classMethodsQuery::ACTION_REMOVE)->useTag($tag);
                $queries[] = $classMethodsQuery;
            }

            if ($functionsList) {
                $functionsQuery = new FunctionFilterQuery();
                $functionsQuery->setList($functionsList)->setAction($functionsQuery::ACTION_REMOVE)->useTag($tag);
                $queries[] = $functionsQuery;
            }
        }

        $highLander->setQueries($queries);
        $highLander->execute();
    }
}
