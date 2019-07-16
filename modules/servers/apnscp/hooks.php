<?php
/**
 * apnscp Provisioning Module for WHMCS
 *
 * @copyright   Copyright (c) Lithium Hosting, llc 2019
 * @author      Troy Siedsma (tsiedsma@lithiumhosting.com)
 * @license     see included LICENSE file
 */

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Client edit sample hook function.
 *
 * This sample demonstrates making a service call whenever a change is made to a
 * client profile within WHMCS.
 *
 * @param array $params Parameters dependant upon hook function
 *
 * @return mixed Return dependant upon hook function
 */
//function hook_apnscp_clientedit(array $params)
//{
//    try {
//        // Call the service's function, using the values provided by WHMCS in
//        // `$params`.
//    } catch (Exception $e) {
//        // Consider logging or reporting the error.
//    }
//}

/**
 * Register a hook with WHMCS.
 *
 * add_hook(string $hookPointName, int $priority, string|array|Closure $function)
 */
//add_hook('ClientEdit', 1, 'hook_apnscp_clientedit');

/**
 * Insert a service item to the client area navigation bar.
 *
 * Demonstrates adding an additional link to the Services navbar menu that
 * provides a shortcut to a filtered products/services list showing only the
 * products/services assigned to the module.
 *
 * @param \WHMCS\View\Menu\Item $menu
 */
//add_hook('ClientAreaPrimaryNavbar', 1, function ($menu)
//{
//    // Check whether the services menu exists.
//    if (!is_null($menu->getChild('Services'))) {
//        // Add a link to the module filter.
//        $menu->getChild('Services')
//            ->addChild(
//                'Provisioning Module Products',
//                array(
//                    'uri' => 'clientarea.php?action=services&module=apnscp',
//                    'order' => 15,
//                )
//            );
//    }
//});

/**
 * Render a custom sidebar panel in the secondary sidebar.
 *
 * Demonstrates the creation of an additional sidebar panel on any page where
 * the My Services Actions default panel appears and populates it with a title,
 * icon, body and footer html output and a child link.  Also sets it to be in
 * front of any other panels defined up to this point.
 *
 * @param \WHMCS\View\Menu\Item $secondarySidebar
 */
add_hook('ClientAreaPrimarySidebar', 1, function ($primarySidebar)
{
    // determine if we are on a page containing My Services Actions
    if (!is_null($sidebar = $primarySidebar->getChild('Service Details Overview'))) {

        // define new sidebar panel
        $customPanel = $sidebar->addChild('Test');

        // set panel attributes
        $customPanel->moveToFront()
            ->setIcon('fa-user')
            ->setBodyHtml(
                'Your HTML output goes here...'
            )
            ->setFooterHtml(
                'Footer HTML can go here...'
            );

        // define link
        $customPanel->addChild(
                'Sample Link Menu Item',
                array(
                    'uri' => 'clientarea.php?action=services&module=apnscp',
                    'icon'  => 'fa-list-alt',
                    'order' => 2,
                )
            );

    }
});
