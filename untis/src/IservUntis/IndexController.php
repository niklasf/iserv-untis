<?php

namespace IservUntis;

use Symfony\Component\HttpFoundation\Request;
use IservUntis\ScheduleController;

class IndexController
{
    private $scheduleController;

    private $hallController;

    public function __construct()
    {
        $this->scheduleController = new ScheduleController();
        $this->hallController = new HallController();
    }

    public function renderIndex(Request $request, Application $app)
    {
        $render = array(
            'classes' => $this->scheduleController->getClassIndex(),
            'rooms' => $this->scheduleController->getRoomIndex(),
        );

        if ($app['security']->isGranted('ROLE_TEACHER')) {
            $render['teachers'] = $this->scheduleController->getTeacherIndex();
            $render['halls'] = $this->hallController->getHallIndex();
        }

        return $app->render('index.html.twig', $render);
    }
}
