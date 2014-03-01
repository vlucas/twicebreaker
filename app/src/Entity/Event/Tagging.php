<?php
namespace Entity\Event;
use App;

class Tagging extends App\Entity
{
    protected static $_datasource = 'event_taggings';

    public static function fields()
    {
        return [
            'id'             => ['type' => 'int', 'primary' => true, 'serial' => true],
            'user_id'        => ['type' => 'int', 'required' => true, 'unique' => 'user_event_tag'],
            'tagged_user_id' => ['type' => 'int', 'required' => true, 'unique' => 'user_event_tag'],
            'event_id'       => ['type' => 'int', 'required' => true, 'unique' => 'user_event_tag'],
            'tagcode'        => ['type' => 'string', 'length' => 3],
            'date_created'   => ['type' => 'datetime', 'default' => new \DateTime()]
        ];
    }
}

