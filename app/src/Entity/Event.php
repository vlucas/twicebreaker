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

    public static function relations()
    {
        return [
            'participants' => [
                'type' => 'HasMany',
                'entity' => 'Entity\User',
                'where' => ['current_event_id' => ':entity.id']
            ],
            'taggings' => [
                'type' => 'HasMany',
                'entity' => 'Entity\Event\Tagging',
                'where' => ['event_id' => ':entity.id']
            ]
        ];
    }

    /**
     * Get users and tagging stats to display on leaderboard
     */
    public function getLeaderboardStats()
    {
        return app()['mapper']->query('Entity\User', "
            SELECT u.*, COUNT(et.user_id) AS tagcount
            FROM users AS u
            LEFT JOIN event_taggings AS et ON(u.id = et.user_id)
            WHERE u.current_event_id = ?
            GROUP BY u.id
            ORDER BY tagcount DESC, u.date_created ASC
        ", [$this->id]);
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

