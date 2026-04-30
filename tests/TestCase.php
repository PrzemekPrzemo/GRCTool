<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Auto-seed po RefreshDatabase migrations.
     */
    protected bool $seed = true;
}
