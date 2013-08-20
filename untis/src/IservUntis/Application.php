<?php

namespace IservUntis;

use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SecurityServiceProvider;


class Application extends SilexApplication
{
    use \Silex\Application\TwigTrait;

    public function __construct()
    {
        parent::__construct();
        $app = $this;


        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../../views/',
        ));

        $app['security.authentication_listener.factory.iserv'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_provider.' . $name . '.iserv'] = $app->share(function () use ($app) {
                return new IservAuthenticationProvider($app['security.user_provider.default']);
            });

            $app['security.authentication_listener.' . $name . '.iserv'] = $app->share(function () use ($app) {
                return new IservAuthenticationListener($app['security'], $app['security.authentication_manager']);
            });

            return array(
                'security.authentication_provider.' . $name . '.iserv',
                'security.authentication_listener.' . $name . '.iserv',
                null,
                'pre_auth'
            );
        });

        $this->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'default' => array(
                    'iserv' => true,
                ),
            ),
        ));
        /*    'users' => $app->share(function () use ($app) {
                return new IservUserProvider();
            }),
    )); */

        $this->get('/', 'IservUntis\IndexController::renderIndex');

        $this->get('/class/{name}', 'IservUntis\ScheduleController::renderClassSchedule');
        $this->get('/room/{name}', 'IservUntis\ScheduleController::renderRoomSchedule');
        $this->get('/teacher/{name}', 'IservUntis\ScheduleController::renderTeacherSchedule');

        $this->get('/hall/{hall}', 'IservUntis\HallController::renderSchedule');

        $this['debug'] = true;
    }
}
