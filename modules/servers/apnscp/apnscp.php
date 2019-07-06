<?php
/**
 * apnscp Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2019
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

if (! defined("WHMCS"))
{
    die("This file cannot be accessed directly");
}

require_once('lib/Connector.php');

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related abilities and
 * settings.
 *
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 *
 * @return array
 */
function apnscp_MetaData()
{
    return array(
        'DisplayName'              => 'apnscp',
        'APIVersion'               => '1.0', // Use API Version 1.1
        'RequiresServer'           => true, // Set true if module requires a server to work
        'DefaultNonSSLPort'        => '2082', // Default Non-SSL Connection Port
        'DefaultSSLPort'           => '2083', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel'   => 'Login to Panel as Admin',
    );
}

/**
 * Define product configuration options.
 *
 * The values you return here define the configuration options that are
 * presented to a user when configuring a product for use with the module. These
 * values are then made available in all module function calls with the key name
 * configoptionX - with X being the index number of the field from 1 to 24.
 *
 * You can specify up to 24 parameters, with field types:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each and their possible configuration parameters are provided in
 * this sample function.
 *
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 *
 * @return array
 */
function apnscp_ConfigOptions()
{
    return [
        'apnscp Plan' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => 'basic',
            'Description' => 'Enter a plan name as configured in apnscp (case sensitive)',
        ],

        'Addon Domains' => [
            'Type'    => 'text',
            'Size'    => '10',
            'Default' => '0',
        ],

        'Bandwidth Limit (GB)' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '100',
            'Description' => 'Leave empty to disable BW limits',
        ],

        'Memory Limit' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '256',
            'Description' => 'MB',
        ],

        'CPU Limit' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '2000',
            'Description' => 'Default "2000"',
        ],

        'CPU Weight' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '1024',
            'Description' => 'Default "1024"',
        ],

        'Proc/thread Limit' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '100',
            'Description' => '[0-4096]',
        ],

        'Disk Quota (GB)' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '4',
            'Description' => 'Account storage quota',
        ],

        'inode Limit' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '250000',
            'Description' => 'Account inode quota',
        ],

        'FTP Enabled' => [
            'Type'        => 'yesno',
            'Description' => 'Tick to enable',
            'Default'     => 'checked',
        ],

        'IPv4' => [
            'Type'    => 'radio',
            'Options' => 'unique,shared',
            'Default' => 'shared',
        ],

        'IPv6' => [
            'Type'    => 'radio',
            'Options' => 'unique,shared,disabled',
            'Default' => 'disabled',
        ],

        'Mail Enabled' => [
            'Type'        => 'yesno',
            'Description' => 'Enable the mail service',
            'Default'     => 'checked',
        ],

        'MySQL DB Limit' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '-1',
            'Description' => '[-1, 0 => 999]',
        ],

        'PgSQL DB Limit' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '-1',
            'Description' => '[-1, 0 => 999]',
        ],

        'Whitelist IP Limit' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '100',
            'Description' => '[-1, 0 => 4096]',
        ],

        'Mail filtering Enabled' => [
            'Type'    => 'yesno',
            'Default' => 'checked',
        ],

        'Mail Filtering Provider' => [
            'Type'        => 'dropdown',
            'Options'     => [
                'spamassassin' => 'Spam Assassin',
                'rspamd'       => 'Rspamd',
            ],
            'Default'     => 'rspamd',
            'Description' => 'Inbound spam filter',
        ],

        'SSH Enabled' => [
            'Type'    => 'yesno',
            'Default' => 'checked',
        ],

        'Enable SSL Service' => [
            'Type'    => 'yesno',
            'Default' => 'checked',
        ],

        'Limit Secondary Users' => [
            'Type'        => 'text',
            'Size'        => '10',
            'Default'     => '0',
            'Description' => '[-1, 0 => 4096]',
        ],
    ];
}

/**
 * Provision a new instance of a product/service.
 *
 * Attempt to provision a new instance of a given product/service. This is
 * called any time provisioning is requested inside of WHMCS. Depending upon the
 * configuration, this can be any of:
 * * When a new order is placed
 * * When an invoice for a new order is paid
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function apnscp_CreateAccount(array $params)
{
    // Setup Server Params
    $apnscp_apiep  = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain   = $params['domain'];
    $site_admin    = $params['username'];
    $site_password = $params['password'];

    // Setup Options Array
    $opts = [];

    // Addon Domains
    $opts['aliases.enabled'] = (bool) ((int) $params['configoption2'] > 0);
    $opts['aliases.max']     = (int) $params['configoption2']; // Actual Number of addon domains

    // Account Password
    $opts['auth.tpasswd'] = $site_password; // Plain Text Password for account

    //Bandwidth
    $opts['bandwidth.threshold'] = (int) $params['configoption3'] ?: 10000;
    $opts['bandwidth.units']     = 'GB'; //B,KB,MB,GB,TB
//    $opts['bandwidth.rollover']  = ''; // Day of month that BW resets (defaults to today)

//billing Account Linking
//    $opts['billing.invoice']        = 'WHMCS-Client-' . $params['userid']; // Invoice id to link to customer
    $opts['billing.parent_invoice'] = 'WHMCS-Client-' . $params['userid']; // Invoice id to link to customer

//cgroup Resource Enforcement
    $opts['cgroup.memory']    = $params['configoption4'] ?: 256;//'256'; // Limit memory usage of account in MB
    $opts['cgroup.cpu']       = $params['configoption5'] ?: 2000; // Not sure ...
    $opts['cgroup.cpuweight'] = $params['configoption6'] ?: 1024; // Allocate added weight to process tasks
    $opts['cgroup.proclimit'] = $params['configoption7'] ?: null; // [null, 0-4096] Limit account to # processes

//Disk Quota
    $opts['diskquota.quota']  = $params['configoption8'] ?: 4000; // [null,0-∞] Account storage quota
    $opts['diskquota.units']  = 'GB'; //[B,KB,MB,GB,TB] Supplied value has specified unit
    $opts['diskquota.fquota'] = $params['configoption9'] ?: null; // [null,0-∞] Account inode quota

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

//IPv6 Stuffs
//    $opts['ipinfo6.enabled']   = $params['configoption12'] === 'disabled' ? '0' : '1'; // [0,1] Assign account unique IPv6 address from pool
//    $opts['ipinfo6.namebased'] = $params['configoption12'] === 'shared' ? '1' : '0'; // [0,1] Site uses shared IP address (unique otherwise, see ipaddrs)

// Logging
    $opts['logs.enabled'] = '1'; // [0,1] Record web server access

// Mail
    $opts['mail.enabled'] = $params['configoption13'] ? '1' : '0'; // [0,1] Enable mail service

//MySQL
    $opts['mysql.enabled']     = (int) $params['configoption14'] === 0 ? '0' : '1'; // [0,1] MySQL database access. Required for Web App usage.
    $opts['mysql.dbaseadmin']  = $site_admin; // <string> Set mysql admin user
    $opts['mysql.dbaseprefix'] = $site_admin . '_'; // <<string> Set MySQL database prefix. Must end with '_'
    $opts['mysql.dbasenum']    = $params['configoption15'] === '-1' ? 'null' : $params['configoption15'];  // [null, 0-999] Limit total database count
    $opts['mysql.passwd']      = $site_password; // <string> Plain-text password for mysql user.

//PostgreSQL
    $opts['pgsql.enabled']     = (int) $params['configoption16'] === 0 ? '0' : '1'; // [0,1] Enable PostgreSQL database access. Required for Discourse usage.
    $opts['pgsql.dbaseadmin']  = $site_admin; // <string> Set pgsql admin user
    $opts['pgsql.dbaseprefix'] = $site_admin . '_'; // <string> Set PostgreSQL database prefix. Must end with '_'
    $opts['pgsql.dbasenum']    = $params['configoption16'] === '-1' ? 'null' : $params['configoption15']; // [null, 0-999] Limit total database count
    $opts['pgsql.passwd']      = $site_password; // <string> Plain-text password for pgsql user.

// Rampart
//    $opts['rampart.enabled']   = '1'; // [0,1] Delegate brute-force whitelisting
//    $opts['rampart.max']       = $params['configoption27'] ?: 100; // [-1, 0 => 4096] Maximum number of IP address whitelists.
    $opts['rampart.whitelist'] = [$params['model']->client->ip]; // IPv4 | IPv6 IPv4 + IPv6 addresses

//Site Info
    $opts['siteinfo.enabled']    = '1'; // [0,1] Core account attributes
    $opts['siteinfo.domain']     = $site_domain; // <string> Primary domain of the account
    $opts['siteinfo.admin_user'] = $site_admin; // <string> Administrative user of account
    $opts['siteinfo.email']      = $params['model']->client->email; // [email,[email1,email2...]] Contact address on account
    $opts['siteinfo.plan']       = $params['configoption1'];

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
    $opts['users.max']     = $params['configoption21'] === '-1' ? 'null' : $params['configoption21']; // [null, 0-4096] Limit up to # secondary users


    try
    {
        $client  = new Connector($apnscp_apikey, $apnscp_apiep);
        $request = $client->request();

        $request->admin_add_site($site_domain, $site_admin, $opts);
    }
    catch (Exception $e)
    {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            __FUNCTION__,
            ['params' => $params, 'options' => $opts],
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Suspend an instance of a product/service.
 *
 * Called when a suspension is requested. This is invoked automatically by WHMCS
 * when a product becomes overdue on payment or can be called manually by admin
 * user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function apnscp_SuspendAccount(array $params)
{
    $apnscp_apiep  = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain   = $params['domain'];

    try
    {
        $client  = new Connector($apnscp_apikey, $apnscp_apiep);
        $request = $client->request();

        $request->admin_deactivate_site($site_domain);
    }
    catch (Exception $e)
    {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Un-suspend instance of a product/service.
 *
 * Called when an un-suspension is requested. This is invoked
 * automatically upon payment of an overdue invoice for a product, or
 * can be called manually by admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function apnscp_UnsuspendAccount(array $params)
{
    $apnscp_apiep  = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain   = $params['domain'];

    try
    {
        $client  = new Connector($apnscp_apikey, $apnscp_apiep);
        $request = $client->request();

        $request->admin_activate_site($site_domain);
    }
    catch (Exception $e)
    {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Terminate instance of a product/service.
 *
 * Called when a termination is requested. This can be invoked automatically for
 * overdue products if enabled, or requested manually by an admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function apnscp_TerminateAccount(array $params)
{
    $apnscp_apiep  = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain   = $params['domain'];

    try
    {
        $client  = new Connector($apnscp_apikey, $apnscp_apiep);
        $request = $client->request();

        $request->admin_delete_site($site_domain);
    }
    catch (Exception $e)
    {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Change the password for an instance of a product/service.
 *
 * Called when a password change is requested. This can occur either due to a
 * client requesting it via the client area or an admin requesting it from the
 * admin side.
 *
 * This option is only available to client end users when the product is in an
 * active status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function apnscp_ChangePassword(array $params)
{
    $apnscp_apiep  = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain   = $params['domain'];
    $site_admin    = $params['username'];
    $site_password = $params['password'];

    try
    {
        $client  = new Connector($apnscp_apikey, $apnscp_apiep);
        $request = $client->request();

        $request->auth_change_password($site_password, $site_admin, $site_domain);
    }
    catch (Exception $e)
    {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Perform single sign-on for a given instance of a product/service.
 *
 * Called when single sign-on is requested for an instance of a product/service.
 *
 * When successful, returns a URL to which the user should be redirected.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function apnscp_ServiceSingleSignOn(array $params)
{
    $apnscp_apiep  = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain   = $params['domain'];
    $site_admin    = $params['username'];

    try
    {
        $client  = new Connector($apnscp_apikey, $apnscp_apiep);
        $request = $client->request();

        $esprit_id = $request->admin_hijack($site_domain, $site_admin, 'UI');

        $url = $apnscp_apiep . '/apps/dashboard?esprit_id=' . $esprit_id;

        return [
            'success'    => true,
            'redirectTo' => $url,
        ];
    }
    catch (Exception $e)
    {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success'  => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}
