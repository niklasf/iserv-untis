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
            if ($record['class']) {
                $year = intval($record['class']);
                $matrix[$year][$record['class']] = $record['class'];
            }
        }

        // Sort by year.
        ksort($matrix);

        // Sort each row by class.
        foreach ($matrix as &$year) {
            ksort($year, SORT_STRING);
        }

        return $matrix;
    }

    public function getRoomIndex()
    {
        $matrix = array();

        // Group rooms by the first two letters.
        foreach ($this->records as $record) {
            if ($record['room']) {
                $group = substr($record['room'], 0, 2);
                $matrix[$group][$record['room']] = $record['room'];
            }
        }

        // Sort by group.
        ksort($matrix, SORT_STRING);

        // Sort each row.
        foreach ($matrix as &$row) {
            ksort($row, SORT_STRING);
        }

        return $matrix;
    }

    public function getTeacherIndex()
    {
        $matrix = array();

        // Group teachers by the first letter of the lastname.
        foreach ($this->records as $record) {
            $group = substr($record['teacher'], 0, 1);
            $matrix[$group][$record['teacher']] = $record['teacher'];
        }

        // Sort.
        ksort($matrix, SORT_STRING);

        // Sort each group.
        foreach ($matrix as &$row) {
            ksort($row, SORT_STRING);
        }

        return $matrix;
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

    public function renderClassSchedule(Request $request, Application $app, $name)
    {
        return $app->render('schedule.html.twig', array(
            'title' => sprintf('Klasse %s', $name),
            'columns' => array('subject', 'teacher', 'room'),
            'schedule' => $this->getFilteredSchedule('class', $name),
            'last_change' => $this->getLastChange(),
        ));
    }

    public function renderRoomSchedule(Request $request, Application $app, $name)
    {
        if (ctype_digit($name)) {
            $title = sprintf('Raum %s', $name);
        } else {
            $title = $name;
        }

        return $app->render('schedule.html.twig', array(
            'title' => $title,
            'columns' => array('teacher', 'class', 'subject'),
            'schedule' => $this->getFilteredSchedule('room', $name),
            'last_change' => $this->getLastChange(),
        ));
    }

    public function renderTeacherSchedule(Request $request, Application $app, $name)
    {
        return $app->render('schedule.html.twig', array(
            'title' => sprintf('Studenplan %s', $name),
            'columns' => array('class', 'subject', 'room'),
            'schedule' => $this->getFilteredSchedule('teacher', $name),
            'last_change' => $this->getLastChange(),
        ));
    }
}
