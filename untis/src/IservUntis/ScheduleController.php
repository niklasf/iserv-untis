<?php

namespace IservUntis;

use Symfony\Component\HttpFoundation\Request;

class ScheduleController
{
    public function __construct()
    {
        $this->records = array();

        $this->last_change = filemtime("/var/untis/GPU001.TXT");

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
            fclose($handle);
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

        return $app->render('schedule-index.html.twig', array(
            'matrix' => $matrix
        ));
    }

    public function schedule(Request $request, Application $app, $class) {
        $schedule = array();

        foreach ($this->records as $record) {
            if ($record['class'] == $class) {
                $schedule[$record['slot']][$record['day']][] = array(
                    'subject' => $record['subject'],
                    'teacher' => $record['teacher'],
                    'room' => $record['room'],
                );
            }
        }

        return $app->render('schedule.html.twig', array(
            'class' => $class,
            'schedule' => $schedule,
            'last_change' => $this->last_change,
        ));
    }
}
