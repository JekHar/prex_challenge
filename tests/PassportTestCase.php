<?php

namespace Tests;

use Tests\Traits\PassportTestHelper;

abstract class PassportTestCase extends TestCase
{
    use PassportTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPassport();
    }
}
