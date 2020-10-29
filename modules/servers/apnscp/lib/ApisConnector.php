<?php
/**
 * apnscp Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2019
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

class ApisConnector extends SoapClient {
    const WSDL_PATH = '/apnscp.wsdl';
    // @var string session cookie identifier
    const COOKIE_NAME = 'esprit_id';

    private $id;

    /**
     * Create new API client
     *
     * @param       $api_key
     * @param       $api_endpoint
     * @param array $ctor additional constructor arguments to SoapClient
     *
     * @return SoapClient
     */
    public static function create_client($api_key, $api_endpoint, ...$ctor): \SoapClient
    {
        $uri  = $api_endpoint . '/soap';
        $wsdl = str_replace('/soap', self::WSDL_PATH, $uri);

        $ip = $ctor[1] ?? $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['SSH_CLIENT'])) {
            $ip = explode(' ', $_SERVER['SSH_CLIENT']);
            $ip = $ip[0];
        }

        $ip = $ip ?? '127.0.0.1';

        $headers = [
            'Abort-On: error',
            'X-Forwarded-For: ' . $ip,
        ];

        $connopts = $ctor + [
                'connection_timeout' => 30,
                'location'           => $uri,
                'uri'                => 'urn:apnscp.api.soap',
                'trace'              => true,
                'stream_context'     => stream_context_create([
                    'http' => [
                        'header' => implode("\r\n", $headers) . "\r\n",
                    ],
                ]),
            ];

        $connopts['location'] = $uri . '?authkey=' . $api_key;

        return (new static($wsdl, $connopts))->setId($ctor[0] ?? \session_id());
    }

    /**
     * Set session name to pass between requests
     *
     * @param string $name
     *
     * @return Util_API
     */
    public function setId(string $name): self
    {
        $this->id = $name;

        return $this;
    }

    public function __call($function_name, $arguments)
    {
        static $ctr = 0;
        $this->__setCookie(self::COOKIE_NAME, $this->id);
        $ret = parent::__call($function_name, $arguments);
        if ($ret !== null || $ctr >= 5)
        {
            $ctr = 0;

            return $ret;
        }
        // 50 ms sleep
        usleep(50000);
        $ctr++;

        return $this->__call($function_name, $arguments);
    }
}
