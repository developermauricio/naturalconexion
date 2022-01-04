<?php
defined( 'ABSPATH' ) || exit;
require_once('v1/QC_WC_CM_REST_Orders_Controller.php');
class CM_Controller
{
    public function init()
    {
        add_action('rest_api_init', array($this, 'register_rest_routes'), 10, 3);
    }

    /**
     * Register REST API routes.
     */
    public function register_rest_routes()
    {
        foreach ($this->get_rest_namespaces() as $namespace => $controllers) {
            foreach ($controllers as $controller_name => $controller_class) {
                $this->controllers[$namespace][$controller_name] = new $controller_class();
                $this->controllers[$namespace][$controller_name]->register_routes();
            }
        }
    }

    /**
     * Get API namespaces - new namespaces should be registered here.
     *
     * @return array List of Namespaces and Main controller classes.
     */
    protected function get_rest_namespaces()
    {
        return apply_filters(
            'cm_wc_rest_namespaces',
            [
                'cm/v1' => $this->get_v1_controllers(),
            ]
        );
    }

    /**
     * List of controllers in the wc/v3 namespace.
     *
     * @return array
     */
    protected function get_v1_controllers()
    {
        return [
            'orders' => 'QC_WC_CM_REST_Orders_Controller',
        ];
    }
}
