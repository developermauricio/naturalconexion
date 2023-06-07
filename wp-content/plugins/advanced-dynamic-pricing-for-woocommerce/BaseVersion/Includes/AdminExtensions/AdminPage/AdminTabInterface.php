<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

interface AdminTabInterface
{
    /**
     * AdminTabInterface constructor.
     *
     * @param null $deprecated
     */
    public function __construct($deprecated = null);

    public function withContext(Context $context);

    public function handleSubmitAction();

    public function registerAjax();

    public function enqueueScripts();

    /**
     * @return array
     */
    public function getViewVariables();

    /**
     * Display priority in the header
     *
     * @return int
     */
    public static function getHeaderDisplayPriority();

    /**
     * @return string
     */
    public static function getRelativeViewPath();

    /**
     * Unique tab key
     *
     * @return string
     */
    public static function getKey();

    /**
     * Localized title
     *
     * @return string
     */
    public static function getTitle();
}
