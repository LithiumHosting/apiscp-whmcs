# ApisCP Provisioning Module for WHMCS
By Troy Siedsma (Lithium Hosting)

## Requirements
- WHMCS v8+
- ApisCP (any actively supported version)

## Configuring
- Create Plans in ApisCP
- Install server module in WHMCS
- Create a server or multiple ApisCP servers under products -> servers
  - Define the hostname, password (API key), check the box for secure and make sure the port is 2083
- Create a new Product in WHMCS, use apnscp as the module
- Under module config, select your plan and add / update values as needed (blank is ok to use plan defaults)
- Create a new hosting account
- Profit!

## Template Changes
Added in 1.0.8, updated in 1.0.14.

In your WHMCS theme, edit the `clientareaproductdetails` template and add the following snippet after the last `{if}` block, before the first `<div>`. This displays an IP ban notice when the module detects the client's public IP is listed in ApisCP's Rampart (fail2ban) firewall, and automatically unbans it.

```smarty
{if $apisVars['is_banned']}
    <div class="alert alert-danger">
        <p><strong>IP Ban Notice</strong></p>
        We found your IP Address in the Blacklist on ApisCP. <br>Your IP was detected in the following Jails:
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
- Creating accounts
- Suspending accounts
- Unsuspending accounts
- Terminating accounts
- Changing password
- Changing plans
- SSO from WHMCS for client and admin
- SSO with custom links to different apps (mail, MySQL, etc.)
- Automatic public IP detection and IP ban check with auto-unban on the service details page
- Login to ApisCP button in the service sidebar
- Cancellation Hold (deferred termination)
- Statistics update
- SiteID population on stats update

## Login to ApisCP Sidebar Button
A **Login to ApisCP** link is automatically added to the service details sidebar via `hooks.php` for any active apnscp service. No template changes are required. The link opens an SSO session in a new tab and is disabled when the service is not active.

## Cancellation Hold
This feature defers termination of cancelled accounts for 30 days, suspending the site on ApisCP instead of deleting it. ApisCP's built-in deferred deletion cron then purges the account automatically after the hold period.

**In `apnscp_TerminateAccount` in `apnscp.php`**, swap the active block:

Comment out the immediate deletion:
```php
$client->admin_delete_site($site_domain, ['force' => true]);
```

Uncomment the deferred suspension instead:
```php
$client->admin_deactivate_site($site_domain, ['reason' => 'Deferred Account Cancellation']);
```

**In `hooks.php`**, enable the `DailyCronJob` hook block. This runs nightly and instructs ApisCP to purge any site that was deactivated with the `Deferred Account Cancellation` reason more than 30 days ago.

## Summary
The ApisCP provisioning module for WHMCS allows you to integrate your billing system with your server management panel so new user accounts will be automatically provisioned, suspended and terminated as needed. Users can change their password as well as use the Single Sign-On (SSO) feature to seamlessly transition from WHMCS to ApisCP.

## License
This product is licensed under the GPL v3 (see LICENSE file). Basically, you can't call it your own or sell it.
This is meant to be free for the benefit of the community. Help us by improving with Pull Requests!

## Contributing
Submit a PR and have fun!
I am a developer by hobby, not profession so don't judge me and I won't judge you :P

## Need Help?
Join us in the [ApisCP Discord](https://discord.gg/5bQr3Dm) in the #whmcs channel!

## Is it any good?

Yes.

_When people first hear about a new product, they frequently ask if it is any good. A Hacker News user
[remarked](https://news.ycombinator.com/item?id=3067434):_

> Note to self: Starting immediately, all raganwald projects will have a “Is it any good?” section in the readme, and
> the answer shall be “yes.".