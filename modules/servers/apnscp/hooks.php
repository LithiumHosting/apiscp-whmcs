<?php
require_once('lib/ApisConnector.php');
require_once('lib/Helper.php');

add_hook('ClientAreaPageProductDetails', 1, function ($vars) {

    $server = $vars['serverdata'];

    // WHMCS MAGIC!!!1
    $ca           = new WHMCS\ClientArea();
    $legacyClient = new WHMCS\Client($ca->getClient());
    $service      = new WHMCS\Service($vars['serviceid'], $legacyClient->getID());
    $product      = WHMCS\Product\Product::where('id', $service->getData('packageid'))->first();

    if ($product->module !== "apnscp")
    {
        return null;
    }

    if (version_compare(WHMCS\Config\Setting::getValue("Version"), '8', '<'))
    {
        $ip = WHMCS\Utility\Environment\CurrentUser::getIP();
    }
    else
    {
        $user = (new WHMCS\Authentication\CurrentUser())->user();
        $ip = $user->currentIp();
    }

    //    $ip     = '2.2.2.2'; // Only for debug...

    $knownJails = [
        'f2b-sshd'         => 'SSH (f2b-sshd)',
        'f2b-postfix'      => 'SMTP (f2b-postfix)',
        'f2b-postfix-sasl' => 'SMTP (f2b-postfix-sasl)',
        'f2b-dovecot'      => 'IMAP/POP3 (f2b-dovecot)',
        'f2b-vsftpd'       => 'FTP (f2b-vsftpd)',
        'f2b-evasive'      => 'Apache Mod Evasive (f2b-evasive)',
        'f2b-spambots'     => 'Irregular Mail Patterns (f2b-spambots)',
        'f2b-recidive'     => 'Repeatedly getting banned (f2b-recidive)',
        'f2b-pgsql'        => 'pgSQL (f2b-pgsql)',
        'f2b-mysql'        => 'MySQL (f2b-mysql)',
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

    if ($result['jails'])
    {
        if (is_array($result['jails']))
        {
            foreach ($result['jails'] as $jail)
            {
                $jails[] = $knownJails[ $jail ];
            }
        }
        else
        {
            $jails[] = $result['jails'];
        }

        return ['apisVars' => ['is_banned' => true, 'jails' => $jails, 'ip' => $ip, 'rampart_enabled' => $result['rampart_enabled']]];
    }

    return ['apisVars' => ['is_banned' => false, 'jails' => [], 'ip' => $ip, 'rampart_enabled' => false]];
});

add_hook('ClientAreaPrimarySidebar', 1, function ($sidebar) {
    if (! $sidebar->getChild("Service Details Actions"))
    {
        return null;
    }
    $service = Menu::context("service");
    if ($service instanceof WHMCS\Service\Service && $service->product->module !== "apnscp")
    {
        return null;
    }

    $sidebar->getChild("Service Details Actions")->addChild("Login to ApisCP", array("uri" => "clientarea.php?action=productdetails&id=" . $service->id . "&dosinglesignon=1", "label" => 'Login to ApisCP', "attributes" => array("target" => "_blank"), "disabled" => $service->status != "Active", "order" => 1));
});

function apnscp_checkIP(array $params)
{
    $apnscp_apiendpoint = $params['serverhttpprefix'] . '://' . $params['serverhostname'] . ':' . $params['serverport'];
    $apnscp_apikey      = $params['serverpassword'];
    $site_domain        = $params['domain'];
    $site_admin         = $params['username'];
    $clientIp           = $params['ip'];

    try
    {
        $adminId = \session_id();
        $client  = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        if ($client->rampart_is_banned($clientIp))
        {
            $jails = $client->rampart_banned_services($clientIp);
            $client->rampart_unban($clientIp);

            $rampartCheck    = $client->admin_collect(['rampart.enabled'], null, [$site_domain]);
            $rampart_enabled = $rampartCheck[ $site_domain ]['rampart']['enabled'] === 1;

            // Disabled whitelisting to avoid conflicts, will instruct customer to whitelist themselves!
//            $session_id = $client->admin_hijack($site_domain, $site_admin, 'SOAP');
//            $newClient  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $session_id, $clientIp);
//
//            $newClient->rampart_whitelist($clientIp);

            return ['jails' => $jails, 'rampart_enabled' => $rampart_enabled];
        }

        return false;
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

    return false;
}

add_hook('DailyCronJob', 1, function () {

    $servers = WHMCS\Product\Server::where('type', 'apnscp')->where('active', 1)->where('disabled', 0)->get();
    foreach ($servers as $server)
    {
        $apnscp_apiendpoint = $server->serverhttpprefix . '://' . $server->serverhostname . ':' . $server->serverport ?: 2083;
        $apnscp_apikey      = decrypt($server->serverpassword);

        $adminId = \session_id();
        $client  = ApisConnector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        $opts['since'] = "30 days ago";
        $opts['match'] = "Deferred Account Cancellation";
        $opts['dry-run'];
        $client->admin_delete_site(null, $opts);
        logModuleCall('apnscp', 'Deferred Cancellation - ' . $server->serverhostname, str_ireplace('><', ">\n<", $client->__getLastRequest()), str_ireplace('><', ">\n<", $client->__getLastResponse()));
    }

});