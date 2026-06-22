<?php
/**
 * apnscp/ApisCP Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2026
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

use WHMCS\Database\Capsule as DB;

class Helper
{

    /**
     * Build the ApisCP endpoint URL and API key from either a WHMCS module params
     * array or a tblservers row object.
     *
     * When passed an array (module params), expects the standard WHMCS keys
     * serverhttpprefix, serverhostname, serverport, and serverpassword (pre-decrypted).
     * When passed an object (Eloquent model or stdClass from DB::table), reads the
     * raw tblservers columns secure, hostname, port, and password (encrypted).
     *
     * @param array|object $source Module params array or tblservers row.
     *
     * @return array{endpoint: string, apikey: string}
     */
    public static function buildEndpoint(array|object $source): array
    {
        if (is_array($source)) {
            return [
                'endpoint' => $source['serverhttpprefix'] . '://' . $source['serverhostname'] . ':' . $source['serverport'],
                'apikey'   => $source['serverpassword'],
            ];
        }

        return [
            'endpoint' => ($source->secure === 'on' ? 'https' : 'http') . '://' . $source->hostname . ':' . ($source->port ?: 2083),
            'apikey'   => decrypt($source->password),
        ];
    }

    /**
     * Build the apnscp site-creation options array from WHMCS module parameters.
     *
     * Only the minimal set of parameters required for account creation is
     * populated here (siteinfo, billing, mysql/pgsql admin user). The large
     * commented-out block below is retained as reference documentation for the
     * full set of available apnscp API options.
     *
     * @param array $params Common WHMCS module parameters (domain, username, password, configoptionX, model, serviceid).
     *
     * @return array Associative array of apnscp API option keys to their values.
     */
    public static function generateOptions(array $params): array
    {
//        // Addon Domains
//        if ((int) $params['configoption2'] === 0)
//        {
//            $opts['aliases.enabled'] = 0;
//        }
//        else
//        {
//            $opts['aliases.enabled'] = 1;
//            if ($params['configoption2'] === '-1')
//            {
//                $opts['aliases.max'] = 'null';
//            }
//            else
//            {
//                $opts['aliases.max'] = (int) $params['configoption2']; // Actual Number of addon domains
//            }
//        }
//
//        // Account Password
//        $opts['auth.tpasswd'] = $params['password']; // Plain Text Password for account
//
//        //Bandwidth
//        if ($params['configoption3'] !== '')
//        {
//            $opts['bandwidth.threshold'] = (int) $params['configoption3'];
//            $opts['bandwidth.units']     = 'GB'; //B,KB,MB,GB,TB
//        }
////    $opts['bandwidth.rollover']  = ''; // Day of month that BW resets (defaults to today)
//
////billing Account Linking
//        $opts['billing.invoice'] = 'WHMCS-' . $params['serviceid']; // Invoice id to link to customer
////    $opts['billing.parent_invoice'] = 'WHMCS-Client-' . $params['userid']; // Invoice id to link to customer
//
////cgroup Resource Enforcement
//        if ($params['configoption4'] !== '')
//        {
//            $opts['cgroup.memory'] = (int) $params['configoption4'];//'256'; // Limit memory usage of account in MB
//        }
//        if ($params['configoption5'] !== '')
//        {
//            $opts['cgroup.cpu'] = (int) $params['configoption5']; // Not sure ...
//        }
//        if ($params['configoption6'] !== '')
//        {
//            $opts['cgroup.cpuweight'] = (int) $params['configoption6']; // Allocate added weight to process tasks
//        }
//        if ($params['configoption7'] !== '')
//        {
//            $opts['cgroup.proclimit'] = (int) $params['configoption7']; // [null, 0-4096] Limit account to # processes
//        }
//
////Disk Quota
//        if ($params['configoption8'] !== '')
//        {
//            $opts['diskquota.quota'] = (int) $params['configoption8']; // [null,0-∞] Account storage quota
//            $opts['diskquota.units'] = 'GB'; //[B,KB,MB,GB,TB] Supplied value has specified unit
//        }
//        if ($params['configoption9'] !== '')
//        {
//            $opts['diskquota.fquota'] = $params['configoption9']; // [null,0-∞] Account inode quota
//        }
//
//// DNS Module
////    $opts['dns.enabled']  = '1'; // 0.1
////    $opts['dns.provider'] = $params['configoption13'] ?: 'builtin'; //  [aws,builtin,cloudflare,digitalocean,linode,null,vultr] Assign DNS handler for account
////    $opts['dns.key']      = $params['configoption14'] ?: null; // <string> DNS provider key
//
////FTP Module
//        $opts['ftp.enabled'] = $params['configoption10'] ? 1 : 0;
////    $opts['ftp.ftpserver'] = 'ftp.'; // FTP Prefix
//
////IP Stuffs
//        $opts['ipinfo.enabled']   = 1; // [0,1] Assign account unique IPv4 address from pool
//        $opts['ipinfo.namebased'] = $params['configoption11'] === 'shared' ? 1 : 0; // [0,1] Site uses shared IP address (unique otherwise, see ipaddrs)
//
//// Not supported yet!!!
////IPv6 Stuffs
////    $opts['ipinfo6.enabled']   = $params['configoption12'] === 'disabled' ? '0' : '1'; // [0,1] Assign account unique IPv6 address from pool
////    $opts['ipinfo6.namebased'] = $params['configoption12'] === 'shared' ? '1' : '0'; // [0,1] Site uses shared IP address (unique otherwise, see ipaddrs)
//
//// Logging
////        $opts['logs.enabled'] = 1; // [0,1] Record web server access
//
//// Mail
//        $opts['mail.enabled'] = $params['configoption13'] ? 1 : 0; // [0,1] Enable mail service
//
////MySQL
//        if ((int) $params['configoption14'] === 0)
//        {
//            $opts['mysql.enabled'] = 0; // [0,1] MySQL database access. Required for Web App usage.
//        }
//        else
//        {
//            $opts['mysql.enabled']     = '1'; // [0,1] MySQL database access. Required for Web App usage.
//            $opts['mysql.dbaseadmin']  = $params['username']; // <string> Set mysql admin user
//            $opts['mysql.dbaseprefix'] = $params['username'] . '_'; // <<string> Set MySQL database prefix. Must end with '_'
//            if ($params['configoption15'] !== '')
//            {
//                $opts['mysql.dbasenum'] = $params['configoption14'] === '-1' ? 'null' : $params['configoption14'];  // [null, 0-999] Limit total database count
//            }
//            $opts['mysql.passwd'] = $params['password']; // <string> Plain-text password for mysql user.
//        }
//
////PostgreSQL
//        if ((int) $params['configoption15'] === 0)
//        {
//            $opts['pgsql.enabled'] = 0; // [0,1] Enable PostgreSQL database access. Required for Discourse usage.
//        }
//        else
//        {
//            $opts['pgsql.enabled']     = '1'; // [0,1] Enable PostgreSQL database access. Required for Discourse usage.
//            $opts['pgsql.dbaseadmin']  = $params['username']; // <string> Set pgsql admin user
//            $opts['pgsql.dbaseprefix'] = $params['username'] . '_'; // <string> Set PostgreSQL database prefix. Must end with '_'
//            if ($params['configoption16'] !== '')
//            {
//                $opts['pgsql.dbasenum'] = $params['configoption15'] === '-1' ? 'null' : $params['configoption15']; // [null, 0-999] Limit total database count
//            }
//            $opts['pgsql.passwd'] = $params['password']; // <string> Plain-text password for pgsql user.
//        }
//
//// Rampart
////    $opts['rampart.enabled']   = '1'; // [0,1] Delegate brute-force whitelisting
////    $opts['rampart.max']       = $params['configoption16'] ?: 100; // [-1, 0 => 4096] Maximum number of IP address whitelists.
//        $opts['rampart.whitelist'] = [$params['model']->client->ip]; // IPv4 | IPv6 IPv4 + IPv6 addresses
//
//// Spam Filtering
//        $opts['spamfilter.enabled']  = $params['configoption17'] ? 1 : 0; // [0,1] Mail filtering
//        $opts['spamfilter.provider'] = $params['configoption18']; // [spamassassin,rspamd] Inbound spam filter
//
//// SSH Module
//        $opts['ssh.enabled'] = $params['configoption19'] ? 1 : 0; // [0,1] Enable ssh service
//        $opts['ssh.jail']    = '1'; // [0,1] Jail all SSH sessions to account
//
////SSL
//        $opts['ssl.enabled'] = $params['configoption20'] ? 1 : 0; // [0,1] Enable ssl service
//
//// Users
//        if ((int) $params['configoption21'] === 0)
//        {
//            $opts['users.enabled'] = 0;
//        }
//        else
//        {
//            $opts['users.enabled'] = 1;
//            if ($params['configoption21'] === '-1')
//            {
//                $opts['users.max'] = 'null';
//            }
//            else
//            {
//                $opts['users.max'] = (int) $params['configoption21']; // Actual Number of Users
//            }
//        }

        //Site Info
        $opts['siteinfo.enabled']    = '1'; // [0,1] Core account attributes
        $opts['siteinfo.domain']     = $params['domain']; // <string> Primary domain of the account
        $opts['siteinfo.admin_user'] = $params['username']; // <string> Administrative user of account
        $opts['siteinfo.email']      = $params['model']->client->email; // [email,[email1,email2...]] Contact address on account
        $opts['siteinfo.plan']       = $params['configoption1'];

        //Billing
        $opts['billing.invoice'] = 'WHMCS-' . $params['serviceid']; // Invoice id to link to customer

        // MySQL
        $opts['mysql.dbaseadmin']  = $params['username']; // <string> Set mysql admin user
        $opts['mysql.dbaseprefix'] = $params['username'] . '_'; // <string> Set MySQL database prefix. Must end with '_'

        // PGSQL
        $opts['pgsql.dbaseadmin']  = $params['username']; // <string> Set pgsql admin user
        $opts['pgsql.dbaseprefix'] = $params['username'] . '_'; // <string> Set PostgreSQL database prefix. Must end with '_'

        return $opts;
    }

    /**
     * Convert an options array into an apnscp CLI command string.
     *
     * Each key is converted from dot notation to comma notation and wrapped as
     * a "-c 'key'='value'" argument, then joined into a single command string
     * prefixed with the action name (e.g. "AddDomain" or "EditDomain").
     *
     * @param array  $opts   Associative array of apnscp option keys to their values.
     * @param string $action The CLI action name to prepend (e.g. 'AddDomain').
     *
     * @return string The assembled CLI command string, suitable for logging.
     */
    public static function generateCommand(array $opts, string $action): string
    {
        $optArray = [$action];

        foreach ($opts as $service => $value) {
            $service    = str_replace('.', ',', $service);
            $optArray[] = "-c '{$service}'='{$value}'";
        }

        return implode(' ', $optArray);
    }

    /**
     * Format apnscp's numeric database version into a dotted string.
     *
     * common_get_mysql_version and common_get_postgresql_version return the
     * compiled integer form (major * 10000 + minor * 100 + patch), e.g.
     * 101118 => "10.11.18" and 151800 => "15.18.0". Non-numeric or non-positive
     * input is returned as a plain string, or "-" when empty.
     *
     * @param mixed $raw The raw version value from the apnscp API.
     *
     * @return string The dotted version string.
     */
    public static function formatDbVersion(mixed $raw): string
    {
        $n = (int) $raw;
        if ($n <= 0) {
            return (string) $raw !== '' ? (string) $raw : '-';
        }

        return intdiv($n, 10000) . '.' . intdiv($n % 10000, 100) . '.' . ($n % 100);
    }

    /**
     * Pretty-print an XML string for module log readability.
     *
     * Inserts a newline between adjacent closing/opening tags so each element
     * appears on its own line in the WHMCS module log viewer.
     *
     * @param string|null $xml Raw XML string, or null if no request/response exists yet.
     *
     * @return string The formatted XML string, or an empty string if $xml is null.
     */
    public static function formatXml(?string $xml): string
    {
        return str_ireplace('><', ">\n<", $xml ?? '');
    }

    /**
     * Ensure the required custom fields exist for the given apnscp product.
     *
     * Checks whether the "SiteID" custom field is present on the product and
     * creates it if missing. Uses a per-request static cache so repeated calls
     * for the same product ID within the same request are no-ops.
     *
     * @param int|string $productId The WHMCS product (tblproducts) ID to inspect.
     *
     * @return void
     */
    public static function apnscpValidateCustomFields(int|string $productId): void
    {
        static $validated = [];

        if (isset($validated[$productId])) {
            return;
        }

        $validated[$productId] = true;

        $requiredFields = ['SiteID'];
        $existingFields = [];

        $customFields = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $productId)->get();

        foreach ($customFields as $field) {
            $existingFields[] = $field->fieldname;
        }

        foreach (array_diff($requiredFields, $existingFields) as $field) {
            if ($field === 'SiteID') {
                DB::table('tblcustomfields')->insert([
                    'type'         => 'product',
                    'relid'        => $productId,
                    'fieldname'    => 'SiteID',
                    'fieldtype'    => 'text',
                    'description'  => 'ApisCP Site ID',
                    'fieldoptions' => '',
                    'adminonly'    => 'on',
                    'sortorder'    => 0,
                ]);
            }
        }
    }

    /**
     * Retrieve all custom fields defined for the given product, keyed by field name.
     *
     * Returns an associative array where each key is the custom field's name
     * (e.g. "SiteID") and each value is an array with "id" and "description"
     * entries. Returns an empty array when no custom fields exist.
     *
     * @param int|string $productId The WHMCS product (tblproducts) ID to look up.
     *
     * @return array<string, array{id: int, description: string}>
     */
    public static function apnscpGetCustomFields(int|string $productId): array
    {
        $fields       = [];
        $customFields = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $productId)->get();

        foreach ($customFields as $field) {
            $fields[$field->fieldname] = ['id' => $field->id, 'description' => $field->description];
        }

        return $fields;
    }

    /**
     * Return the tblcustomfieldsvalues row ID for a custom field value.
     *
     * Looks up the record in tblcustomfieldsvalues matching the given hosting
     * service ID and custom field ID, returning the row's primary key. Returns
     * an empty string when no matching record exists.
     *
     * @param int|string $hostingId The tblhosting service ID.
     * @param int|string $fieldId   The tblcustomfields field ID.
     *
     * @return string The row ID, or an empty string if not found.
     */
    public static function apnscpGetCustomFieldId(int|string $hostingId, int|string $fieldId): string
    {
        $result = DB::table('tblcustomfieldsvalues')->where('fieldid', $fieldId)->where('relid', $hostingId)->first();
        return $result->id ?? '';
    }

    /**
     * Insert or update a custom field value for a hosting service.
     *
     * Checks whether a value record already exists for the given hosting
     * service ID and field ID. If it does, the existing record is updated;
     * otherwise a new record is inserted.
     *
     * @param int|string $hostingId The tblhosting service ID.
     * @param int|string $fieldId   The tblcustomfields field ID.
     * @param mixed      $value     The value to store.
     *
     * @return void
     */
    public static function apnscpAddCustomFieldValue(int|string $hostingId, int|string $fieldId, mixed $value): void
    {
        $result = DB::table('tblcustomfieldsvalues')->where('fieldid', $fieldId)->where('relid', $hostingId)->first();
        if (! empty($result)) {
            DB::table('tblcustomfieldsvalues')->where('id', $result->id)->update(['value' => $value]);
        } else {
            DB::table('tblcustomfieldsvalues')->insert(['fieldid' => $fieldId, 'relid' => $hostingId, 'value' => $value]);
        }
    }

    /**
     * Retrieve the stored value of a custom field for a hosting service.
     *
     * Returns the current value from tblcustomfieldsvalues for the given
     * hosting service ID and custom field ID. Returns an empty string when
     * no matching record exists.
     *
     * @param int|string $hostingId The tblhosting service ID.
     * @param int|string $fieldId   The tblcustomfields field ID.
     *
     * @return string The stored field value, or an empty string if not found.
     */
    public static function apnscpGetCustomFieldValue(int|string $hostingId, int|string $fieldId): string
    {
        $result = DB::table('tblcustomfieldsvalues')->where('fieldid', $fieldId)->where('relid', $hostingId)->first();
        return $result->value ?? '';
    }
}