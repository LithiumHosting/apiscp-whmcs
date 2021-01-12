# ApisCP Provisioning Module for WHMCS
By Troy Siedsma (Lithium Hosting)

## Requirements:
WHMCS v7.1+
ApisCP must be on version 3.2.5

## Configuring
- Create Plans in ApisCP
- Install server module in WHMCS
- Create a server or multiple ApisCP servers under products -> servers
  - Define the hostname, password(api key), check the box for secure and make sure the port is 2083 
- Create a new Product in WHMCS, use apnscp as the module
- Under module config, select your plan and add / update values as needed (blank is ok to use plan defaults)
- Create a new hosting account
- Profit!

## Template Changes
- New in 1.0.8 

Edit clientareaproductdetails and add the following after the last {if} before the first <div>
```smarty
{if $apisVars['is_banned']}
    <div class="alert alert-danger">
        <h3>IP Ban Notice</h3>
        We found your IP Address in the Blacklist in the Panel. <br>Your IP was detected in the following Jails:
        <ul>
            {foreach from=$apisVars['jails'] item=jail}
                <li>{$jail}</li>
            {/foreach}
        </ul>
        We've removed the ban on your IP but any additional suspicious activity may result in banning your IP Again.
        {if $apisVars['rampart_enabled']}
            <br>
            You should also be sure to add your IP {$apisVars['ip']} to the Whitelist
            <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=whitelist" target="_blank" title="Panel Whitelist" class="alert-link">here</a>.
        {/if}
    </div>
{/if}
```


## Supported Features
- Creating
- Suspending
- Unsuspending
- Terminating
- Changing Password
- Changing Plans
- SSO from WHMCS for Client and Admin
- SSO with custom links to different apps
- Automatically unban a user's IP
- Cancellation Hold
- Statistics update
- SiteID Population on stats update

## Cancellation Hold
This feature allows you to defer termination of cancelled and terminated accounts for 30 days.  
To enable, uncomment lines 242 and 243 in apnscp.php and comment out lines 245 and 246  
  
Comment:
```php
        $opts['force'] = 'true';
        $client->admin_delete_site($site_domain, $opts);
```
By commenting out that section, you are preventing account deletion.

Uncomment:
```php
        $opts['reason'] = 'Customer Requested Cancellation';
        $client->admin_deactivate_site($site_domain, $opts);
```
By uncommenting that section, you are forcing account suspension.  This means the site will effectively be suspended until the automated process purges the account from the server.  
To enable that feature, uncomment the last action hook block in hooks.php

## Summary
The ApisCP provisioning module for WHMCS allows you to integrate your billing system with your server management panel so new user accounts will be automatically provisioned, suspended and terminated as needed.  Users can change their password as well as use the Single Sign-On (SSO) feature to seamlessly transition from WHMCS to ApisCP.

## License
This product is licensed under the GPL v3 (see LICENSE file).  Basically, you can't call it your own or sell it.
This is meant to be free for the benefit of the community.  Help us by improving with Pull Requests!

## Contributing
Submit a PR and have fun!  
I am a developer by hobby, not profession so don't judge me and I won't judge you :P

## Need Help?
Join us in the [ApisCP Discord](https://discord.gg/5bQr3Dm) in the #whmcs channel!