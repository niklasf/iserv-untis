<?php

namespace IservUntis;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleController
{
    private $records;

    private $lastChange;

    public function __construct()
    {
        $this->records = array();

        $this->lastChange = filemtime("/var/untis/GPU001.TXT");

        $handle = fopen("/var/untis/GPU001.TXT", "r");
        if ($handle) {
            while ($data = fgetcsv($handle)) {
                $this->records[] = array(
                    'id' => intval($data[0]),
                    'class' => utf8_encode($data[1]),
                    'teacher' => utf8_encode($data[2]),
                    'subject' => utf8_encode($data[3]),
                    'room' => utf8_encode($data[4]),
                    'day' => intval($data[5]),
                    'slot' => intval($data[6]),
                );
            }
            fclose($handle);
        }
    }

    public function getLastChange()
    {
        return $this->lastChange;
    }

    public function getClassIndex()
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

        return $matrix;
    }

    public function renderClassIndex(Request $request, Application $app)
    {
        $matrix = array();

        return $app->render('schedule-index.html.twig', array(
            'matrix' => $this->getClassIndex()
        ));
    }

    public function getSchedule()
    {
        $schedule = array();

        foreach ($this->records as $record) {
            $schedule[$record['day']][$record['slot']][] = $record;
        }

        return $schedule;
    }

    public function getFilteredSchedule($key, $value)
    {
        $schedule = $this->getSchedule();

        $found = false;

        foreach ($schedule as &$day) {
            foreach ($day as &$slot) {
                $slot = array_filter($slot, function ($record) use ($key, $value) {
                    return $record[$key] == $value;
                });

                if ($slot) {
                    $found = true;
                }
            }
        }

        if (!$found) {
            throw new NotFoundHttpException();
        }

        return $schedule;
    }

    public function renderSchedule(Request $request, Application $app, $filter, $by)
    {
        switch ($filter) {
        case 'class':
            $title = sprintf('Stundenplan Klasse %s', $by);
            $columns = array('subject', 'teacher', 'room');
            break;
        case 'teacher':
            $title = sprintf('Stundenplan %s', $by);
            $columns = array('class', 'subject', 'room');
            break;
        case 'room':
            $title = sprintf('Stundenplan Raum %s', $by);
            $columns = array('class', 'subject', 'teacher');
            break;
        }

        return $app->render('schedule.html.twig', array(
            'title' => $title,
            'columns' => $columns,
            'schedule' => $this->getFilteredSchedule($filter, $by),
            'last_change' => $this->getLastChange(),
        ));
    }
}
