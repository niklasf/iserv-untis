<?php

namespace IservUntis;

use Symfony\Component\HttpFoundation\Request;

class ScheduleController
{
    public function __construct()
    {
        $this->records = array();

        $handle = fopen("/var/untis/GPU001.TXT", "r");
        if ($handle) {
            while ($data = fgetcsv($handle)) {
                $this->records[] = array(
                    'id' => intval($data[0]),
                    'class' => $data[1],
                    'teacher' => $data[2],
                    'subject' => $data[3],
                    'room' => $data[4],
                    'day' => intval($data[5]),
                    'slot' => intval($data[6]),
                );
            }
        }
    }

    public function index(Request $request, Application $app)
    {
        $matrix = array();

        // Group classes by year.
        foreach ($this->records as $record) {
            $year = intval($record['class']);
            $matrix[$year][$record['class']] = $record['class'];
        }

        // Sort by year.
        ksort($matrix);

        // Sort each row by class.
        foreach ($matrix as &$year) {
            ksort($year);
        }

        return $app->render('schedule.html.twig', array(
            'matrix' => $matrix
        ));
    }
}
