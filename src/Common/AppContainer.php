<?php
namespace HTR\Common;

use App\System\Configuration as cfg;
use Slim\Container;

/**
 * The container configuration of Slim Framework
 *
 * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
 * @since 1.0
 */
class AppContainer
{

    /**
     * @var type Slim\Container
     */
    private static $container;

    /**
     * Config the container used in Slim Framework
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return Slim\Container
     */
    private static function setUp(): Container
    {
        if (!self::$container) {
            //Create Your container
            self::$container = new Container();
        }
        $c = self::$container;

        //$c['settings'] = Configuration::SLIM_SETTINGS['settings'];
        // Not Found Configs
        $c['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                return $c['response']
                        ->withStatus(404)
                        ->withJson([
                            "message" => "Route not found",
                            "status" => "error"
                ]);
            };
        };
        // Not Allowed Methods
        $c['notAllowedHandler'] = function ($c) {
            return function ($request, $response, $methods) use ($c) {
                return $c['response']
                        ->withStatus(405)
                        ->withHeader('Allow', implode(', ', $methods))
                        ->withJson([
                            "message" => 'Method must be one of: ' . implode(', ', $methods),
                            "status" => "error"]);
            };
        };
        // Error 500
        $c['errorHandler'] = function ($c) {
            return function ($request, $response, $exception) use ($c) {
                $dataError = [
                    "message" => 'Something went wrong!',
                    "status" => 'error'
                ];
                if (cfg::htrFileConfigs()->devmode ?? false) {
                    $dataError['error'] = $exception->getMessage();
                }
                return $c['response']
                        ->withStatus(500)
                        ->withJson($dataError);
            };
        };
        // Settings configs
        $c['settings']['addContentLengthHeader'] = false;
        $c['settings']['displayErrorDetails'] = cfg::htrFileConfigs()->devmode ?? false;
        // 
        return $c;
    }

    /**
     * Returns the Container Object configured
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return Slim\Container
     */
    public static function container(): Container
    {
        return self::setUp();
    }
}
