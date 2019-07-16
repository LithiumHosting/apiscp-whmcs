<h2>Overview</h2>
<div class="row">
    <div class="col-md-6">

        <div class="panel panel-default" id="PackagePanel">
            <div class="panel-heading">
                <h3 class="panel-title">{$LANG.cPanel.packageDomain}</h3>
            </div>
            <div class="panel-body text-center">

                <div class="package-details">
                    <em>{$groupname}</em>
                    <h4 style="margin:0;">{$product}</h4>
                    <a href="http://{$domain}" target="_blank">www.{$domain}</a>
                    {*<a href="#" id="domainname" data-name="newdomain" data-type="text" data-pk="{$serviceid}" data-placement="right" data-url="/billing/ajax.php" data-title="Change your hosted domain name:"><i class="fa fa-pencil"></i> {$domain}</a>*}
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-12 text-left">
                        <dl class="dl-horizontal">
                            <dt>Server Hostname</dt>
                            <dd>{$serverdata.hostname}</dd>
                            {if $dedicatedip}
                                <dt>{$LANG.clientareadedicatedip} {$LANG.domainregisternsip}</dt>
                                <dd>{$dedicatedip}</dd>
                            {else}
                                <dt>{$LANG.clientareasharedip} {$LANG.domainregisternsip}</dt>
                                <dd> {$serverdata.ipaddress}</dd>
                            {/if}
                        </dl>
                    </div>
                </div>

                <p>
                    <a href="http://{$domain}" class="btn btn-default btn-sm" target="_blank">{$LANG.visitwebsite}</a>
                    {if $domainId}
                        <a href="clientarea.php?action=domaindetails&id={$domainId}" class="btn btn-success btn-sm" target="_blank">{$LANG.managedomain}</a>
                    {/if}
                    <a class="btn btn-info btn-sm" href="#" data-url="whois.php?domain={$domain}" data-toggle="modal" data-target="#whoisModal" data-subject="whois details for {$domain}" id="modalTrigger">{$LANG.whoisinfo}</a>
                </p>

            </div>
        </div>

        {if $availableAddonProducts}
            <div class="panel panel-default" id="panelExtrasPurchasePanel">
                <div class="panel-heading">
                    <h3 class="panel-title">{$LANG.cPanel.addonsExtras}</h3>
                </div>
                <div class="panel-body text-center">

                    <form method="post" action="cart.php?a=add" class="form-inline">
                        <input type="hidden" name="serviceid" value="{$serviceid}" />
                        <select name="aid" class="form-control input-sm">
                            {foreach $availableAddonProducts as $addonId => $addonName}
                                <option value="{$addonId}">{$addonName}</option>
                            {/foreach}
                        </select>
                        <button type="submit" class="btn btn-default btn-sm">
                            <i class="fas fa-shopping-cart"></i>
                            {$LANG.cPanel.purchaseActivate}
                        </button>
                    </form>

                </div>
            </div>
        {/if}

    </div>
    <div class="col-md-6">

        <div class="panel panel-default" id="panelUsagePanel">
            <div class="panel-heading">
                <h3 class="panel-title">{$LANG.cPanel.usageStats}</h3>
            </div>
            <div class="panel-body text-center cpanel-usage-stats">

                <div class="row">
                    <div class="col-sm-5 col-sm-offset-1 col-xs-6" id="diskUsage">
                        <strong>{$LANG.cPanel.diskUsage}</strong>
                        <br /><br />
                        <input type="text" value="{$diskpercent|substr:0:-1}" class="usage-dial" data-fgColor="#444" data-angleOffset="-125" data-angleArc="250" data-min="0" data-max="{if substr($diskpercent, 0, -1) > 100}{$diskpercent|substr:0:-1}{else}100{/if}" data-readOnly="true" data-width="100" data-height="80" />
                        <br /><br />
                        {$diskusage} M / {$disklimit} M
                    </div>
                    <div class="col-sm-5 col-xs-6" id="bandwidthUsage">
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

<div class="panel panel-default" id="productdetails">
    <div class="panel-heading">
        <h3 class="panel-title">{$LANG.clientareaproductdetails}</h3>
    </div>
    <div class="panel-body text-left">

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
                        {if $serverdata.nameserver1}{$serverdata.nameserver1} ({$serverdata.nameserver1ip})<br/>{/if}
                        {if $serverdata.nameserver2}{$serverdata.nameserver2} ({$serverdata.nameserver2ip})<br/>{/if}
                        {if $serverdata.nameserver3}{$serverdata.nameserver3} ({$serverdata.nameserver3ip})<br/>{/if}
                        {if $serverdata.nameserver4}{$serverdata.nameserver4} ({$serverdata.nameserver4ip})<br/>{/if}
                        {if $serverdata.nameserver5}{$serverdata.nameserver5} ({$serverdata.nameserver5ip})<br/>{/if}
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
    <div class="panel panel-default" id="panelQuickShortcutsPanel">
        <div class="panel-heading">
            <h3 class="panel-title">Quick Shortcuts</h3>
        </div>
        <div class="panel-body text-center">

            <div class="row panel-feature-row">
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=usermanage" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="users">
                        <div class="icon">
                            <i class="fas fa-users fa-4x"></i>
                        </div>
                        <div>
                            <h4>User Manager</h4>
                            <p>Manage users for email, FTP and more...</p>
                        </div>
                    </div>
                </a>

                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=mailboxroutes" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="email">
                        <div class="icon">
                            <i class="fas fa-inbox-in fa-4x"></i>
                        </div>
                        <div>
                            <h4>Email Accounts</h4>
                            <p>Manage Email Accounts</p>
                        </div>
                    </div>
                </a>
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=vacation" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="vacation">
                        <div class="icon">
                            <i class="fas fa-plane fa-4x"></i>
                        </div>
                        <div>
                            <h4>Vacation Responder</h4>
                            <p>Set your out-of-office reply</p>
                        </div>
                    </div>
                </a>

                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=filemanager" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="filemanager">
                        <div class="icon">
                            <i class="fas fa-folder fa-4x"></i>
                        </div>
                        <div>
                            <h4>File Manager</h4>
                            <p>Upload / Download files</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="row panel-feature-row">
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=subdomains" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="subdomains">
                        <div class="icon">
                            <i class="fas fa-columns fa-4x"></i>
                        </div>
                        <div>
                            <h4>Subdomains</h4>
                            <p>Setup subdomains</p>
                        </div>
                    </div>
                </a>
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=domainmanager" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="domainmanager">
                        <div class="icon">
                            <i class="fas fa-cloud fa-4x"></i>
                        </div>
                        <div>
                            <h4>Addon Domains</h4>
                            <p>Manage your Addon Domains</p>
                        </div>
                    </div>
                </a>
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=crontab" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="crontab">
                        <div class="icon">
                            <i class="fas fa-clock fa-4x"></i>
                        </div>
                        <div>
                            <h4>Scheduled Tasks</h4>
                            <p>Manage your crontab</p>
                        </div>
                    </div>
                </a>
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=bandwidthbd" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="bandwidthbd">
                        <div class="icon">
                            <i class="fas fa-chart-line fa-4x"></i>
                        </div>
                        <div>
                            <h4>Bandwidth</h4>
                            <p>View your Bandwidth Usage</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="row panel-feature-row">
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=changemysql" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="changemysql">
                        <div class="icon">
                            <i class="fas fa-database fa-4x"></i>
                        </div>
                        <div>
                            <h4>MySQL Manager</h4>
                            <p>Add / Delete databases</p>
                        </div>
                    </div>
                </a>
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=phpmyadmin" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="phpmyadmin">
                        <div class="icon">
                            <i class="fas fa-toolbox fa-4x"></i>
                        </div>
                        <div>
                            <h4>phpMyAdmin</h4>
                            <p>Manage databases</p>
                        </div>
                    </div>
                </a>
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=terminal" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="terminal">
                        <div class="icon">
                            <i class="fas fa-terminal fa-4x"></i>
                        </div>
                        <div>
                            <h4>SSH / Terminal</h4>
                            <p>Manage your site like a boss</p>
                        </div>
                    </div>
                </a>
                <a href="clientarea.php?action=productdetails&amp;id={$serviceid}&amp;dosinglesignon=1&amp;app=webapps" target="_blank">
                    <div class="col-sm-3 col-xs-6" id="webapps">
                        <div class="icon">
                            <i class="fab fa-wordpress fa-4x"></i>
                        </div>
                        <div>
                            <h4>Web Apps</h4>
                            <p>Install and Manage Apps</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
{/if}
<hr>

<div class="row">
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
<!-- Modal -->
<div class="modal fade" id="whoisModal" tabindex="-1" role="dialog" aria-labelledby="whoisModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="whoisModalLabel">whois data for <strong>{$domain}</strong></h4>
            </div>
            <div class="modal-body">
                <div class="modal-text-wrapper" id="whoisModalContent">
                    <p>Content Loading... <i class="fas fa-spinner fa-pulse"></i></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>{literal}
    $(function() {
        $('#modalTrigger').on('click', function (e) {
            $("#whoisModalContent p").load($(this).data("url"));
        });
        $('#whoisModal').on('shown.bs.modal', function (e) {
            var modal = $(this);
            modal.find('#whoisModalLabel').html('whois data for <strong>{/literal}{$domain}{literal}</strong>');
        });
        $('#whoisModal').on('hidden.bs.modal', function(e){
            var modal = $(this);
            modal.find('#whoisModalContent').html('<p>Content Loading... <i class="fas fa-spinner fa-pulse"></i></p>');
            modal.removeData('bs.modal');
        });
    });{/literal}
</script>