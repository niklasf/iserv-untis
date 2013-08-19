<?php

namespace IservUntis;

use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;


class Application extends SilexApplication
{
    use \Silex\Application\TwigTrait;

    public function __construct()
    {
        parent::__construct();

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../../views/',
        ));

        $this->get('/schedule/', 'IservUntis\ScheduleController::renderClassIndex');
        $this->get('/schedule/{filter}/{by}', 'IservUntis\ScheduleController::renderSchedule');

        $this['debug'] = true;
    }
}
