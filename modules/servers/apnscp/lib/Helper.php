<?php

use WHMCS\Database\Capsule as DB;


class Helper
{

    /**
     * @param array $params
     *
     * @return array
     */
    public static function generateOptions(array $params): array
    {
//        // Addon Domains
//        if ((int) $params['configoption2'] === 0)
//        {
//            $opts['aliases.enabled'] = 0;
//        }
//        else
//        {
//            $opts['aliases.enabled'] = 1;
//            if ($params['configoption2'] === '-1')
//            {
//                $opts['aliases.max'] = 'null';
//            }
//            else
//            {
//                $opts['aliases.max'] = (int) $params['configoption2']; // Actual Number of addon domains
//            }
//        }
//
//        // Account Password
//        $opts['auth.tpasswd'] = $params['password']; // Plain Text Password for account
//
//        //Bandwidth
//        if ($params['configoption3'] !== '')
//        {
//            $opts['bandwidth.threshold'] = (int) $params['configoption3'];
//            $opts['bandwidth.units']     = 'GB'; //B,KB,MB,GB,TB
//        }
////    $opts['bandwidth.rollover']  = ''; // Day of month that BW resets (defaults to today)
//
////billing Account Linking
//        $opts['billing.invoice'] = 'WHMCS-' . $params['serviceid']; // Invoice id to link to customer
////    $opts['billing.parent_invoice'] = 'WHMCS-Client-' . $params['userid']; // Invoice id to link to customer
//
////cgroup Resource Enforcement
//        if ($params['configoption4'] !== '')
//        {
//            $opts['cgroup.memory'] = (int) $params['configoption4'];//'256'; // Limit memory usage of account in MB
//        }
//        if ($params['configoption5'] !== '')
//        {
//            $opts['cgroup.cpu'] = (int) $params['configoption5']; // Not sure ...
//        }
//        if ($params['configoption6'] !== '')
//        {
//            $opts['cgroup.cpuweight'] = (int) $params['configoption6']; // Allocate added weight to process tasks
//        }
//        if ($params['configoption7'] !== '')
//        {
//            $opts['cgroup.proclimit'] = (int) $params['configoption7']; // [null, 0-4096] Limit account to # processes
//        }
//
////Disk Quota
//        if ($params['configoption8'] !== '')
//        {
//            $opts['diskquota.quota'] = (int) $params['configoption8']; // [null,0-∞] Account storage quota
//            $opts['diskquota.units'] = 'GB'; //[B,KB,MB,GB,TB] Supplied value has specified unit
//        }
//        if ($params['configoption9'] !== '')
//        {
//            $opts['diskquota.fquota'] = $params['configoption9']; // [null,0-∞] Account inode quota
//        }
//
//// DNS Module
////    $opts['dns.enabled']  = '1'; // 0.1
////    $opts['dns.provider'] = $params['configoption13'] ?: 'builtin'; //  [aws,builtin,cloudflare,digitalocean,linode,null,vultr] Assign DNS handler for account
////    $opts['dns.key']      = $params['configoption14'] ?: null; // <string> DNS provider key
//
////FTP Module
//        $opts['ftp.enabled'] = $params['configoption10'] ? 1 : 0;
////    $opts['ftp.ftpserver'] = 'ftp.'; // FTP Prefix
//
////IP Stuffs
//        $opts['ipinfo.enabled']   = 1; // [0,1] Assign account unique IPv4 address from pool
//        $opts['ipinfo.namebased'] = $params['configoption11'] === 'shared' ? 1 : 0; // [0,1] Site uses shared IP address (unique otherwise, see ipaddrs)
//
//// Not supported yet!!!
////IPv6 Stuffs
////    $opts['ipinfo6.enabled']   = $params['configoption12'] === 'disabled' ? '0' : '1'; // [0,1] Assign account unique IPv6 address from pool
////    $opts['ipinfo6.namebased'] = $params['configoption12'] === 'shared' ? '1' : '0'; // [0,1] Site uses shared IP address (unique otherwise, see ipaddrs)
//
//// Logging
////        $opts['logs.enabled'] = 1; // [0,1] Record web server access
//
//// Mail
//        $opts['mail.enabled'] = $params['configoption13'] ? 1 : 0; // [0,1] Enable mail service
//
////MySQL
//        if ((int) $params['configoption14'] === 0)
//        {
//            $opts['mysql.enabled'] = 0; // [0,1] MySQL database access. Required for Web App usage.
//        }
//        else
//        {
//            $opts['mysql.enabled']     = '1'; // [0,1] MySQL database access. Required for Web App usage.
//            $opts['mysql.dbaseadmin']  = $params['username']; // <string> Set mysql admin user
//            $opts['mysql.dbaseprefix'] = $params['username'] . '_'; // <<string> Set MySQL database prefix. Must end with '_'
//            if ($params['configoption15'] !== '')
//            {
//                $opts['mysql.dbasenum'] = $params['configoption14'] === '-1' ? 'null' : $params['configoption14'];  // [null, 0-999] Limit total database count
//            }
//            $opts['mysql.passwd'] = $params['password']; // <string> Plain-text password for mysql user.
//        }
//
////PostgreSQL
//        if ((int) $params['configoption15'] === 0)
//        {
//            $opts['pgsql.enabled'] = 0; // [0,1] Enable PostgreSQL database access. Required for Discourse usage.
//        }
//        else
//        {
//            $opts['pgsql.enabled']     = '1'; // [0,1] Enable PostgreSQL database access. Required for Discourse usage.
//            $opts['pgsql.dbaseadmin']  = $params['username']; // <string> Set pgsql admin user
//            $opts['pgsql.dbaseprefix'] = $params['username'] . '_'; // <string> Set PostgreSQL database prefix. Must end with '_'
//            if ($params['configoption16'] !== '')
//            {
//                $opts['pgsql.dbasenum'] = $params['configoption15'] === '-1' ? 'null' : $params['configoption15']; // [null, 0-999] Limit total database count
//            }
//            $opts['pgsql.passwd'] = $params['password']; // <string> Plain-text password for pgsql user.
//        }
//
//// Rampart
////    $opts['rampart.enabled']   = '1'; // [0,1] Delegate brute-force whitelisting
////    $opts['rampart.max']       = $params['configoption16'] ?: 100; // [-1, 0 => 4096] Maximum number of IP address whitelists.
//        $opts['rampart.whitelist'] = [$params['model']->client->ip]; // IPv4 | IPv6 IPv4 + IPv6 addresses
//
//// Spam Filtering
//        $opts['spamfilter.enabled']  = $params['configoption17'] ? 1 : 0; // [0,1] Mail filtering
//        $opts['spamfilter.provider'] = $params['configoption18']; // [spamassassin,rspamd] Inbound spam filter
//
//// SSH Module
//        $opts['ssh.enabled'] = $params['configoption19'] ? 1 : 0; // [0,1] Enable ssh service
//        $opts['ssh.jail']    = '1'; // [0,1] Jail all SSH sessions to account
//
////SSL
//        $opts['ssl.enabled'] = $params['configoption20'] ? 1 : 0; // [0,1] Enable ssl service
//
//// Users
//        if ((int) $params['configoption21'] === 0)
//        {
//            $opts['users.enabled'] = 0;
//        }
//        else
//        {
//            $opts['users.enabled'] = 1;
//            if ($params['configoption21'] === '-1')
//            {
//                $opts['users.max'] = 'null';
//            }
//            else
//            {
//                $opts['users.max'] = (int) $params['configoption21']; // Actual Number of Users
//            }
//        }

        //Site Info
        $opts['siteinfo.enabled'] = '1'; // [0,1] Core account attributes
        $opts['siteinfo.domain'] = $params['domain']; // <string> Primary domain of the account
        $opts['siteinfo.admin_user'] = $params['username']; // <string> Administrative user of account
        $opts['siteinfo.email'] = $params['model']->client->email; // [email,[email1,email2...]] Contact address on account
        $opts['siteinfo.plan'] = $params['configoption1'];

        //Billing
        $opts['billing.invoice'] = 'WHMCS-' . $params['serviceid']; // Invoice id to link to customer

        // MySQL
        $opts['mysql.dbaseadmin'] = $params['username']; // <string> Set mysql admin user
        $opts['mysql.dbaseprefix'] = $params['username'] . '_'; // <<string> Set MySQL database prefix. Must end with '_'

        // PGSQL
        $opts['pgsql.dbaseadmin'] = $params['username']; // <string> Set pgsql admin user
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

        foreach ($opts as $service => $value) {
            $service = str_replace('.', ',', $service);
            $optArray[] = "-c '{$service}'='{$value}'";
        }

        return implode(' ', $optArray);
    }


    public static function apnscpValidateCustomFields($productId): void
    {
        $requiredFields = ['SiteID'];
        $existingFields = [];

        $customFields = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $productId)->get();

        if (! empty($customFields)) {
            foreach ($customFields as $field) {
                $existingFields[] = $field->fieldname;
            }
        }
        $newFields = array_diff($requiredFields, $existingFields);
        foreach ($newFields as $field) {
            switch ($field) {
                case 'SiteID':
                    DB::table('tblcustomfields')->insert(['type' => 'product', 'relid' => $productId, 'fieldname' => 'SiteID', 'fieldtype' => 'text', 'description' => 'ApisCP Site ID', 'fieldoptions' => '', 'adminonly' => 'on', 'sortorder' => 0]);
                    break;
            }
        }
    }


    public static function apnscpGetCustomFields($productId): array
    {
        $customFields = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $productId)->get();
        if (! empty($customFields)) {
            foreach ($customFields as $field) {
                $fields[$field->fieldname] = ['id' => $field->id, 'description' => $field->description]; // ACCESS Custom Fields via $customFields['OrderID']['id']; Where OrderID is the name of the custom field.
            }
        }

        return $fields ?? [];
    }

    public static function apnscpGetCustomFieldId($hostingId, $fieldId): string
    {
        $result = DB::table('tblcustomfieldsvalues')->where('fieldid', $fieldId)->where('relid', $hostingId)->first();
        return $result->id ?? '';
    }

    public static function apnscpAddCustomFieldValue($hostingId, $fieldId, $value): void
    {
        $result = DB::table('tblcustomfieldsvalues')->where('fieldid', $fieldId)->where('relid', $hostingId)->first();
        if (! empty($result)) {
            // update
            DB::table('tblcustomfieldsvalues')->where('id', $result->id)->update(['value' => $value]);
        } else {
            // insert
            DB::table('tblcustomfieldsvalues')->insert(['fieldid' => $fieldId, 'relid' => $hostingId, 'value' => $value]);
        }
    }

    public static function apnscpGetCustomFieldValue($hostingId, $fieldId): string
    {
        $result = DB::table('tblcustomfieldsvalues')->where('fieldid', $fieldId)->where('relid', $hostingId)->first();
        return $result->value ?? '';
    }
}