<?php
namespace Entity;
use App;

class Event extends App\Entity
{
    protected static $_datasource = 'events';

    public static function fields()
    {
        return [
            'id'             => ['type' => 'int', 'primary' => true, 'serial' => true],
            'user_id'        => ['type' => 'int', 'index' => true],
            'title'          => ['type' => 'string', 'required' => true],
            'description'    => ['type' => 'text'],
            'duration'       => ['type' => 'string', 'required' => true, 'default' => '5 minutes'],
            'started_at'     => ['type' => 'datetime'],
            'ended_at'       => ['type' => 'datetime'],
            'created_at'     => ['type' => 'datetime', 'default' => new \DateTime()]
        ];
    }

    public function hasStarted()
    {
       return $this->started_at !== null;
    }

    public function hasEnded()
    {
       return !$this->hasStarted() || ($this->ended_at <= new \DateTime());
    }

    public function isActive()
    {
       return $this->hasStarted() && !$this->hasEnded();
    }

    public function getSecondsLeft()
    {
        $seconds = 0;
        if($this->ended_at instanceof \DateTime) {
            $seconds = ($this->ended_at->format('U') - time());
        }
        return $seconds > 0 ? $seconds : 0;
    }
}

