<?php
/**
 * apnscp Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2019
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

class Connector {

    private $key;
    private $endpoint;

    public function __construct($key, $endpoint)
    {
        $this->key      = $key;
        $this->endpoint = $endpoint;
    }

    public function request() :SoapClient
    {
        $key      = $this->key;
        $endpoint = $this->endpoint;
        $client   = new SoapClient(
            $endpoint . '/apnscp.wsdl',
            [
                'connection_timeout' => 5,
                'location'           => $endpoint . '/soap?authkey=' . $key,
                'uri'                => 'urn:net.apnscp.soap',
                'trace'              => true,
            ]
        );

        return $client;
    }
}
