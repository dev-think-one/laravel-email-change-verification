<?php

namespace EmailChangeVerification\Broker;

interface BrokerFactory
{
    /**
     * Get a email change broker instance by name.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function broker($name = null);
}
