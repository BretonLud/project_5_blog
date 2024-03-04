<?php

namespace App\Router;

class RouterException extends \Exception
{
    protected $message = "No matching routes";
}