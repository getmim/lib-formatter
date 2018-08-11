<?php
/**
 * Custom datetime
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class DateTime extends \DateTime implements \JsonSerializable
{
    private $timezone;
    private $time;
    private $value;

    public function __construct(string $time='now', \DateTimeZone $timezone=null){
        parent::__construct($time, $timezone);
        $this->value = $time;
        $this->time = $this->getTimestamp();
        $this->timezone = $this->getTimezone()->getName();
    }

    public function __get($name){
        return $this->$name ?? null;
    }

    public function __toString(){
        return $this->format('c');
    }

    public function jsonSerialize(){
        return $this->__toString();
    }
}