<?php

namespace App\Ctrl\Demo;

use Ylmz\Controller;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class DemoCtrl extends Controller
{
    public function index(Request $request): Response
    {
        return $this->display('demo/index.html');
    }
}
