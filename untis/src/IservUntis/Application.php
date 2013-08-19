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

        $this->get('/class/{name}', 'IservUntis\ScheduleController::renderClassSchedule');
        $this->get('/room/{name}', 'IservUntis\ScheduleController::renderRoomSchedule');
        $this->get('/teacher/{name}', 'IservUntis\ScheduleController::renderTeacherSchedule');
        $this->get('/hall/{name}', 'IservUntis\HallController::renderHallSchedule');

        $this['debug'] = true;
    }
}
