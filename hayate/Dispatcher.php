<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Sat May  7 23:08:05 JST 2011
 */
namespace Hayate;

/**
 * Finishes off the job started by the router,
 * it dispatches the request to the model/controller/action
 * assigned by the router
 * it fires Dispatched event when done
 */
class Dispatcher
{
    public function __construct() {}


    public function dispatch(Router $router)
    {
        $controllerPath = $router->modulesPath() .'/'. $router->module() .'/controller/'. $router->controller() .'.php';
        if (! is_file($controllerPath))
        {
            throw new Exception(URI::getInstance()->current(), 404);
        }

        require_once $controllerPath;
        $classname = $router->module().'\Controller\\'.$router->controller();

        $controller = new $classname();
        $controller->fire(Controller::PreAction);

        $action = $router->action();
        $parts = $router->args();

        switch (count($parts))
        {
        case 0:
            $controller->$action();
            break;
        case 1:
            $controller->$action($parts[0]);
            break;
        case 2:
            $controller->$action($parts[0], $parts[1]);
            break;
        case 3:
            $controller->$action($parts[0], $parts[1], $parts[2]);
            break;
        case 4:
            $controller->$action($parts[0], $parts[1], $parts[2], $parts[3]);
            break;
        case 5:
            $controller->$action($parts[0], $parts[1], $parts[2], $parts[3], $pargs[4]);
            break;
        default:
            // all right then
            call_user_func_array(array($controller, $action), $parts);
        }
        $controller->fire(Controller::PostAction);
    }
}
