<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var \App\Models\User */
    public $user;
    
    /** @var \App\Models\Companies */
    public $company;
    
    /** @var \App\Models\Department */
    public $department;
    
    /** @var \App\Models\Currency */
    public $currency;
    
    /** @var \App\Models\Product */
    public $product;
}
