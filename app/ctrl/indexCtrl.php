<?php

namespace App\Ctrl;

use Ylmz\Controller;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class IndexCtrl extends Controller
{
    public function index(Request $request): Response
    {
        $this->assign('title', 'Ylmz Framework');
        $this->assign('data', 'Hello Ylmz!');
        return $this->display('index.html');
    }

    public function test(Request $request): Response
    {
        $this->assign('title', 'Test Page');
        $this->assign('data', 'This is a test');
        return $this->display('test.html');
    }
}
