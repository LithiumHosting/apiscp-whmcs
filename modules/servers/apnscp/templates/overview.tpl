<style>
    .apiscp-package-details {
        margin-bottom: 12px;
    }

    .apiscp-usage-stats {
        padding: 17px 15px;
    }

    .apiscp-usage-stats .limit-near {
        margin: 15px 0 5px;
        font-size: 0.8em;
    }

    .apiscp-feature-row {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .apiscp-feature-row i {
        display: block;
        margin: 0 auto 5px auto;
    }
</style>
<h2>Overview</h2>
<div class="row">
    <div class="col-md-6">

        <div class="panel panel-default card mb-3" id="PackagePanel">
            <div class="panel-heading card-header">
                <h3 class="panel-title card-title m-0">{$LANG.cPanel.packageDomain}</h3>
            </div>
            <div class="panel-body card-body text-center">

                <div class="apiscp-package-details">
                    <em>{$groupname}</em>
                    <h4 style="margin:0;">{$product}</h4>
                    <a href="http://{$domain}" target="_blank">www.{$domain}</a>
                </div>

                <p>
                    <a href="http://{$domain}" class="btn btn-default btn-sm" target="_blank">{$LANG.visitwebsite}</a>
                    {if $domainId}
                        <a href="clientarea.php?action=domaindetails&id={$domainId}" class="btn btn-success btn-sm" target="_blank">{$LANG.managedomain}</a>
                    {/if}
                </p>

            </div>
        </div>

    </div>

    <div class="col-md-6">

        <div class="panel panel-default card mb-3" id="panelUsagePanel">
            <div class="panel-heading card-header">
                <h3 class="panel-title card-title m-0">{$LANG.cPanel.usageStats}</h3>
            </div>
            <div class="panel-body card-body text-center apiscp-usage-stats">

                <div class="row">
                    <div class="col-sm-6 col-xs-6 col-6" id="diskUsage">
                        <strong>{$LANG.cPanel.diskUsage}</strong>
                        <br /><br />
                        <input type="text" value="{$diskpercent|substr:0:-1}" class="usage-dial" data-fgColor="#444" data-angleOffset="-125" data-angleArc="250" data-min="0" data-max="{if substr($diskpercent, 0, -1) > 100}{$diskpercent|substr:0:-1}{else}100{/if}" data-readOnly="true" data-width="100" data-height="80" />
                        <br /><br />
                        {$diskusage} M / {$disklimit} M
                    </div>
                    <div class="col-sm-6 col-xs-6 col-6" id="bandwidthUsage">
                        <strong>{$LANG.cPanel.bandwidthUsage}</strong>
                        <br /><br />
                        <input type="text" value="{$bwpercent|substr:0:-1}" class="usage-dial" data-fgColor="#d9534f" data-angleOffset="-125" data-angleArc="250" data-min="0" data-max="{if substr($bwpercent, 0, -1) > 100}{$bwpercent|substr:0:-1}{else}100{/if}" data-readOnly="true" data-width="100" data-height="80" />
                        <br /><br />
                        {$bwusage} M / {$bwlimit} M
                    </div>
                </div>

                {if $bwpercent|substr:0:-1 > 75}
                    <div class="text-danger limit-near">
                        {if $bwpercent|substr:0:-1 > 100}
                            {$LANG.cPanel.usageStatsBwOverLimit}
                        {else}
                            {$LANG.cPanel.usageStatsBwLimitNear}
                        {/if}
                        {if $packagesupgrade}
                            <a href="upgrade.php?type=package&id={$serviceid}" class="btn btn-xs btn-danger">
                                <i class="fas fa-arrow-circle-up"></i>
                                {$LANG.cPanel.usageUpgradeNow}
                            </a>
                        {/if}
                    </div>
                {elseif $diskpercent|substr:0:-1 > 75}
                    <div class="text-danger limit-near">
                        {if $diskpercent|substr:0:-1 > 100}
                            {$LANG.cPanel.usageStatsDiskOverLimit}
                        {else}
                            {$LANG.cPanel.usageStatsDiskLimitNear}
                        {/if}
                        {if $packagesupgrade}
                            <a href="upgrade.php?type=package&id={$serviceid}" class="btn btn-xs btn-danger">
                                <i class="fas fa-arrow-circle-up"></i>
                                {$LANG.cPanel.usageUpgradeNow}
                            </a>
                        {/if}
                    </div>
                {else}
                    <div class="text-info limit-near">
                        {$LANG.cPanel.usageLastUpdated} {$lastupdate}
                    </div>
                {/if}

                <script src="{$BASE_PATH_JS}/jquery.knob.js"></script>
                <script type="text/javascript">
                    jQuery(function() {
                        jQuery(".usage-dial").knob({
                            'format': function (value) {
                                return value + '%';
                            }
                        });
                    });
                </script>

            </div>
        </div>
    </div>
</div>

{if $availableAddonProducts}
    <div class="panel panel-default card mb-3" id="panelExtrasPurchasePanel">
        <div class="panel-heading card-header">
            <h3 class="panel-title card-title m-0">{$LANG.cPanel.addonsExtras}</h3>
        </div>
        <div class="panel-body card-body text-center mx-auto">

            <form method="post" action="cart.php?a=add" class="form-inline">
                <input type="hidden" name="serviceid" value="{$serviceid}" />
                <select name="aid" class="form-control custom-select w-100 input-sm form-control-sm mr-2">
                    {foreach $availableAddonProducts as $addonId => $addonName}
                        <option value="{$addonId}">{$addonName}</option>
                    {/foreach}
                </select>
                <button type="submit" class="btn btn-default btn-sm mt-1">
                    <i class="fas fa-shopping-cart"></i>
                    {$LANG.cPanel.purchaseActivate}
                </button>
            </form>

        </div>
    </div>
{/if}

<div class="panel panel-default card mb-3" id="productdetails">
    <div class="panel-heading card-header">
        <h3 class="panel-title card-title m-0">{$LANG.clientareaproductdetails}</h3>
    </div>
    <div class="panel-body card-body text-left">

        <div class="row">
            <div class="col-sm-5">
                {$LANG.clientareahostingregdate}
            </div>
            <div class="col-sm-7">
                {$regdate}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                {$LANG.orderproduct}
            </div>
            <div class="col-sm-7">
                {$groupname} - {$product}
            </div>
        </div>


        {if $domain}
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.orderdomain}
                </div>
                <div class="col-sm-7">
                    {$domain}
                    <a href="http://{$domain}" target="_blank" class="btn btn-default btn-xs">{$LANG.visitwebsite}</a>
                </div>
            </div>
        {/if}
        {if $username}
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.serverusername}
                </div>
                <div class="col-sm-7">
                    {$username}
                </div>
            </div>
        {/if}
        {if $serverdata}
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.servername}
                </div>
                <div class="col-sm-7">
                    {$serverdata.hostname}
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.domainregisternsip}
                </div>
                <div class="col-sm-7">
                    {$serverdata.ipaddress}
                </div>
            </div>
            {if $serverdata.nameserver1 || $serverdata.nameserver2 || $serverdata.nameserver3 || $serverdata.nameserver4 || $serverdata.nameserver5}
                <div class="row">
                    <div class="col-sm-5">
                        {$LANG.domainnameservers}
                    </div>
                    <div class="col-sm-7">
                        {if $serverdata.nameserver1}{$serverdata.nameserver1}{if $serverdata.nameserver1ip} ({$serverdata.nameserver1ip}){/if}<br/>{/if}
                        {if $serverdata.nameserver2}{$serverdata.nameserver2}{if $serverdata.nameserver2ip} ({$serverdata.nameserver2ip}){/if}<br/>{/if}
                        {if $serverdata.nameserver3}{$serverdata.nameserver3}{if $serverdata.nameserver3ip} ({$serverdata.nameserver3ip}){/if}<br/>{/if}
                        {if $serverdata.nameserver4}{$serverdata.nameserver4}{if $serverdata.nameserver4ip} ({$serverdata.nameserver4ip}){/if}<br/>{/if}
                        {if $serverdata.nameserver5}{$serverdata.nameserver5}{if $serverdata.nameserver5ip} ({$serverdata.nameserver5ip}){/if}<br/>{/if}
                    </div>
                </div>
            {/if}
        {/if}

        {if $dedicatedip}
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.domainregisternsip}
                </div>
                <div class="col-sm-7">
                    {$dedicatedip}
                </div>
            </div>
        {/if}

        {foreach from=$configurableoptions item=configoption}
            <div class="row">
                <div class="col-sm-5">
                    {$configoption.optionname}
                </div>
                <div class="col-sm-7">
                    {if $configoption.optiontype eq 3}
                        {if $configoption.selectedqty}
                            {$LANG.yes}
                        {else}
                            {$LANG.no}
                        {/if}
                    {elseif $configoption.optiontype eq 4}
                        {$configoption.selectedqty} x {$configoption.selectedoption}
                    {else}
                        {$configoption.selectedoption}
                    {/if}
                </div>
            </div>
        {/foreach}

        {foreach from=$productcustomfields item=customfield}
            <div class="row">
                <div class="col-sm-5">
                    {$customfield.name}
                </div>
                <div class="col-sm-7">
                    {$customfield.value}
                </div>
            </div>
        {/foreach}

        {if $lastupdate}
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.clientareadiskusage}
                </div>
                <div class="col-sm-7">
                    {$diskusage}MB / {$disklimit}MB ({$diskpercent})
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.clientareabwusage}
                </div>
                <div class="col-sm-7">
                    {$bwusage}MB / {$bwlimit}MB ({$bwpercent})
                </div>
            </div>
        {/if}

        <div class="row">
            <div class="col-sm-5">
                {$LANG.orderpaymentmethod}
            </div>
            <div class="col-sm-7">
                {$paymentmethod}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                {$LANG.firstpaymentamount}
            </div>
            <div class="col-sm-7">
                {$firstpaymentamount}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                {$LANG.recurringamount}
            </div>
            <div class="col-sm-7">
                {$recurringamount}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                {$LANG.clientareahostingnextduedate}
            </div>
            <div class="col-sm-7">
                {$nextduedate}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                {$LANG.orderbillingcycle}
            </div>
            <div class="col-sm-7">
                {$billingcycle}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                {$LANG.clientareastatus}
            </div>
            <div class="col-sm-7">
                {$status}
            </div>
        </div>

        {if $suspendreason}
            <div class="row">
                <div class="col-sm-5">
                    {$LANG.suspendreason}
                </div>
                <div class="col-sm-7">
                    {$suspendreason}
                </div>
            </div>
        {/if}
    </div>
</div>
{if $systemStatus == 'Active'}
    <div class="panel panel-default card mb-3" id="panelQuickShortcutsPanel">
        <div class="panel-heading card-header">
            <h3 class="panel-title card-title m-0">Quick Shortcuts</h3>
        </div>
        <div class="panel-body card-body text-center mx-auto">

            <div class="row apiscp-feature-row">
                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=usermanage" target="_blank" class="d-block mb-3">
                        <i class="fas fa-users fa-3x fa-fw"></i>
                        <p>User Manager<br><small>Manage account users</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=mailboxroutes" target="_blank" class="d-block mb-3">
                        <i class="fas fa-inbox-in fa-3x fa-fw"></i>
                        <p>Email Accounts<br><small>Manage Email Accounts</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=vacation" target="_blank" class="d-block mb-3">
                        <i class="fas fa-plane fa-3x fa-fw"></i>
                        <p>Vacation Responder<br><small>Set your out-of-office reply</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=filemanager" target="_blank" class="d-block mb-3">
                        <i class="fas fa-folder fa-3x fa-fw"></i>
                        <p>File Manager<br><small>Upload / Download Files</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=subdomains" target="_blank" class="d-block mb-3">
                        <i class="fas fa-columns fa-3x fa-fw"></i>
                        <p>Subdomains<br><small>Manage Subdomains</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=domainmanager" target="_blank" class="d-block mb-3">
                        <i class="fas fa-cloud fa-3x fa-fw"></i>
                        <p>Addon Domains<br><small>Manage Addon Domains</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=crontab" target="_blank" class="d-block mb-3">
                        <i class="fas fa-clock fa-3x fa-fw"></i>
                        <p>Scheduled Tasks<br><small>Manage your crontab</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=bandwidthbd" target="_blank" class="d-block mb-3">
                        <i class="fas fa-chart-line fa-3x fa-fw"></i>
                        <p>Bandwidth Usage<br><small>View bandwidth usage</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=changemysql" target="_blank" class="d-block mb-3">
                        <i class="fas fa-database fa-3x fa-fw"></i>
                        <p>MySQL Manager<br><small>Add / Remove Databases</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=phpmyadmin" target="_blank" class="d-block mb-3">
                        <i class="fas fa-toolbox fa-3x fa-fw"></i>
                        <p>phpMyAdmin<br><small>Manage databases</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=terminal" target="_blank" class="d-block mb-3">
                        <i class="fas fa-terminal fa-3x fa-fw"></i>
                        <p>SSH / Terminal<br><small>Manage your site like a boss</small></p>
                    </a>
                </div>

                <div class="col-md-3 col-sm-4 col-xs-6 col-6">
                    <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=webapps" target="_blank" class="d-block mb-3">
                        <i class="fab fa-wordpress fa-3x fa-fw"></i>
                        <p>Web Apps<br><small>Install and Manage Apps</small></p>
                    </a>
                </div>
            </div>
        </div>
    </div>
{/if}
<hr>

<div class="row">
    {if $systemStatus == 'Active'}
        <div class="col-sm-4">
            <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1" class="btn btn-primary btn-block">
                Login to ApisCP
            </a>
        </div>
    {/if}

    {if $packagesupgrade}
        <div class="col-sm-4">
            <a href="upgrade.php?type=package&amp;id={$id}" class="btn btn-success btn-block">
                {$LANG.upgrade}
            </a>
        </div>
    {/if}

    <div class="col-sm-4">
        <a href="clientarea.php?action=cancel&amp;id={$id}" class="btn btn-danger btn-block{if $pendingcancellation}disabled{/if}">
            {if $pendingcancellation}
                {$LANG.cancellationrequested}
            {else}
                {$LANG.cancel}
            {/if}
        </a>
    </div>
</div>