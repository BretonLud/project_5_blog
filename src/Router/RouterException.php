<?php

namespace App\Router;

class RouterException extends \Exception
{
    protected $message = "Cette route n'existe pas";
}