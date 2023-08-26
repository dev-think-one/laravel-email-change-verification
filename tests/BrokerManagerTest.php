<?php

namespace EmailChangeVerification\Tests;

use EmailChangeVerification\EmailChange;

class BrokerManagerTest extends TestCase
{

    /** @test */
    public function change_default_driver()
    {
        $this->assertEquals('users', EmailChange::getDefaultDriver());

        EmailChange::setDefaultDriver('foo_bar');

        $this->assertEquals('foo_bar', EmailChange::getDefaultDriver());
    }

    /** @test */
    public function error_if_config_not_exists()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email change verificator [foo_bar] is not defined.');

        EmailChange::broker('foo_bar');
    }
}
