<?php
/**
 * apnscp/ApisCP Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2026
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

use WHMCS\Database\Capsule as DB;
use WHMCS\Service\Status;

if (! defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once(__DIR__ . '/lib/ApisConnector.php');
require_once(__DIR__ . '/lib/Helper.php');

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
function apnscp_MetaData(): array
{
    return [
        'DisplayName'              => 'apnscp',
        'APIVersion'               => '1.1',
        'RequiresServer'           => true,
        'DefaultNonSSLPort'        => '2082',
        'DefaultSSLPort'           => '2083',
        'ServiceSingleSignOnLabel' => 'Login to LiPanel',
        'AdminSingleSignOnLabel'   => 'Login to ApisCP',
        'ListAccountsUniqueIdentifierDisplayName' => 'Domain',
        'ListAccountsUniqueIdentifierField'       => 'domain',
        'ListAccountsProductField'                => 'configoption1',
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
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 *
 * @return array
 */
function apnscp_ConfigOptions(): array
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
 */
function apnscp_CreateAccount(array $params): string
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $domain     = strtolower($params['domain']);
    $opts       = Helper::generateOptions($params);
    $cliCommand = Helper::generateCommand($opts, 'AddDomain');

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $client->admin_add_site($domain, $params['username'], $opts);
        Helper::apnscpValidateCustomFields($params['pid']);

        logModuleCall('apnscp', 'Create', ['Request' => Helper::formatXml($client->__getLastRequest()), 'CommandString' => $cliCommand], Helper::formatXml($client->__getLastResponse()));
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'Create',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? Helper::formatXml($client->__getLastResponse()) : '')
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
 */
function apnscp_SuspendAccount(array $params): string
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $site_domain    = strtolower($params['domain']);
    $opts['reason'] = $params['suspendreason'];

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $client->admin_deactivate_site($site_domain, $opts);

        logModuleCall('apnscp', 'Suspend', ['Request' => Helper::formatXml($client->__getLastRequest())], Helper::formatXml($client->__getLastResponse()));
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'Suspend',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? Helper::formatXml($client->__getLastResponse()) : '')
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
 */
function apnscp_UnsuspendAccount(array $params): string
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $site_domain = strtolower($params['domain']);

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $client->admin_activate_site($site_domain);

        logModuleCall('apnscp', 'Unsuspend', ['Request' => Helper::formatXml($client->__getLastRequest())], Helper::formatXml($client->__getLastResponse()));
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'Unsuspend',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? Helper::formatXml($client->__getLastResponse()) : '')
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
 * Treats an empty message or "Error Fetching http headers" as success, since
 * that almost certainly indicates a SOAP timeout after the panel already
 * processed the deletion command.
 *
 * @param array $params common module parameters
 *
 * @return string "success" or an error message
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_TerminateAccount(array $params): string
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $site_domain = strtolower($params['domain']);

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        
        // Delete Accounts when WHMCS says so...
        // Comment Out to disable Termination/Cancellation
//        $client->admin_delete_site($site_domain, ['force' => true]);
        
        // Defer Deletion to allow recovery in the event you can retain a customer or they change their mind.
        // Managed in hooks.php in the Daily Cron Job task
        // Uncomment to enable deferred cancellation.
        $client->admin_deactivate_site($site_domain, ['reason' => 'Deferred Account Cancellation']);

        logModuleCall(
            'apnscp',
            'Terminate',
            "Request: " . Helper::formatXml($client->__getLastRequest()) . "\n\nHeaders:" . $client->__getLastRequestHeaders(),
            "Response: " . $client->__getLastResponse() . "\n\nHeaders: " . $client->__getLastResponseHeaders()
        );

        return 'success';
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'Terminate',
            $client ? "Request: " . Helper::formatXml($client->__getLastRequest()) . "\n\nHeaders:" . $client->__getLastRequestHeaders() : '',
            "Exception: " . $e->getMessage() . "\n\n" .
            $e->getLine() . "\n\n" .
            $e->getTraceAsString() . "\n\n" .
            ($client ? "Response: " . $client->__getLastResponse() . "\n\nHeaders: " . $client->__getLastResponseHeaders() : '')
        );

        if (empty($e->getMessage()) || ($e->getMessage() === 'Error Fetching http headers')) {
            // Almost certainly a SOAP timeout: the panel processed the deletion
            // but didn't reply in time. Treat as success.
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
 */
function apnscp_ChangePassword(array $params): string
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $site_domain   = strtolower($params['domain']);
    $site_admin    = $params['username'];
    $site_password = $params['password'];

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $client->auth_change_password($site_password, $site_admin, $site_domain);

        logModuleCall('apnscp', 'ChangePassword', ['Request' => Helper::formatXml($client->__getLastRequest())], Helper::formatXml($client->__getLastResponse()));
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'ChangePassword',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? $client->__getLastResponseHeaders() . "\n\n" . Helper::formatXml($client->__getLastResponse()) : '')
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
 */
function apnscp_ChangePackage(array $params): string
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $domain                = strtolower($params['domain']);
    $opts['siteinfo.plan'] = $params['configoption1'];
    $extra['reset']        = true;

    $cliCommand = Helper::generateCommand($opts, 'EditDomain');

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $client->admin_edit_site($domain, $opts, $extra);

        logModuleCall('apnscp', 'ChangePackage', ['Request' => Helper::formatXml($client->__getLastRequest()), 'CommandString' => $cliCommand], Helper::formatXml($client->__getLastResponse()));
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'ChangePackage',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? Helper::formatXml($client->__getLastResponse()) : '')
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Perform single sign-on for a given instance of a product/service.
 *
 * Called when single sign-on is requested for an instance of a product/service.
 * Hijacks the admin session for the given site and returns a redirect URL to
 * the requested app (defaults to "dashboard" if the requested app is not in
 * the allowed list).
 *
 * @param array $params common module parameters
 *
 * @return array{success: bool, redirectTo?: string, errorMsg?: string}
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_ServiceSingleSignOn(array $params): array
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $site_domain  = strtolower($params['domain']);
    $site_admin   = $params['username'];
    $app          = App::get_req_var('app');
    $extra        = [];
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

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $session_id = $client->admin_hijack($site_domain, $site_admin, 'UI');

        if (! isset($app) || ! in_array($app, $allowed_apps)) {
            $app = 'dashboard';
        }

        if ($app === 'subdomains') {
            $extra['mode'] = 'add';
        }

        $extra['esprit_id'] = $session_id;
        $query = http_build_query($extra);
        $url   = "{$apnscp_apiendpoint}/apps/{$app}?{$query}";

        return [
            'success'    => true,
            'redirectTo' => $url,
        ];
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'SSO',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? Helper::formatXml($client->__getLastResponse()) : '')
        );

        return [
            'success'  => false,
            'errorMsg' => $e->getMessage(),
        ];
    }
}

/**
 * Perform single sign-on into the apnscp admin (server) control panel.
 *
 * Called when an admin clicks the "Login to ApisCP" button on the admin
 * service page. The connecting API key authenticates as the server
 * administrator, so the admin identity is read from auth_session_info and a UI
 * session is hijacked for that account, returning a redirect into the admin
 * dashboard.
 *
 * The apnscp administrator has no domain of its own (site_id 0), so the server
 * hostname is passed as the hijack site when the session reports no domain.
 *
 * @param array $params common module parameters (includes server connection details).
 *
 * @return array{success: bool, redirectTo?: string, errorMsg?: string}
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_AdminSingleSignOn(array $params): array
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $session   = (array) $client->auth_session_info();
        $adminUser = (string) ($session['username'] ?? '');
        $adminSite = (string) ($session['domain'] ?: $params['serverhostname']);

        $session_id = $client->admin_hijack($adminSite, $adminUser, 'UI');

        $url = "{$apnscp_apiendpoint}/apps/dashboard?" . http_build_query(['esprit_id' => $session_id]);

        logModuleCall('apnscp', 'AdminSSO', ['Request' => Helper::formatXml($client->__getLastRequest())], Helper::formatXml($client->__getLastResponse()));

        return [
            'success'    => true,
            'redirectTo' => $url,
        ];
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'AdminSSO',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? Helper::formatXml($client->__getLastResponse()) : '')
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
 * Returns the template override used to render the service overview in the
 * client area, using the service domain as the display title.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_ClientArea(array $params): array
{
    return [
        'overrideDisplayTitle'           => $params['domain'],
        'tabOverviewReplacementTemplate' => 'overview.tpl',
    ];
}

/**
 * Fetch all plans from the first reachable enabled apnscp server.
 *
 * Called by the 'ApisCP Plan' config option loader so the product dropdown is
 * auto-populated. Servers are queried in order; the first successful response
 * is returned immediately. Falls back to a single "basic (api call failed)"
 * entry if no server responds within the 15-second timeout.
 *
 * @return array<string, string> Associative array of plan_name => plan_name pairs.
 */
function apnscp_getPlans(): array
{
    $servers = DB::table('tblservers')
        ->where('type', 'apnscp')
        ->where('disabled', 0)
        ->get();

    $prevTimeout = ini_get('default_socket_timeout');

    foreach ($servers as $server) {
        $client = null;
        try {
            if (!class_exists('ApisConnector')) {
                require(__DIR__ . '/lib/ApisConnector.php');
            }

            $apnscp_apiendpoint = ($server->secure === 'on' ? 'https' : 'http') . '://' . $server->hostname . ':' . ($server->secure === 'on' ? '2083' : '2082');
            $apnscp_apikey      = decrypt($server->password);

            ini_set('default_socket_timeout', 15);
            $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
            $plans  = $client->admin_list_plans();
            ini_set('default_socket_timeout', $prevTimeout);

            logModuleCall(
                'apnscp',
                'GetPlans',
                ['server' => $server->name, 'endpoint' => $apnscp_apiendpoint, 'request' => Helper::formatXml($client->__getLastRequest())],
                Helper::formatXml($client->__getLastResponse()),
                $plans
            );

            $return = [];
            foreach ($plans as $plan) {
                $return[$plan] = $plan;
            }

            return $return;
        } catch (\Throwable $e) {
            ini_set('default_socket_timeout', $prevTimeout);
            logModuleCall(
                'apnscp',
                'GetPlans',
                ['server' => $server->name, 'request' => $client ? Helper::formatXml($client->__getLastRequest()) : 'Client not initialized'],
                $e->getMessage()
            );
            // Try the next server
        }
    }

    return ['basic' => 'basic (api call failed)'];
}

/**
 * Synchronise disk, bandwidth, and SiteID data for all apnscp accounts on a server.
 *
 * Called by WHMCS on a scheduled basis to keep usage statistics up to date.
 * For each site reported by the apnscp server, the corresponding hosting
 * record is updated with current quota and bandwidth figures, and the
 * SiteID custom field is written (or created if missing).
 *
 * Custom fields are fetched once per unique package ID and cached for the
 * duration of the run to avoid redundant database queries.
 *
 * @param array $params Common module parameters, including server connection details and serverid.
 *
 * @return void
 */
function apnscp_UsageUpdate(array $params): void
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $serverid = $params['serverid'];

    $client = null;
    try {
        $client       = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $siteInfoArr  = $client->admin_collect(['siteinfo.domain']);
        $storageArr   = $client->admin_get_usage('storage');
        $bandwidthArr = $client->admin_get_usage('bandwidth');

        $products = DB::table('tblproducts')->where('type', 'hostingaccount')->where('servertype', 'apnscp')->get();
        foreach ($products as $product) {
            Helper::apnscpValidateCustomFields($product->id);
        }

        $customFieldsCache = [];
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

                if (!isset($customFieldsCache[$service->packageid])) {
                    $customFieldsCache[$service->packageid] = Helper::apnscpGetCustomFields($service->packageid);
                }
                $customFields = $customFieldsCache[$service->packageid];
                Helper::apnscpAddCustomFieldValue($service->id, $customFields['SiteID']['id'], $site);
            }
        }
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'UsageUpdate',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString()
        );
    }
}

/**
 * Enumerate every account on an apnscp server for the Sync Accounts tool.
 *
 * Powers the "Sync Accounts" button in WHMCS (Configuration > Servers, or
 * Utilities > Server Sync). WHMCS calls this once per server and matches the
 * returned accounts against existing services using the unique identifier
 * declared in apnscp_MetaData (the domain), offering to import any account
 * that has no matching service.
 *
 * A single admin_collect call returns the relevant siteinfo for all sites.
 * apnscp keys the result by internal site ID and, for each site, returns the
 * domain and an `active` flag at the top level alongside the per-module
 * sub-arrays (e.g. ['active' => true, 'siteinfo' => ['domain' => ..., 'plan'
 * => ..., 'admin_user' => ...], 'domain' => ...]). Values are read defensively
 * so servers that return them flat still work.
 *
 * Status is taken from the top-level `active` flag, which reflects apnscp's
 * activate/deactivate state — the same state apnscp_SuspendAccount toggles via
 * admin_deactivate_site — making it the reliable suspended indicator (the
 * billing.suspended field comes back null on current builds).
 *
 * The account creation date comes from billing.ctime (a Unix timestamp set
 * when the site was created); it falls back to today only if that value is
 * missing. The 'created' value must be a valid "Y-m-d H:i:s" string, since
 * WHMCS parses it with that exact format when matching accounts.
 *
 * @param array $params Standard server connection params (serverhostname,
 *                      serverport, serverpassword, serverhttpprefix, serverip, etc.).
 *
 * @return array{success: bool, accounts: array<int, array<string, string>>, error?: string}
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_ListAccounts(array $params): array
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $serverIp = $params['serverip'] ?? '';

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $sites = (array) $client->admin_collect([
            'siteinfo.domain',
            'siteinfo.plan',
            'siteinfo.admin_user',
            'siteinfo.email',
            'billing.ctime',
        ]);

        logModuleCall(
            'apnscp',
            'ListAccounts',
            ['endpoint' => $apnscp_apiendpoint, 'request' => Helper::formatXml($client->__getLastRequest())],
            Helper::formatXml($client->__getLastResponse())
        );

        $accounts = [];
        foreach ($sites as $siteId => $info) {
            if (! is_array($info)) {
                continue;
            }

            $siteinfo = is_array($info['siteinfo'] ?? null) ? $info['siteinfo'] : [];
            $billing  = is_array($info['billing'] ?? null) ? $info['billing'] : [];

            // Domain and the active flag live at the top level of each site,
            // alongside the per-module sub-arrays.
            $domain = strtolower((string) ($info['domain'] ?? $siteinfo['domain'] ?? ''));
            if ($domain === '') {
                continue;
            }

            $username = (string) ($siteinfo['admin_user'] ?? '');
            $active   = (bool) ($info['active'] ?? true);

            // billing.ctime is the account creation Unix timestamp; fall back to
            // today only when it's missing.
            $ctime   = $billing['ctime'] ?? null;
            $created = (is_numeric($ctime) && $ctime > 0)
                ? date('Y-m-d H:i:s', (int) $ctime)
                : date('Y-m-d H:i:s');

            $accounts[] = [
                'uniqueIdentifier' => $domain,
                'name'             => $username !== '' ? $username : $domain,
                'email'            => (string) ($siteinfo['email'] ?? ''),
                'username'         => $username,
                'domain'           => $domain,
                'product'          => (string) ($siteinfo['plan'] ?? ''),
                'primaryip'        => $serverIp,
                'created'          => $created,
                'status'           => $active ? Status::ACTIVE : Status::SUSPENDED,
            ];
        }

        return ['success' => true, 'accounts' => $accounts];
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'ListAccounts',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString() . "\n\n" . ($client ? Helper::formatXml($client->__getLastResponse()) : '')
        );

        return ['success' => false, 'accounts' => [], 'error' => $e->getMessage()];
    }
}

/**
 * Collect remote server statistics for display on the admin Servers page.
 *
 * Called by WHMCS (and cached) to show live server health alongside the server
 * in the admin area. Gathers the apnscp control-panel and platform versions,
 * load averages, uptime, PHP/MySQL/PostgreSQL/kernel versions, and the total
 * account count. The result is passed back to apnscp_RenderRemoteMetaData for
 * formatting.
 *
 * @param array $params common module parameters (includes server connection details).
 *
 * @return array Metadata on success, or ['success' => false, 'error' => string] on failure.
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_GetRemoteMetaData(array $params): array
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $cp       = (array) $client->misc_cp_version('');
        $platform = (string) $client->misc_platform_version();
        $load     = (array) $client->common_get_load();
        $uptime   = (string) $client->common_get_uptime(true);
        $php      = (string) $client->common_get_php_version();
        $mysql    = $client->common_get_mysql_version();
        $pgsql    = (string) $client->common_get_postgresql_version();
        $kernel   = (string) $client->common_get_kernel_version();
        $accounts = count((array) $client->admin_collect(['siteinfo.domain']));

        $cpVersion = isset($cp['ver_maj'])
            ? $cp['ver_maj'] . '.' . ($cp['ver_min'] ?? 0) . '.' . ($cp['ver_patch'] ?? 0)
            : '-';

        return [
            'cp_version' => $cpVersion,
            'platform'   => $platform ?: '-',
            'php'        => $php ?: '-',
            'mysql'      => Helper::formatDbVersion($mysql),
            'pgsql'      => Helper::formatDbVersion($pgsql),
            'kernel'     => trim(preg_replace('/\s+/', ' ', $kernel)),
            'load'       => [
                '1'  => $load[1] ?? $load['1'] ?? '0',
                '5'  => $load[5] ?? $load['5'] ?? '0',
                '15' => $load[15] ?? $load['15'] ?? '0',
            ],
            'uptime'   => $uptime ?: '-',
            'accounts' => $accounts,
        ];
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'GetRemoteMetaData',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString()
        );

        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Render the statistics gathered by apnscp_GetRemoteMetaData as HTML.
 *
 * Reads the cached metadata from $params['remoteData']->metaData and formats it
 * for the admin Servers page. Returns an empty string when no metadata is
 * available (e.g. the server was unreachable at collection time).
 *
 * @param array $params common module parameters, with 'remoteData' carrying the cached metadata.
 *
 * @return string HTML fragment describing the server, or '' when unavailable.
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_RenderRemoteMetaData(array $params): string
{
    $remoteData = $params['remoteData'] ?? null;
    if (! $remoteData) {
        return '';
    }

    $metaData = (array) $remoteData->metaData;
    if (empty($metaData) || ! empty($metaData['error'])) {
        return '';
    }

    $cpVersion = $metaData['cp_version'] ?? '-';
    $platform  = $metaData['platform'] ?? '-';
    $php       = $metaData['php'] ?? '-';
    $mysql     = $metaData['mysql'] ?? '-';
    $pgsql     = $metaData['pgsql'] ?? '-';
    $kernel    = $metaData['kernel'] ?? '-';
    $uptime    = $metaData['uptime'] ?? '-';
    $accounts  = $metaData['accounts'] ?? '0';
    $load      = $metaData['load'] ?? ['1' => '0', '5' => '0', '15' => '0'];
    $loadStr   = ($load['1'] ?? '0') . ' ' . ($load['5'] ?? '0') . ' ' . ($load['15'] ?? '0');

    return "ApisCP Version: " . $cpVersion . " (Platform " . $platform . ")<br>\n"
        . "Accounts: " . $accounts . "<br>\n"
        . "Load Averages: " . $loadStr . "<br>\n"
        . "Uptime: " . $uptime . "<br>\n"
        . "PHP: " . $php . " &nbsp;|&nbsp; MySQL: " . $mysql . " &nbsp;|&nbsp; PostgreSQL: " . $pgsql . "<br>\n"
        . "Kernel: " . $kernel;
}

/**
 * Report the number of accounts on the server for the admin Servers page.
 *
 * Called by WHMCS (on a schedule and via the per-server refresh button) to
 * populate the "Remote Usage Stats" column. Without this function the column
 * shows 0. Every apnscp site is administered by the connecting admin key, so
 * the owned and total counts are the same.
 *
 * @param array $params common module parameters (includes server connection details).
 *
 * @return array{success: bool, totalAccounts?: int, ownedAccounts?: int, error?: string}
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function apnscp_GetUserCount(array $params): array
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $client = null;
    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);

        $count = count((array) $client->admin_collect(['siteinfo.domain']));

        return [
            'success'       => true,
            'totalAccounts' => $count,
            'ownedAccounts' => $count,
        ];
    } catch (\Throwable $e) {
        logModuleCall(
            'apnscp',
            'GetUserCount',
            $client ? Helper::formatXml($client->__getLastRequest()) : '',
            $e->getMessage() . "\n\n" . $e->getTraceAsString()
        );

        return ['success' => false, 'error' => $e->getMessage()];
    }
}