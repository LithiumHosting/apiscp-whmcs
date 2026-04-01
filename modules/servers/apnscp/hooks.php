<?php
/**
 * apnscp/ApisCP Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2026
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

require_once(__DIR__ . '/lib/ApisConnector.php');
require_once(__DIR__ . '/lib/Helper.php');

add_hook('ClientAreaPageProductDetails', 1, function ($vars) {

    $server = $vars['serverdata'];

    // WHMCS MAGIC!!!1
    $ca = new WHMCS\ClientArea();
    $legacyClient = new WHMCS\Client($ca->getClient());
    $service = new WHMCS\Service($vars['serviceid'], $legacyClient->getID());
    $product = WHMCS\Product\Product::where('id', $service->getData('packageid'))->first();

    if ($product->module !== "apnscp") {
        return null;
    }

    $ip = apnscp_getPublicIp();

    if ($ip === null) {
        return ['apisVars' => ['is_banned' => false, 'jails' => [], 'ip' => null, 'rampart_enabled' => false, 'debug' => ['ip' => null, 'is_banned' => 'No', 'jails_raw' => [], 'error' => 'Could not determine public IP.']]];
    }

    $knownJails = [
        'f2b-sshd'         => 'SSH (f2b-sshd)',
        'f2b-postfix'      => 'SMTP (f2b-postfix)',
        'f2b-postfix-sasl' => 'SMTP (f2b-postfix-sasl)',
        'f2b-dovecot'      => 'IMAP/POP3 (f2b-dovecot)',
        'f2b-vsftpd'       => 'FTP (f2b-vsftpd)',
        'f2b-shield'       => 'Apache Mod Shield (f2b-shield)',
        'f2b-spambots'     => 'Irregular Mail Patterns (f2b-spambots)',
        'f2b-recidive'     => 'Repeatedly getting banned (f2b-recidive)',
        'f2b-pgsql'        => 'pgSQL (f2b-pgsql)',
        'f2b-mysqld'       => 'MySQL (f2b-mysqld)',
        'f2b-malware'      => 'Malware Scans (f2b-malware)',
    ];

    $serverParams = [
        'serverhttpprefix' => $server['secure'] === 'on' ? 'https' : 'http',
        'serverhostname'   => $server['hostname'],
        'serverport'       => $server['port'] ?: '2083',
        'serverpassword'   => decrypt($server['password']),
        'domain'           => strtolower($service->getData('domain')),
        'username'         => $service->getData('username'),
        'ip'               => $ip,
    ];

    $result = apnscp_checkIP($serverParams);

    $jails = [];
    foreach ($result['jails'] as $jail) {
        $jails[] = $knownJails[$jail] ?? $jail;
    }

    $debug = [
        'ip'        => $ip,
        'is_banned' => $result['is_banned'] ? 'Yes' : 'No',
        'jails_raw' => $result['jails'],
        'error'     => $result['error'],
    ];

    return ['apisVars' => ['is_banned' => $result['is_banned'], 'jails' => $jails, 'ip' => $ip, 'rampart_enabled' => $result['rampart_enabled'], 'debug' => $debug]];
});

add_hook('ClientAreaPrimarySidebar', 1, function ($sidebar) {
    $service = Menu::context("service");
    if ($service instanceof WHMCS\Service\Service && $service->product->module !== "apnscp") {
        return null;
    }

    if (! $sidebar->getChild("Service Details Actions")) {
        return null;
    }

    $sidebar->getChild("Service Details Actions")
        ->addChild("Login to ApisCP", [
            "uri"        => "clientarea.php?action=productdetails&id=" . $service->id . "&dosinglesignon=1",
            "label"      => 'Login to ApisCP',
            "attributes" => ["target" => "_blank"],
            "disabled"   => $service->status !== "Active",
            "order"      => 1
        ]);
});

/**
 * Fetch the public outbound IP address of this WHMCS installation.
 *
 * Queries a public IP-echo endpoint so the result reflects the real public IP
 * even when WHMCS is behind NAT or a reverse proxy. Falls back to a second
 * endpoint if the first is unreachable, and returns null if both fail.
 *
 * @return string|null The public IPv4 address, or null on failure.
 */
function apnscp_getPublicIp(): ?string
{
    $endpoints = [
        'https://api.ipify.org?format=text',
        'https://checkip.amazonaws.com',
    ];

    foreach ($endpoints as $endpoint) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $ip = $response ? trim($response) : null;
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return null;
}

/**
 * Check whether a given IP is banned on the ApisCP server via Rampart (fail2ban).
 *
 * Always returns a consistent array:
 *   - is_banned:       bool        — whether the IP is currently banned
 *   - jails:           array       — raw jail names from the API (empty if not banned)
 *   - rampart_enabled: bool        — whether Rampart is active for the site; defaults
 *                                    to true on lookup failure since active bans imply it is running
 *   - error:           string|null — exception message if the API call failed
 *
 * @param array $params Server connection params plus 'domain' and 'ip'.
 *
 * @return array{is_banned: bool, jails: array, rampart_enabled: bool, error: string|null}
 */
function apnscp_checkIP(array $params): array
{
    ['endpoint' => $apnscp_apiendpoint, 'apikey' => $apnscp_apikey] = Helper::buildEndpoint($params);

    $site_domain = $params['domain'];
    $clientIp = $params['ip'];

    try {
        $client = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint);
        $isBanned = $client->rampart_is_banned($clientIp);

        if (! $isBanned) {
            return ['is_banned' => false, 'jails' => [], 'rampart_enabled' => false, 'error' => null];
        }

        $jails = (array)$client->rampart_banned_services($clientIp);

        $client->rampart_unban($clientIp);

        // admin_collect can fail on some server versions; default to true since
        // active bans imply Rampart is running.
        $rampart_enabled = true;
        try {
            $rampartCheck = $client->admin_collect(['rampart.enabled'], null, [$site_domain]);
            $rampart_enabled = $rampartCheck[$site_domain]['rampart']['enabled'] === 1;
        } catch (\Throwable $e) {
            // admin_collect can fail on older server versions; $rampart_enabled stays true
        }

        return ['is_banned' => true, 'jails' => $jails, 'rampart_enabled' => $rampart_enabled, 'error' => null];
    } catch (\Throwable $e) {
        return ['is_banned' => false, 'jails' => [], 'rampart_enabled' => false, 'error' => $e->getMessage()];
    }
}


/**
 * Deferred Account Termination — Cancellation Hold feature.
 *
 * Runs nightly via the WHMCS DailyCronJob. Instructs each active ApisCP server
 * to permanently delete any site that was previously deactivated (suspended) with
 * the reason "Deferred Account Cancellation" and has been in that state for at
 * least 30 days.
 *
 * HOW TO ENABLE:
 *   1. In apnscp_TerminateAccount (apnscp.php), comment out the immediate deletion
 *      call and uncomment the deferred deactivation call instead (see README).
 *   2. Uncomment the add_hook block below to activate this nightly purge cron.
 *
 * WARNING: Remove 'dry-run' from $opts before running in production.
 */

// add_hook('DailyCronJob', 1, function () {
//
//     $servers = WHMCS\Product\Server::where('type', 'apnscp')->where('active', 1)->where('disabled', 0)->get();
//
//     foreach ($servers as $server) {
//         $client = null;
//         try {
//             ['endpoint' => $endpoint, 'apikey' => $apikey] = Helper::buildEndpoint($server);
//
//             $client = ApisConnector::create_client($apikey, $endpoint);
//
//             $opts = [
//                 'since' => '30 days ago',                   // Can be any value
//                 'match' => 'Deferred Account Cancellation', // Must match what's in the Terminate function
//                 'dry-run',                                  // Remove for production!
//                 'force' => true                             // May not be required, but ensure an account is fully deleted...
//             ];
//
//             $client->admin_delete_site(null, $opts);
//
//             logModuleCall('apnscp', 'Deferred Cancellation - ' . $server->hostname, Helper::formatXml($client->__getLastRequest()), Helper::formatXml($client->__getLastResponse()));
//         } catch (\Throwable $e) {
//             logModuleCall('apnscp', 'Deferred Cancellation - ' . $server->hostname, '', $e->getMessage());
//         }
//     }
//
// });