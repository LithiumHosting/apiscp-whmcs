# apnscp Provisioning Module for WHMCS
By Troy Siedsma (Lithium Hosting)

## Requirements:
WHMCS v7.1+
apnscp must be on version 3.1  
https://forums.apnscp.com/t/apnscp-3-1-development-thread/68/5?u=msaladna

## Configuring
- Create Plans in apnscp
- Install server module in WHMCS
- Create a server or multiple apnscp servers under products -> servers
  - Define the hostname, password(api key), check the box for secure and make sure the port is 2083 
- Create a new Product in WHMCS, use apnscp as the module
- Under module config, select your plan and add / update values as needed (blank is ok to use plan defaults)
- Create a new hosting account
- Profit!

## Template Changes
- New in 1.0.8 

Edit clientareaproductdetails and add the following after the last {if} before the first <div>
```smarty
{if $apiscp_banned}
    {include file="$template/includes/alert.tpl" type="error" title="IP Ban Notice" msg="We found your IP Address in the Blacklist on in the Panel.  We've taken the liberty of removing the IP ban and added your IP to your accounts whitelist.<br>Your IP was detected in the following Jails:<ul>{foreach from=$apiscp_jails item=jail}<li>{$jail}</li>{/foreach}"}
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
- Automatically unban and whitelist a user's IP

## Summary
The apnscp provisioning module for WHMCS allows you to integrate your billing system with your server management panel so new user accounts will be automatically provisioned, suspended and terminated as needed.  Users can change their password as well as use the Single Sign-On (SSO) feature to seamlessly transition from WHMCS to apnscp.

## License
This product is licensed under the GPL v3 (see LICENSE file).  Basically, you can't call it your own or sell it.
This is meant to be free for the benefit of the community.  Help us by improving with Pull Requests!

## Contributing
Submit a PR and have fun!