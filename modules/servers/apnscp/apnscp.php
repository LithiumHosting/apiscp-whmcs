<?php
/**
 * apnscp Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2019
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

use WHMCS\Database\Capsule as DB;
use WHMCS\Service\Status;

if (! defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once('lib/ApisConnector.php');
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
    return [
        'DisplayName'              => 'apnscp',
        'APIVersion'               => '1.0', // Use API Version 1.1
        'RequiresServer'           => true, // Set true if module requires a server to work
        'DefaultNonSSLPort'        => '2082', // Default Non-SSL Connection Port
        'DefaultSSLPort'           => '2083', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to ApisCP',
        //        'AdminSingleSignOnLabel'                  => 'Login to ApisCP as Admin',
        //        // The display name of the unique identifier to be displayed on the table output
        //        'ListAccountsUniqueIdentifierDisplayName' => 'Domain',
        //        // The field in the return that matches the unique identifier
        //        'ListAccountsUniqueIdentifierField'       => 'domain',
        //        // The config option indexed field from the _ConfigOptions function that identifies the product on the remote system
        //        'ListAccountsProductField'                => 'configoption1',
    ];
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
        'ApisCP Plan' => [
            'Type'        => 'dropdown',
            'Default'     => 'basic',
            'Description' => 'Choose a plan (auto populated)<br>Format: &lt;server&gt; - &lt;plan&gt;',
            'Loader'      => 'apnscp_getPlans',
            'SimpleMode'  => true,
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
 * @return string "success" or an error message
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_CreateAccount(array $params)
{
    // Setup Server Params
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];

    $domain = strtolower($params['domain']);

    $opts = Helper::generateOptions($params);

    $cliCommand = Helper::generateCommand($opts, 'AddDomain');

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $client->admin_add_site($domain, $params['username'], $opts);
        Helper::apnscpValidateCustomFields($params['pid']);

        logModuleCall('apnscp', 'Create', ['Request' => str_ireplace('><', ">\n<", $client->__getLastRequest()), 'CommandString' => $cliCommand], str_ireplace('><', ">\n<", $client->__getLastResponse()));
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            'Create',
            str_ireplace('><', ">\n<", $client->__getLastRequest()),
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . str_ireplace('><', ">\n<", $client->__getLastResponse())
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
 * @return string "success" or an error message
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_SuspendAccount(array $params)
{
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain = strtolower($params['domain']);

    $opts['reason'] = $params['suspendreason'];

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $client->admin_deactivate_site($site_domain, $opts);

        logModuleCall('apnscp', 'Suspend', ['Request' => str_ireplace('><', ">\n<", $client->__getLastRequest())], str_ireplace('><', ">\n<", $client->__getLastResponse()));
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            'Suspend',
            str_ireplace('><', ">\n<", $client->__getLastRequest()),
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . str_ireplace('><', ">\n<", $client->__getLastResponse())
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
 * @return string "success" or an error message
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_UnsuspendAccount(array $params)
{
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain = strtolower($params['domain']);

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $client->admin_activate_site($site_domain);

        logModuleCall('apnscp', 'Unsuspend', ['Request' => str_ireplace('><', ">\n<", $client->__getLastRequest())], str_ireplace('><', ">\n<", $client->__getLastResponse()));
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            'Unsuspend',
            str_ireplace('><', ">\n<", $client->__getLastRequest()),
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . str_ireplace('><', ">\n<", $client->__getLastResponse())
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
 * @return string "success" or an error message
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_TerminateAccount(array $params)
{
    // Logging
    $module = 'apnscp';
    $method = 'terminate';
    // end

    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain = strtolower($params['domain']);

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $opts['force'] = true;
        $client->admin_delete_site($site_domain, $opts);

//        $opts['reason'] = 'Customer Requested Cancellation';
//        $client->admin_deactivate_site($site_domain, $opts);

        logModuleCall(
            $module,
            $method,
            "Request: " . str_ireplace('><', ">\n<", $client->__getLastRequest()) . "\n\nHeaders:" . $client->__getLastRequestHeaders(),
            "Response: " . $client->__getLastResponse() . "\n\n" .
            "Headers: " . $client->__getLastResponseHeaders()
        );

        return 'success';
    } catch (Exception $e) {
        logModuleCall(
            $module,
            $method,
            "Request: " . str_ireplace('><', ">\n<", $client->__getLastRequest()) . "\n\nHeaders:" . $client->__getLastRequestHeaders(),
            "Exception: " . $e->getMessage() . "\n\n" .
            $e->getLine() . "\n\n" .
            $e->getTraceAsString() . "\n\n" .
            "Response: " . $client->__getLastResponse() . "\n\n" .
            "Headers: " . $client->__getLastResponseHeaders()
        );

        if (empty($e->getMessage()) || ($e->getMessage() === 'Error Fetching http headers')) {
            /**
             * This is almost certainly a timeout error where the panel received and processed the termination command but didn't reply in time and SOAP threw an error
             * If I'm wrong, I'll re-think this approach :)
             */

            return 'success';
        }

        return $e->getMessage();
    }
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
 * @return string "success" or an error message
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_ChangePassword(array $params)
{
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain = strtolower($params['domain']);
    $site_admin = $params['username'];
    $site_password = $params['password'];

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $client->auth_change_password($site_password, $site_admin, $site_domain);

        logModuleCall('apnscp', 'ChangePassword', ['Request' => str_ireplace('><', ">\n<", $client->__getLastRequest())], str_ireplace('><', ">\n<", $client->__getLastResponse()));
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            'ChangePassword',
            str_ireplace('><', ">\n<", $client->__getLastRequest()),
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . $client->__getLastResponseHeaders() . "\n\n" . str_ireplace('><', ">\n<", $client->__getLastResponse())
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
 * @return string "success" or an error message
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_ChangePackage(array $params)
{
    // Setup Server Params
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];

    $domain = strtolower($params['domain']);

    $opts['siteinfo.plan'] = $params['configoption1'];
    $extra['reset'] = true;

    $cliCommand = Helper::generateCommand($opts, 'EditDomain');

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $client->admin_edit_site($domain, $opts, $extra);

        logModuleCall('apnscp', 'ChangePackage', ['Request' => str_ireplace('><', ">\n<", $client->__getLastRequest()), 'CommandString' => $cliCommand], str_ireplace('><', ">\n<", $client->__getLastResponse()));
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            'ChangePackage',
            str_ireplace('><', ">\n<", $client->__getLastRequest()),
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . str_ireplace('><', ">\n<", $client->__getLastResponse())
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
 * @return array
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_ServiceSingleSignOn(array $params)
{
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $site_domain = strtolower($params['domain']);
    $site_admin = $params['username'];
    $app = App::get_req_var('app');
    $extra = [];
    $allowed_apps = [
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
        'whitelist',
    ];

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $session_id = $client->admin_hijack($site_domain, $site_admin, 'UI');

        if (! isset($app) or ! in_array($app, $allowed_apps)) {
            $app = 'dashboard';
        }

        if ($app === 'subdomains') {
            $extra['mode'] = 'add';
        }

        $extra['esprit_id'] = $session_id;
        $query = http_build_query($extra);

        $url = "${apnscp_apiendpoint}/apps/${app}?${query}";

        return [
            'success'    => true,
            'redirectTo' => $url,
        ];
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'apnscp',
            'SSO',
            str_ireplace('><', ">\n<", $client->__getLastRequest()),
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . str_ireplace('><', ">\n<", $client->__getLastResponse())
        );

        return [
            'success'  => false,
            'errorMsg' => $e->getMessage(),
        ];
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
 * @return array
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */
function apnscp_ClientArea(array $params)
{
    return [
        'overrideDisplayTitle'           => $params['domain'],
        'tabOverviewReplacementTemplate' => 'overview.tpl',
    ];
}

function apnscp_getPlans()
{
    $servers = DB::table('tblservers')->where('type', 'apnscp')->get();

    try {
        foreach ($servers as $server) {
            $apnscp_apiendpoint = ($server->secure === 'on' ? 'https' : 'http') . '://' . $server->hostname . ':' . ($server->secure === 'on' ? '2083' : '2082');
            $apnscp_apikey = decrypt($server->password);

            $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

            $plans = $client->admin_list_plans();

            foreach ($plans as $plan) {
                $return[$plan] = $server->name . ' - ' . $plan;
            }
        }

        return $return;
    } catch (Exception $e) {
        // No easy way to return an error so we'll default to the basic plan only
        return ['basic' => 'basic (api call failed)'];
    }
}

function apnscp_UsageUpdate($params)
{
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey = $params['serverpassword'];
    $serverid = $params['serverid'];

    $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
    $siteInfoArr = $client->admin_collect(['siteinfo.domain']);
    $storageArr = $client->admin_get_usage('storage');
    $bandwidthArr = $client->admin_get_usage('bandwidth');

    $products = DB::table('tblproducts')->where('type', 'hostingaccount')->where('servertype', 'apnscp')->get();
    foreach ($products as $product) {
        // This is less than efficient, but it works and is only a few extra queries!
        Helper::apnscpValidateCustomFields($product->id);
    }

    foreach ($siteInfoArr as $site => $values) {
        $service = DB::table('tblhosting')
            ->where('server', $serverid)
            ->where('domain', $values['domain'])
            ->first();

        if (! empty($service)) {
            DB::table('tblhosting')
                ->where('id', $service->id)
                ->update([
                    'diskusage'  => $storageArr[$site]['qused'] / 1024,
                    'disklimit'  => $storageArr[$site]['qhard'] / 1024,
                    'bwusage'    => ($bandwidthArr[$site]['sum'] / 1024) / 1024,
                    'bwlimit'    => ($bandwidthArr[$site]['threshold'] / 1024) / 1024,
                    'lastupdate' => DB::raw('now()'),
                ]);

            $customFields = Helper::apnscpGetCustomFields($service->packageid);
            Helper::apnscpAddCustomFieldValue($service->id, $customFields['SiteID']['id'], $site);
        }
    }
}


/**
 * Log module call.
 *
 * @param string $module The name of the module
 * @param string $action The name of the action being performed
 * @param string|array $requestString The input parameters for the API call
 * @param string|array $responseData The response data from the API call
 * @param string|array $processedData The resulting data after any post processing (eg. json decode, xml decode, etc...)
 * @param array $replaceVars An array of strings for replacement
 */
//logModuleCall($module, $action, $requestString, $responseData, $processedData, $replaceVars);