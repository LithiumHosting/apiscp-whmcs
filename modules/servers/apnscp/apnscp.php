<?php
/**
 * apnscp Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2019
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

use WHMCS\Database\Capsule as DB;

if (! defined("WHMCS"))
{
    die("This file cannot be accessed directly");
}

require_once('lib/Connector.php');
require_once('lib/Helper.php');

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
        'ServiceSingleSignOnLabel' => 'Login to Panel',
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
    $plans = apnscp_getPlans();

    return [
        'apnscp Plan' => [
            'Type'        => 'dropdown',
            'Options'     => $plans,
            'Default'     => 'basic',
            'Description' => 'Choose a plan (auto populated)',
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
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];

    $opts = Helper::generateOptions($params);

    $cliCommand = Helper::generateCommand($opts, 'AddDomain');

    logModuleCall('apnscp', __FUNCTION__, ['CommandString' => $cliCommand], '', '');

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $client->admin_add_site($params['domain'], $params['username'], $opts);
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
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];
    $site_domain        = $params['domain'];

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $client->admin_deactivate_site($site_domain);
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
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];
    $site_domain        = $params['domain'];

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $client->admin_activate_site($site_domain);
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
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];
    $site_domain        = $params['domain'];

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $client->admin_delete_site($site_domain);
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
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];
    $site_domain        = $params['domain'];
    $site_admin         = $params['username'];
    $site_password      = $params['password'];

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $client->auth_change_password($site_password, $site_admin, $site_domain);
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
 * Upgrade or downgrade an instance of a product/service.
 *
 * Called to apply any change in product assignment or parameters. It
 * is called to provision upgrade or downgrade orders, as well as being
 * able to be invoked manually by an admin user.
 *
 * This same function is called for upgrades and downgrades of both
 * products and configurable options.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function apnscp_ChangePackage(array $params)
{
    // Setup Server Params
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];

    $opts['siteinfo.plan'] = $params['configoption1'];

    $cliCommand = Helper::generateCommand($opts, 'EditDomain');

    logModuleCall('apnscp', __FUNCTION__, ['CommandString' => $cliCommand], '', '');

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $client->admin_edit_site($params['domain'], $opts);
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
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];
    $site_domain        = $params['domain'];
    $site_admin         = $params['username'];
    $app                = App::get_req_var('app');
    $extra              = [];
    $allowed_apps       = [
        'usermanage',
        'mailboxroutes',
        'vacation',
        'filemanager',
        'domainmanager',
        'bandwidthbd',
        'crontab',
        'subdomains',
        'changemysql',
        'phpmyadmin',
        'webapps',
        'terminal',
    ];

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $session_id = $client->admin_hijack($site_domain, $site_admin, 'UI');

        if (! isset($app) OR ! in_array($app, $allowed_apps))
        {
            $app = 'dashboard';
        }

        if ($app === 'subdomains')
        {
            $extra['mode'] = 'add';
        }

        $extra['esprit_id'] = $session_id;
        $query              = http_build_query($extra);

        $url = "${apnscp_apiendpoint}/apps/${app}?${query}";

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


/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function apnscp_ClientArea(array $params)
{
    return [
        'overrideDisplayTitle'           => ucfirst($params['domain']),
        'tabOverviewReplacementTemplate' => 'overview.tpl',
    ];
}

function apnscp_getPlans()
{
    $server             = DB::table('tblservers')->where('type', 'apnscp')->first();
    $apnscp_apiendpoint = ($server->secure === 'on' ? 'https' : 'http') . '://' . $server->hostname . ':' . ($server->secure === 'on' ? '2083' : '2082');
    $apnscp_apikey      = decrypt($server->password);

    try
    {
        $adminId = \session_id();
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $plans = $client->admin_list_plans();

        return array_combine($plans, $plans);
    }
    catch (Exception $e)
    {
        // No easy way to return an error so we'll default to the basic plan only
        return ['basic' => 'basic (api call failed)'];
    }
}

function apnscp_UsageUpdate($params)
{
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];
    $serverid           = $params['serverid'];
    $stats              = [];

    # Run connection to retrieve usage for all domains/accounts on $serverid
    $adminId     = \session_id();
    $adminClient = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);
    $domains     = $adminClient->admin_get_domains();
    foreach ($domains as $domain)
    {
        $session_id = $adminClient->admin_hijack($domain);
        $userClient = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $session_id);
        $bw         = $userClient->site_get_bandwidth_usage();
        $quota      = $userClient->site_get_account_quota();

        $stats[] = ['domain' => $domain, 'bw' => $bw, 'disk' => $quota];
    }

    # Now loop through results and update DB
    foreach ($stats AS $values)
    {
        update_query('tblhosting', [
            'diskusage'  => $values['disk']['qused'] / 1024,
            'disklimit'  => $values['disk']['qhard'] / 1024,
            'bwusage'    => ($values['bw']['used'] / 1024) / 1024,
            'bwlimit'    => ($values['bw']['total'] / 1024) / 1024,
            'lastupdate' => 'now()',
        ], ['server' => $serverid, 'domain' => $values['domain']]);
    }
}