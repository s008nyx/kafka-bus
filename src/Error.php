<?php

namespace KafkaBus;

class Error
{
    private $code;
    private $title;
    private $message;

    public function __construct($code, $title, $message = null)
    {
        $this->code = $code;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed|null
     */
    public function getMessage()
    {
        return $this->message;
    }
}