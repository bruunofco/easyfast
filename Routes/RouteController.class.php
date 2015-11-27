<?php
namespace EasyFast\Routes;

/**
 * Class RouteController
 *
 * @package EasyFast\Routes
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class RouteController extends RouteParent
{
    /**
     * Contain a the list of routes
     *
     * @var array
     */
    public $route;

    /**
     * Go Route
     *
     * @param $name
     * @throws \EasyFast\Exceptions\EasyFastException
     */
    public function go($name)
    {
        foreach ($this->route as $key => $action) {
            if (isset($ctrl['name']) && $ctrl['name'] == $name) {
                $this->location($action['url']);
            } elseif (isset($ctrl['url']) && $ctrl['url'] == $name) {
                $this->location($action['url']);
            }
        }
    }
}
