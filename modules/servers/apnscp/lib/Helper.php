<?php


class Helper {

    /**
     * @param array $params
     *
     * @return array
     */
    public static function generateOptions(array $params): array
    {
        //Site Info
        $opts['siteinfo.enabled']    = '1'; // [0,1] Core account attributes
        $opts['siteinfo.domain']     = $params['domain']; // <string> Primary domain of the account
        $opts['siteinfo.admin_user'] = $params['username']; // <string> Administrative user of account
        $opts['siteinfo.email']      = $params['model']->client->email; // [email,[email1,email2...]] Contact address on account
        $opts['siteinfo.plan']       = $params['configoption1'];

        //Billing
        $opts['billing.invoice'] = 'WHMCS-' . $params['serviceid']; // Invoice id to link to customer

        // MySQL
        $opts['mysql.dbaseadmin']  = $params['username']; // <string> Set mysql admin user
        $opts['mysql.dbaseprefix'] = $params['username'] . '_'; // <<string> Set MySQL database prefix. Must end with '_'

        // PGSQL
        $opts['pgsql.dbaseadmin']  = $params['username']; // <string> Set pgsql admin user
        $opts['pgsql.dbaseprefix'] = $params['username'] . '_'; // <string> Set PostgreSQL database prefix. Must end with '_'

        return $opts;
    }

    /**
     * @param array $opts
     *
     * @return string
     */
    public static function generateCommand(array $opts, $action): string
    {
        $optArray[] = $action;

        foreach ($opts as $service => $value)
        {
            $service    = str_replace('.', ',', $service);
            $optArray[] = "-c '{$service}'='{$value}'";
        }

        return implode(' ', $optArray);
    }
}

if (! function_exists('dd'))
{
    function dd()
    {
        $args = func_get_args();
        call_user_func_array('dump', $args);
        die();
    }
}

if (! function_exists('d'))
{
    function d()
    {
        $args = func_get_args();
        call_user_func_array('dump', $args);
    }
}