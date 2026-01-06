<?php

namespace App\Dummy;

class DummyClassC
{
    public function __construct(
        protected DummyClassD $dummyClassD
    )
    {

    }
}