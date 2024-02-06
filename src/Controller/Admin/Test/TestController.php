<?php

namespace App\Controller\Admin\Test;

class TestController
{
    public function index($id, $slug)
    {
        echo "$id, $slug";
    }
}