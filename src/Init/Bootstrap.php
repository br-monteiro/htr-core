<?php
namespace HTR\Init;

use Slim\App;
use HTR\Common\AppContainer;
use App\Routes\Router;
use HTR\Common\HttpFunctions as fn;

class Bootstrap
{

    /**
     * Instance of Slim App
     *
     * @var Slim\App
     */
    private $app;

    public function __construct()
    {
        $this->setUp();
    }

    /**
     * SetUp the system
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return $this
     */
    private function setUp(): self
    {
        fn::allowCors();
        fn::allowHeader();
        $this->configApp();
        $this->configRoutes();

        return $this;
    }

    /**
     * Setting the container of Application
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return \self
     */
    private function configApp(): self
    {
        $this->app = new App(AppContainer::container());

        return $this;
    }

    /**
     * Configure the routes defined on application
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return \self
     */
    private function configRoutes(): self
    {
        Router::setUp($this->app);

        return $this;
    }

    /**
     * Run the application
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param bool $silent
     */
    public function run(bool $silent = false)
    {
        $this->app->run($silent);
    }
}
