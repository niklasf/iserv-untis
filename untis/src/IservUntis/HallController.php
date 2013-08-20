<?php

namespace IservUntis;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HallController
{
    private $records = array();

    private $lastChange;

    public function __construct()
    {
        $this->lastChange = filemtime('/var/untis/GPU009.TXT');

        $handle = fopen('/var/untis/GPU009.TXT', 'r');
        if ($handle) {
            while ($data = fgetcsv($handle)) {
                $this->records[] = array(
                    'hall' => utf8_encode($data[0]),
                    'teacher' => utf8_encode($data[1]),
                    'day' => intval($data[2]),
                    'slot' => intval($data[3]),
                    'points' => intval($data[4]),
                );
            }
            fclose($handle);
        }
    }

    public function getLastChange()
    {
        return $this->lastChange;
    }

    public function getSchedule($hall)
    {
        $schedule = array();

        foreach ($this->records as $record) {
            if ($record['hall'] == $hall) {
                $schedule[$record['day']][$record['slot']] = $record;
            }
        }

        if (!$schedule) {
            throw new NotFoundHttpException();
        }

        return $schedule;
    }

    public function renderSchedule(Request $request, Application $app, $hall)
    {
        return $app->render('hall.html.twig', array(
            'hall' => $hall,
            'schedule' => $this->getSchedule($hall),
            'last_change' => $this->getLastChange(),
        ));
    }

    public function getHallIndex()
    {
        $halls = array();

        foreach ($this->records as $record) {
            $halls[$record['hall']] = $record['hall'];
        }

        ksort($halls);

        return $halls;
    }
}
