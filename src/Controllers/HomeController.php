<?php

namespace App\Controllers;

use App\Dummy\DummyClassA;
use App\Dummy\DummyClassC;

class HomeController {

    public function __construct(
        protected DummyClassA $dummyClassA,
        protected DummyClassC $dummyClassC
    )
    {

    }

    public function index(): string {
       // echo $this->dummyClassA->test();
        return "Welcome to our homepage";
    }
    public function about(): string {
        return "About Us Page";
    }
}
