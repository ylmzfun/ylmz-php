<?php

namespace App\Ctrl;

use Ylmz\Controller;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class IndexCtrl extends Controller
{
    public function index(Request $request): Response
    {
        return $this->display('index.html');
    }
}
