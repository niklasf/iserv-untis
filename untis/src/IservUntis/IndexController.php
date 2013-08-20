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
        return $app->render('index.html.twig', array(
            'classes' => $this->scheduleController->getClassIndex(),
            'rooms' => $this->scheduleController->getRoomIndex(),
            'teachers' => $this->scheduleController->getTeacherIndex(),
            'halls' => $this->hallController->getHallIndex(),
        ));
    }
}
