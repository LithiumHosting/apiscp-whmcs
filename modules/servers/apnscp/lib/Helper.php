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

        // Addon Domains
        if ($params['configoption2'] !== '')
        {
            $opts['aliases.enabled'] = (bool) ((int) $params['configoption2'] > 0);
            $opts['aliases.max']     = (int) $params['configoption2']; // Actual Number of addon domains
        }

        // Account Password
        $opts['auth.tpasswd'] = $params['password']; // Plain Text Password for account

        //Bandwidth
        if ($params['configoption3'] !== '')
        {
            $opts['bandwidth.threshold'] = (int) $params['configoption3'] ?: 10000;
            $opts['bandwidth.units']     = 'GB'; //B,KB,MB,GB,TB
        }
//    $opts['bandwidth.rollover']  = ''; // Day of month that BW resets (defaults to today)

//billing Account Linking
        $opts['billing.invoice'] = 'whmcs-' . $params['serviceid']; // Invoice id to link to customer
//    $opts['billing.parent_invoice'] = 'WHMCS-Client-' . $params['userid']; // Invoice id to link to customer

//cgroup Resource Enforcement
        if ($params['configoption4'] !== '')
        {
            $opts['cgroup.memory'] = $params['configoption4'] ?: 256;//'256'; // Limit memory usage of account in MB
        }
        if ($params['configoption5'] !== '')
        {
            $opts['cgroup.cpu'] = $params['configoption5'] ?: 2000; // Not sure ...
        }
        if ($params['configoption6'] !== '')
        {
            $opts['cgroup.cpuweight'] = $params['configoption6'] ?: 1024; // Allocate added weight to process tasks
        }
        if ($params['configoption7'] !== '')
        {
            $opts['cgroup.proclimit'] = $params['configoption7'] ?: 'null'; // [null, 0-4096] Limit account to # processes
        }

//Disk Quota
        if ($params['configoption8'] !== '')
        {
            $opts['diskquota.quota'] = $params['configoption8'] ?: 4000; // [null,0-∞] Account storage quota
            $opts['diskquota.units'] = 'GB'; //[B,KB,MB,GB,TB] Supplied value has specified unit
        }
        if ($params['configoption9'] !== '')
        {
            $opts['diskquota.fquota'] = $params['configoption9'] ?: 'null'; // [null,0-∞] Account inode quota
        }

// DNS Module
//    $opts['dns.enabled']  = '1'; // 0.1
//    $opts['dns.provider'] = $params['configoption13'] ?: 'builtin'; //  [aws,builtin,cloudflare,digitalocean,linode,null,vultr] Assign DNS handler for account
//    $opts['dns.key']      = $params['configoption14'] ?: null; // <string> DNS provider key

//FTP Module
        $opts['ftp.enabled'] = $params['configoption10'] ? '1' : '0';
//    $opts['ftp.ftpserver'] = 'ftp.'; // FTP Prefix

//IP Stuffs
        $opts['ipinfo.enabled']   = '1'; // [0,1] Assign account unique IPv4 address from pool
        $opts['ipinfo.namebased'] = $params['configoption11'] === 'shared' ? '1' : '0'; // [0,1] Site uses shared IP address (unique otherwise, see ipaddrs)

// Not supported yet!!!
//IPv6 Stuffs
//    $opts['ipinfo6.enabled']   = $params['configoption12'] === 'disabled' ? '0' : '1'; // [0,1] Assign account unique IPv6 address from pool
//    $opts['ipinfo6.namebased'] = $params['configoption12'] === 'shared' ? '1' : '0'; // [0,1] Site uses shared IP address (unique otherwise, see ipaddrs)

// Logging
        $opts['logs.enabled'] = '1'; // [0,1] Record web server access

// Mail
        $opts['mail.enabled'] = $params['configoption13'] ? '1' : '0'; // [0,1] Enable mail service

//MySQL
        if ((int) $params['configoption14'] === 0)
        {
            $opts['mysql.enabled'] = '0'; // [0,1] MySQL database access. Required for Web App usage.
        }
        else
        {
            $opts['mysql.enabled']     = '1'; // [0,1] MySQL database access. Required for Web App usage.
            $opts['mysql.dbaseadmin']  = $params['username']; // <string> Set mysql admin user
            $opts['mysql.dbaseprefix'] = $params['username'] . '_'; // <<string> Set MySQL database prefix. Must end with '_'
            if ($params['configoption15'] !== '')
            {
                $opts['mysql.dbasenum'] = $params['configoption15'] === '-1' ? 'null' : $params['configoption15'];  // [null, 0-999] Limit total database count
            }
            $opts['mysql.passwd'] = $params['password']; // <string> Plain-text password for mysql user.
        }

//PostgreSQL
        if ((int) $params['configoption16'] === 0)
        {
            $opts['pgsql.enabled'] = '0'; // [0,1] Enable PostgreSQL database access. Required for Discourse usage.
        }
        else
        {
            $opts['pgsql.enabled']     = '1'; // [0,1] Enable PostgreSQL database access. Required for Discourse usage.
            $opts['pgsql.dbaseadmin']  = $params['username']; // <string> Set pgsql admin user
            $opts['pgsql.dbaseprefix'] = $params['username'] . '_'; // <string> Set PostgreSQL database prefix. Must end with '_'
            if ($params['configoption16'] !== '')
            {
                $opts['pgsql.dbasenum'] = $params['configoption16'] === '-1' ? 'null' : $params['configoption16']; // [null, 0-999] Limit total database count
            }
            $opts['pgsql.passwd'] = $params['password']; // <string> Plain-text password for pgsql user.
        }

// Rampart
//    $opts['rampart.enabled']   = '1'; // [0,1] Delegate brute-force whitelisting
//    $opts['rampart.max']       = $params['configoption27'] ?: 100; // [-1, 0 => 4096] Maximum number of IP address whitelists.
        $opts['rampart.whitelist'] = [$params['model']->client->ip]; // IPv4 | IPv6 IPv4 + IPv6 addresses

// Spam Filtering
        $opts['spamfilter.enabled']  = $params['configoption17'] ? '1' : '0'; // [0,1] Mail filtering
        $opts['spamfilter.provider'] = $params['configoption18']; // [spamassassin,rspamd] Inbound spam filter

// SSH Module
        $opts['ssh.enabled'] = $params['configoption19'] ? '1' : '0'; // [0,1] Enable ssh service
        $opts['ssh.jail']    = '1'; // [0,1] Jail all SSH sessions to account

//SSL
        $opts['ssl.enabled'] = $params['configoption20'] ? '1' : '0'; // [0,1] Enable ssl service

// Users
        $opts['users.enabled'] = (int) $params['configoption21'] === 0 ? '0' : '1'; // [0,1] Enable users service
        if ($params['configoption21'] !== '')
        {
            $opts['users.max'] = $params['configoption21'] === '-1' ? 'null' : $params['configoption21']; // [null, 0-4096] Limit up to # secondary users
        }

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
    public static function generateCommand(array $opts): string
    {
        $optArray   = [];
        $optArray[] = 'AddDomain';

        foreach ($opts as $service => $value)
        {
            $service    = str_replace('.', ',', $service);
            $optArray[] = "-c '{$service}'='{$value}'";
        }

        return implode(' ', $optArray);
    }
}