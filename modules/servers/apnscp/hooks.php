<?php
require_once('lib/Connector.php');
require_once('lib/Helper.php');

add_hook('ClientAreaPageProductDetails', 1, function ($vars) {

    $server = $vars['serverdata'];
    
    // WHMCS version check for $ip
    if (version_compare(WHMCS\Config\Setting::getValue("Version"), '8', '<'))
    {
        $ip     = WHMCS\Utility\Environment\CurrentUser::getIP();
    }
    else {
	$user = (new WHMCS\Authentication\CurrentUser())->user();
	$ip = $user->currentIp();
    }

    $knownJails = [
        'f2b-sshd'         => 'SSH (f2b-sshd)',
        'f2b-postfix'      => 'SMTP (f2b-postfix)',
        'f2b-postfix-sasl' => 'SMTP (f2b-postfix-sasl)',
        'f2b-dovecot'      => 'IMAP/POP3 (f2b-dovecot)',
        'f2b-vsftpd'       => 'FTP (f2b-vsftpd)',
        'f2b-evasive'      => 'Apache Mod Evasive (f2b-evasive)',
        'f2b-spambots'     => 'Irregular Mail Patterns (f2b-spambots)',
        'f2b-recidive'     => 'Repeatedly getting banned (f2b-recidive)',
    ];

    // WHMCS MAGIC!!!
    $ca           = new WHMCS\ClientArea();
    $legacyClient = new WHMCS\Client($ca->getClient());
    $service      = new WHMCS\Service($vars['serviceid'], $legacyClient->getID());

    if ($service->product->module != "apnscp")
    {
        return null;
    }

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

    if ($result)
    {
        $jails = [];
        if (is_array($result))
        {
            foreach ($result as $jail)
            {
                $jails[] = $knownJails[ $jail ];
            }
        }
        else
        {
            $jails[] = $result;
        }

        return ['apiscp_banned' => true, 'apiscp_jails' => $jails];
    }

    return ['apiscp_banned' => false, 'apiscp_jails' => []];
});

add_hook('ClientAreaPrimarySidebar', 1, function ($sidebar) {
    if (! $sidebar->getChild("Service Details Actions"))
    {
        return null;
    }
    $service = Menu::context("service");
    if ($service instanceof WHMCS\Service\Service && $service->product->module != "apnscp")
    {
        return null;
    }

    $sidebar->getChild("Service Details Actions")->addChild("Login to Panel", array("uri" => "clientarea.php?action=productdetails&id=" . $service->id . "&dosinglesignon=1", "label" => 'Login to Panel', "attributes" => array("target" => "_blank"), "disabled" => $service->status != "Active", "order" => 1));
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
        $client  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $adminId);

        if ($client->rampart_is_banned($clientIp))
        {
            $jails = $client->rampart_banned_services($clientIp);
            $client->rampart_unban($clientIp);

            $session_id = $client->admin_hijack($site_domain, $site_admin, 'SOAP');
            $newClient  = Connector::create_client($apnscp_apikey, $apnscp_apiendpoint, $session_id, $clientIp);

            $newClient->rampart_whitelist($clientIp);

            return $jails;
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
