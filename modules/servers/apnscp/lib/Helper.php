<?php


class Helper {

    /**
     * @param array $params
     *
     * @return array
     */
    public static function generateOptions(array $params): array
    {
        $opts = [];

        //Site Info
        $opts['siteinfo.enabled']    = '1'; // [0,1] Core account attributes
        $opts['siteinfo.domain']     = $params['domain']; // <string> Primary domain of the account
        $opts['siteinfo.admin_user'] = $params['username']; // <string> Administrative user of account
        $opts['siteinfo.email']      = $params['model']->client->email; // [email,[email1,email2...]] Contact address on account
        $opts['siteinfo.plan']       = $params['configoption1'];

        return $opts;
    }

    /**
     * @param array $opts
     *
     * @return string
     */
    public static function generateCommand(array $opts, $action): string
    {
        $optArray   = [];
        $optArray[] = $action;

        foreach ($opts as $service => $value)
        {
            $service    = str_replace('.', ',', $service);
            $optArray[] = "-c '{$service}'='{$value}'";
        }

        return implode(' ', $optArray);
    }
}