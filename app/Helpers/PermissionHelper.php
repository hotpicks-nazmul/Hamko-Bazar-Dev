<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use App\Models\Setting;

class PermissionHelper
{
    private static ?Collection $cachedPermissions = null;
    private static ?array $cachedModules = null;
    private static ?array $hiddenSubmenus = null;
    private static ?array $hiddenModules = null;

    /**
     * Get list of submenu keys that are hidden from sidebar.
     */
    public static function hiddenSubmenus(): array
    {
        if (self::$hiddenSubmenus !== null) {
            return self::$hiddenSubmenus;
        }

        $raw = Setting::get('sidebar_hidden_submenus', '[]');
        $decoded = json_decode($raw, true);
        return self::$hiddenSubmenus = is_array($decoded) ? $decoded : [];
    }

    /**
     * Toggle a submenu's sidebar visibility.
     * Returns the new state (true = visible, false = hidden).
     */
    public static function toggleSubmenuVisibility(string $submenuKey): bool
    {
        $hidden = self::hiddenSubmenus();
        if (in_array($submenuKey, $hidden)) {
            $hidden = array_values(array_filter($hidden, fn($s) => $s !== $submenuKey));
            Setting::set('sidebar_hidden_submenus', json_encode($hidden));
            self::$hiddenSubmenus = $hidden;
            return true;
        } else {
            $hidden[] = $submenuKey;
            Setting::set('sidebar_hidden_submenus', json_encode($hidden));
            self::$hiddenSubmenus = $hidden;
            return false;
        }
    }

    /**
     * Check if a submenu is visible in the sidebar.
     */
    public static function isSubmenuVisible(string $submenuKey): bool
    {
        return !in_array($submenuKey, self::hiddenSubmenus());
    }

    /**
     * Check if user can see a submenu (global override + per-user permission).
     * Super admins and admins bypass per-user check.
     */
    public static function canUserSeeSubmenu(string $routeName): bool
    {
        // Global master override (admin hides from everyone)
        if (!self::isSubmenuVisible($routeName)) {
            return false;
        }

        $user = auth()->user();
        if (!$user) return false;
        
        // Super admin bypass per-user check
        if ($user->role === 'super_admin') {
            return true;
        }

        // Staff - check per-user submenu permission
        return $user->hasPermission('submenu:' . $routeName);
    }

    /**
     * Get comprehensive module-to-action mapping.
     * Defines which CRUD and special actions each module supports.
     */
    public static function moduleActions(): array
    {
        return [
            'dashboard'  => ['view', 'view-revenue', 'view-sales'],
            'analytics'  => ['view', 'export', 'view-revenue', 'view-sales'],
            'products'   => ['view', 'create', 'edit', 'export', 'view-cost'],
            'inventory'  => ['view'],
            'orders'     => ['view', 'view-customer', 'view-pricing'],
            'delivery'   => ['view'],
            'refund'     => ['view', 'view-customer'],
            'customers'  => ['view', 'export', 'view-financial'],
            'sellers'    => ['view'],
            'affiliate'  => ['view'],
            'media'      => ['view'],
            'reports'    => ['view'],
            'marketing'  => ['view'],
            'support'    => ['view'],
            'otp'        => ['view'],
            'content'    => ['view'],
            'appearance' => ['view'],
            'settings'   => ['view'],
            'locations'  => ['view'],
            'warehouse'  => ['view', 'create'],
            'staffs'     => ['view'],
            'system'     => ['view'],
            'pos'        => ['view'],
            'addon'      => ['view'],
            'multistore' => ['view'],
        ];
    }

    /**
     * Get list of all section-level permission actions (used to filter them out of module actions).
     */
    public static function sectionActions(): array
    {
        return [
            'view-customer', 'view-pricing', 'view-cost', 'view-financial', 'view-revenue', 'view-sales',
        ];
    }

    /**
     * Get all submenus mapping (submenu_key => route_name => label).
     * Complete coverage of ALL sidebar navigation items.
     */
    public static function submenus(): array
    {
        return [
            'products' => [
                'admin.products.create' => 'Add New Product',
                'admin.products.index' => 'All Products',
                'admin.categories.index' => 'Category',
                'admin.products.in-house' => 'In-House Products',
                'admin.products.digital.index' => 'Digital Products',
                'admin.digital-categories.index' => 'Digital Categories',
                'admin.products.bulk-import' => 'Bulk Import',
                'admin.products.bulk-export' => 'Bulk Export',
                'admin.products.bulk-discount' => 'Bulk Discount',
                'admin.brands.index' => 'Brand',
                'admin.attributes.index' => 'Attribute',
                'admin.colors.index' => 'Colors',
                'admin.reviews.index' => 'Product Reviews',
                'admin.product-qa.index' => 'Product Q&A',
                'admin.wishlists.index' => 'Wishlist Management',
            ],
            'inventory' => [
                'admin.inventory.index' => 'Inventory Overview',
                'admin.inventory.stock-alerts' => 'Stock Alerts',
                'admin.inventory.stock-history' => 'Stock History',
            ],
            'orders' => [
                'admin.orders.in-house' => 'In-House Orders',
                'admin.orders.seller' => 'Seller Orders',
                'admin.orders.pickup-point' => 'Pickup Point Orders',
                'admin.quotations.index' => 'Quotations',
                'admin.subscriptions.index' => 'Subscriptions',
            ],
            'delivery' => [
                'admin.delivery.index' => 'Delivery Dashboard',
                'admin.delivery.partners.index' => 'Delivery Partners',
                'admin.delivery.carriers.index' => 'Carriers',
                'admin.delivery.tracking' => 'Shipment Tracking',
                'admin.delivery.zones.index' => 'Delivery Zones',
                'admin.delivery.courier-integration' => 'Courier Integration',
                'admin.delivery.delivery-boys.index' => 'Delivery Boys',
                'admin.pickup-points.index' => 'Pick-up Points',
                'admin.delivery.schedules.index' => 'Delivery Schedules',
                'admin.delivery.reports' => 'Delivery Reports',
            ],
            'refund' => [
                'admin.refunds.index' => 'All Refunds',
                'admin.refunds.requests' => 'Refund Requests',
                'admin.refunds.approved' => 'Approved Refunds',
                'admin.refunds.rejected' => 'Rejected Refunds',
                'admin.refunds.configuration' => 'Refund Configuration',
            ],
            'customers' => [
                'admin.customers.index' => 'All Customers',
                'admin.customers.groups.index' => 'Customer Groups',
                'admin.customers.segmentation.index' => 'Customer Segmentation',
                'admin.customers.loyalty.index' => 'Loyalty Points',
                'admin.customers.membership.index' => 'Membership Plans',
                'admin.customers.wallet.index' => 'Customer Wallet',
            ],
            'sellers' => [
                'admin.sellers.index' => 'All Sellers',
                'admin.sellers.payouts' => 'Payouts',
                'admin.sellers.payout-requests' => 'Payout Requests',
                'admin.sellers.commission' => 'Seller Commission',
                'admin.sellers.verification' => 'Seller Verification',
            ],
            'affiliate' => [
                'admin.affiliate.users.index' => 'Affiliate Users',
                'admin.affiliate.configuration' => 'Affiliate Configuration',
                'admin.affiliate.payouts' => 'Affiliate Payouts',
                'admin.affiliate.requests' => 'Affiliate Requests',
                'admin.affiliate.categories.index' => 'Affiliate Categories',
                'admin.affiliate.products.index' => 'Affiliate Products',
                'admin.affiliate.links.index' => 'Affiliate Links',
                'admin.affiliate.banners.index' => 'Affiliate Banners',
                'admin.affiliate.reports' => 'Affiliate Reports',
            ],
            'media' => [
                'admin.media.index' => 'Media',
            ],
            'reports' => [
                'admin.reports.in-house-product-sale' => 'In-House Product Sale',
                'admin.reports.seller-sales' => 'Seller Products Sale',
                'admin.reports.inventory' => 'Products Stock',
                'admin.reports.wishlist' => 'Products Wishlist',
                'admin.reports.user-searches' => 'User Searches',
                'admin.reports.commission-history' => 'Commission History',
                'admin.reports.wallet-history' => 'Wallet Recharge History',
                'admin.jakat.index' => 'Jakat Calculator',
            ],
            'marketing' => [
                'admin.marketing.flash-deals.index' => 'Flash Deals',
                'admin.marketing.newsletters.index' => 'Newsletters',
                'admin.marketing.bulk-sms.index' => 'Bulk SMS',
                'admin.marketing.subscribers.index' => 'Subscribers',
                'admin.coupons.index' => 'Coupon',
                'admin.marketing.abandoned-cart.index' => 'Abandoned Cart Recovery',
                'admin.marketing.gift-cards.index' => 'Gift Cards',
                'admin.marketing.push-notifications.index' => 'Push Notifications',
                'admin.marketing.price-rules.index' => 'Price Rules',
            ],
            'support' => [
                'admin.support.tickets.index' => 'Ticket',
                'admin.chat.index' => 'Live Chat',
                'admin.chat.ai-settings.index' => 'AI Chatbot Settings',
                'admin.chat.widget-settings.index' => 'Chat Widget Settings',
                'admin.chat.predefined.index' => 'Quick Replies',
                'admin.settings.whatsapp' => 'WhatsApp Chat',
                'admin.support.product-queries.index' => 'Product Queries',
            ],
            'otp' => [
                'admin.otp.configuration' => 'OTP Configurations',
                'admin.otp.sms-templates' => 'SMS Templates',
                'admin.otp.credentials' => 'Set OTP Credentials',
            ],
            'content' => [
                'admin.pages.index' => 'Pages',
                'admin.blogs.index' => 'Blog Posts',
                'admin.blog-categories.index' => 'Blog Categories',
                'admin.blog-tags.index' => 'Blog Tags',
                'admin.form-builder.index' => 'Form Builder',
                'admin.faqs.index' => 'FAQs',
                'admin.content.widgets.index' => 'Widget Manager',
            ],
            'appearance' => [
                'admin.themes.index' => 'Themes',
                'admin.menus.index' => 'Menu Builder',
                'admin.sliders.index' => 'Sliders',
                'admin.banners.index' => 'Banners',
                'admin.hero.index' => 'Hero Section',
                'admin.homepage.index' => 'Home Page Settings',
            ],
            'settings' => [
                'admin.settings.general' => 'General Settings',
                'admin.settings.languages' => 'Languages',
                'admin.settings.currency' => 'Currency',
                'admin.settings.vat-tax' => 'VAT & Tax',
                'admin.settings.email' => 'SMTP Settings',
                'admin.payment-gateways.index' => 'Payment Methods',
                'admin.settings.order-configuration' => 'Order Configuration',
                'admin.settings.file-system' => 'File System & Cache',
                'admin.settings.social-login' => 'Social Media Logins',
                'admin.settings.shipping' => 'Shipping',
                'admin.seo.index' => 'SEO Settings',
                'admin.settings.footer' => 'Footer Settings',
                'admin.settings.email-templates.index' => 'Email Templates',
                'admin.settings.notifications.index' => 'Notification Settings',
                'admin.api-keys.index' => 'API Keys & Integrations',
                'admin.backup' => 'Backup & Restore',
            ],
            'locations' => [
                'admin.locations.countries.index' => 'Countries',
                'admin.locations.states.index' => 'States',
                'admin.locations.cities.index' => 'Cities',
                'admin.locations.areas.index' => 'Areas',
            ],
            'warehouse' => [
                'admin.warehouses.picking' => 'My Warehouse',
                'admin.warehouses.index' => 'All Warehouses',
                'admin.warehouses.create' => 'Add Warehouse',
            ],
            'staffs' => [
                'admin.staffs.index' => 'All Staffs',
                'admin.staffs.warehouse' => 'Warehouse Staffs',
                'admin.permissions.index' => 'Permission Keys',
            ],
            'system' => [
                'admin.system.update' => 'Update',
                'admin.system.server-status' => 'Server Status',
                'admin.system.activity-logs.index' => 'Activity Logs',
                'admin.system.data-export.index' => 'Data Export/Import',
            ],
            'pos' => [
                'admin.pos.terminal' => 'POS Terminal',
                'admin.pos.cash-register' => 'Cash Register',
                'admin.pos.reports' => 'POS Reports',
            ],
            'addon' => [
                'admin.addons.index' => 'Addon Manager',
            ],
            'multistore' => [
                'admin.multi-store.index' => 'All Stores',
                'admin.multi-store.create' => 'Add Store',
            ],
            'analytics' => [
                'admin.analytics.index' => 'Analytics',
            ],
            'dashboard' => [
                'admin.dashboard' => 'Dashboard',
            ],
        ];
    }

    /**
     * Map submenu pages to their section-level permission actions.
     * Key = route name, value = permission actions that apply to this page.
     */
    public static function pageSectionPermissions(): array
    {
        return [
            // ===== Products =====
            'admin.products.edit'            => ['view-cost'],
            'admin.products.create'          => ['view-cost'],
            'admin.products.show'            => ['view-cost'],
            'admin.products.index'           => ['view-cost'],
            'admin.products.in-house'        => ['view-cost'],
            'admin.products.digital.index'   => ['view-cost'],
            'admin.products.bulk-import'     => ['view-cost'],
            'admin.products.bulk-export'     => [],
            'admin.products.bulk-discount'   => ['view-cost'],
            'admin.categories.index'         => [],
            'admin.digital-categories.index' => [],
            'admin.brands.index'             => [],
            'admin.attributes.index'         => [],
            'admin.colors.index'             => [],
            'admin.reviews.index'            => [],
            'admin.product-qa.index'         => [],
            'admin.wishlists.index'          => [],

            // ===== Inventory =====
            'admin.inventory.index'          => ['view-cost'],
            'admin.inventory.stock-alerts'   => [],
            'admin.inventory.stock-history'  => ['view-cost'],

            // ===== Orders / Sales =====
            'admin.orders.in-house'          => [],
            'admin.orders.seller'            => [],
            'admin.orders.pickup-point'      => ['view-customer', 'view-pricing'],
            'admin.orders.index'             => ['view-customer', 'view-pricing'],
            'admin.orders.show'              => ['view-customer', 'view-pricing'],
            'admin.quotations.index'         => ['view-customer', 'view-pricing'],
            'admin.subscriptions.index'      => ['view-customer', 'view-pricing'],

            // ===== Delivery =====
            'admin.delivery.index'           => ['view-revenue'],
            'admin.delivery.partners.index'  => [],
            'admin.delivery.carriers.index'  => ['view-revenue'],
            'admin.delivery.tracking'        => [],
            'admin.delivery.zones.index'     => ['view-revenue'],
            'admin.delivery.courier-integration' => [],
            'admin.delivery.delivery-boys.index' => [],
            'admin.pickup-points.index'      => [],
            'admin.delivery.schedules.index' => [],
            'admin.delivery.reports'         => ['view-revenue'],

            // ===== Refunds =====
            'admin.refunds.index'            => ['view-customer'],
            'admin.refunds.requests'         => ['view-customer'],
            'admin.refunds.approved'         => ['view-customer'],
            'admin.refunds.rejected'         => ['view-customer'],
            'admin.refunds.configuration'    => [],

            // ===== Customers =====
            'admin.customers.index'          => ['view-financial'],
            'admin.customers.show'           => ['view-financial'],
            'admin.customers.edit'           => ['view-financial'],
            'admin.customers.groups.index'   => [],
            'admin.customers.segmentation.index' => [],
            'admin.customers.loyalty.index'  => ['view-financial'],
            'admin.customers.membership.index' => ['view-financial'],
            'admin.customers.wallet.index'   => ['view-financial'],

            // ===== Sellers =====
            'admin.sellers.index'            => ['view-financial'],
            'admin.sellers.show'             => ['view-financial', 'view-revenue'],
            'admin.sellers.edit'             => ['view-financial'],
            'admin.sellers.payouts'          => ['view-financial'],
            'admin.sellers.payout-requests'  => ['view-financial'],
            'admin.sellers.commission'       => ['view-financial'],
            'admin.sellers.verification'     => [],

            // ===== Affiliate =====
            'admin.affiliate.users.index'    => ['view-financial'],
            'admin.affiliate.configuration'  => [],
            'admin.affiliate.payouts'        => ['view-financial'],
            'admin.affiliate.requests'       => [],
            'admin.affiliate.categories.index' => [],
            'admin.affiliate.products.index' => [],
            'admin.affiliate.links.index'    => [],
            'admin.affiliate.banners.index'  => [],
            'admin.affiliate.reports'        => ['view-financial'],

            // ===== Media =====
            'admin.media.index'              => [],

            // ===== Reports =====
            'admin.reports.in-house-product-sale' => ['view-revenue', 'view-sales'],
            'admin.reports.seller-sales'          => ['view-revenue', 'view-sales'],
            'admin.reports.inventory'             => ['view-cost'],
            'admin.reports.wishlist'              => [],
            'admin.reports.user-searches'         => [],
            'admin.reports.commission-history'    => ['view-financial'],
            'admin.reports.wallet-history'        => ['view-financial'],
            'admin.jakat.index'                   => [],

            // ===== Marketing =====
            'admin.marketing.flash-deals.index'    => [],
            'admin.marketing.newsletters.index'    => [],
            'admin.marketing.bulk-sms.index'       => [],
            'admin.marketing.subscribers.index'    => [],
            'admin.coupons.index'                  => [],
            'admin.marketing.abandoned-cart.index' => [],
            'admin.marketing.gift-cards.index'     => [],
            'admin.marketing.push-notifications.index' => [],
            'admin.marketing.price-rules.index'    => [],

            // ===== Support =====
            'admin.support.tickets.index'        => [],
            'admin.chat.index'                   => [],
            'admin.chat.ai-settings.index'       => [],
            'admin.chat.widget-settings.index'    => [],
            'admin.chat.predefined.index'        => [],
            'admin.settings.whatsapp'            => [],
            'admin.support.product-queries.index' => [],

            // ===== OTP =====
            'admin.otp.configuration'    => [],
            'admin.otp.sms-templates'    => [],
            'admin.otp.credentials'      => [],

            // ===== Content =====
            'admin.pages.index'             => [],
            'admin.blogs.index'            => [],
            'admin.blog-categories.index' => [],
            'admin.blog-tags.index'       => [],
            'admin.form-builder.index'    => [],
            'admin.faqs.index'           => [],
            'admin.content.widgets.index' => [],

            // ===== Appearance =====
            'admin.themes.index'    => [],
            'admin.menus.index'     => [],
            'admin.sliders.index'   => [],
            'admin.banners.index'   => [],
            'admin.hero.index'      => [],
            'admin.homepage.index'  => [],

            // ===== Settings =====
            'admin.settings.general'               => [],
            'admin.settings.languages'             => [],
            'admin.settings.currency'              => [],
            'admin.settings.vat-tax'               => [],
            'admin.settings.email'                 => [],
            'admin.payment-gateways.index'         => [],
            'admin.settings.order-configuration'   => ['view-customer', 'view-pricing'],
            'admin.settings.file-system'           => [],
            'admin.settings.social-login'          => [],
            'admin.settings.shipping'              => ['view-revenue'],
            'admin.seo.index'                      => [],
            'admin.settings.footer'                => [],
            'admin.settings.email-templates.index' => [],
            'admin.settings.notifications.index' => [],
            'admin.api-keys.index'                 => [],
            'admin.backup'                        => [],

            // ===== Locations =====
            'admin.locations.countries.index' => [],
            'admin.locations.states.index'    => [],
            'admin.locations.cities.index'    => [],
            'admin.locations.areas.index'     => [],

            // ===== Warehouse =====
            'admin.warehouses.index'  => [],
            'admin.warehouses.create' => [],

            // ===== Staffs =====
            'admin.staffs.index'       => [],
            'admin.staffs.warehouse'   => [],
            'admin.permissions.index'  => [],

            // ===== System =====
            'admin.system.update'        => [],
            'admin.system.server-status' => [],
            'admin.system.activity-logs.index' => [],
            'admin.system.data-export.index' => [],

            // ===== POS =====
            'admin.pos.terminal'          => [],
            'admin.pos.cash-register' => [],
            'admin.pos.reports'       => ['view-revenue', 'view-sales'],

            // ===== Addon =====
            'admin.addons.index' => [],

            // ===== Multi-Store =====
            'admin.multi-store.index'  => [],
            'admin.multi-store.create' => [],

            // ===== Analytics =====
            'admin.analytics.index'   => ['view-revenue', 'view-sales'],

            // ===== Dashboard =====
            'admin.dashboard'         => ['view-sales', 'view-revenue'],
        ];
    }

    /**
     * Page-level action permissions for every page (list AND detail).
     * Key = route name, value = flat list of permission names for that page.
     */
    public static function pageActions(): array
    {
        $flat = [];
        foreach (self::pageComponents() as $route => $page) {
            if (isset($page['items'])) {
                $flat[$route] = array_merge($flat[$route] ?? [], array_values($page['items']));
            }
            if (isset($page['groups'])) {
                foreach ($page['groups'] as $group) {
                    if (isset($group['items'])) {
                        $flat[$route] = array_merge($flat[$route] ?? [], array_values($group['items']));
                    }
                    if (isset($group['children'])) {
                        foreach ($group['children'] as $child) {
                            $childRoute = $child['route'] ?? '';
                            if ($childRoute && isset(self::pageComponents()[$childRoute]['items'])) {
                                $flat[$childRoute] = array_merge(
                                    $flat[$childRoute] ?? [],
                                    array_values(self::pageComponents()[$childRoute]['items'])
                                );
                            }
                        }
                    }
                }
            }
        }
        return $flat;
    }

    /**
     * Page tree: route → { items, groups }
     *
     * 'items' => ['Label' => 'permission.name']   — direct toggle pills
     * 'groups' => [
     *     'Group Label' => [
     *         'items'    => ['Label' => 'permission.name'],
     *         'children' => ['Item Label' => ['route' => 'child.route', 'label' => 'Child Label']],
     *     ],
     * ]
     */
    public static function pageComponents(): array
    {
        return [
            'admin.orders.in-house' => [
                'items' => [
                    'Create Order'  => 'orders.inhouse-create',
                    'Export'        => 'orders.inhouse-export',
                    'Summary Cards' => 'orders.inhouse-summary-cards',
                ],
                'groups' => [
                    'Table Columns' => [
                        'items' => [
                            'Customer'       => 'orders.view-customer',
                            'Pricing'        => 'orders.view-pricing',
                            'Pricing Detail' => 'orders.view-pricing-detail',
                            'Discount'       => 'orders.view-discount',
                        ],
                    ],
                    'Table Actions' => [
                        'items' => [
                            'View Details'   => 'orders.inhouse-view-details',
                            'Edit'           => 'orders.inhouse-edit',
                            'Delete'         => 'orders.inhouse-delete',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.orders.show',
                                'label' => 'Order Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.orders.seller' => [
                'items' => [
                    'Export' => 'orders.seller-export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'View Details'   => 'orders.seller-view-details',
                            'Edit'           => 'orders.seller-edit',
                            'Delete'         => 'orders.seller-delete',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.orders.seller.show',
                                'label' => 'Seller Order Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.orders.seller.show' => [
                'items' => [
                    'Update Order Status'    => 'orders.seller-update-status',
                    'Invoice'               => 'orders.seller-invoice',
                    'Customer Info'         => 'orders.seller-customer-info',
                    'Billing Address'       => 'orders.seller-billing-address',
                    'Shipping Address'      => 'orders.seller-shipping-address',
                    'Payment Details'       => 'orders.seller-payment-details',
                    'Order Items'           => 'orders.seller-order-items',
                    'Timeline'              => 'orders.seller-timeline',
                ],
            ],
            'admin.orders.pickup-point' => [
                'items' => [
                    'Export' => 'orders.pickup-export',
                ],
                'groups' => [
                    'Table Columns' => [
                        'items' => [
                            'Customer'       => 'orders.view-customer',
                            'Pricing'        => 'orders.view-pricing',
                            'Pricing Detail' => 'orders.view-pricing-detail',
                            'Discount'       => 'orders.view-discount',
                        ],
                    ],
                    'Table Actions' => [
                        'items' => [
                            'View Details'   => 'orders.pickup-view-details',
                            'Mark Picked Up' => 'orders.pickup-mark-picked',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.orders.pickup-point.show',
                                'label' => 'Pickup Order Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.orders.pickup-point.show' => [
                'items' => [
                    'Status Management'   => 'orders.pickup-update-status',
                    'Invoice'             => 'orders.pickup-invoice',
                    'Customer Info'       => 'orders.pickup-customer-info',
                    'Pickup Address'      => 'orders.pickup-address',
                    'Payment Details'     => 'orders.pickup-payment-details',
                    'Order Items'         => 'orders.pickup-order-items',
                    'Mark as Picked Up'   => 'orders.pickup-mark-picked-up',
                    'Timeline'            => 'orders.pickup-timeline',
                ],
            ],
            'admin.orders.show' => [
                'items' => [
                    'Update Order Status'   => 'orders.show-update-status',
                    'Update Payment Status'  => 'orders.show-update-payment',
                    'Invoice'               => 'orders.show-invoice',
                    'Customer Info'         => 'orders.show-customer-info',
                    'Billing Address'       => 'orders.show-billing-address',
                    'Shipping Address'      => 'orders.show-shipping-address',
                    'Payment Details'       => 'orders.show-payment-details',
                    'Order Items'           => 'orders.show-order-items',
                    'Timeline'              => 'orders.show-timeline',
                    'Ship Order'            => 'orders.show-ship-order',
                    'Add Product'           => 'orders.inhouse-add-product',
                    'Edit Item'             => 'orders.inhouse-edit-item',
                    'Remove Item'           => 'orders.inhouse-remove-item',
                    'Change Warehouse'      => 'orders.inhouse-change-warehouse',
                ],
            ],
            'admin.quotations.index' => [
                'items' => [
                    'Create Quotation' => 'quotations.create',
                    'Export'           => 'quotations.export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'View Details'    => 'quotations.view-details',
                            'Edit'            => 'quotations.edit',
                            'Delete'          => 'quotations.delete',
                            'Convert to Order'=> 'quotations.convert-to-order',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.quotations.show',
                                'label' => 'Quotation Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.quotations.show' => [
                'items' => [
                    'Status Management' => 'quotations.update-status',
                    'Convert to Order'  => 'quotations.convert-to-order',
                    'Customer Info'     => 'quotations.customer-info',
                    'Billing Address'   => 'quotations.billing-address',
                    'Shipping Address'  => 'quotations.shipping-address',
                    'Payment Details'   => 'quotations.payment-details',
                    'Items'             => 'quotations.items',
                    'Notes'             => 'quotations.notes',
                    'Timeline'          => 'quotations.timeline',
                ],
            ],
            'admin.subscriptions.index' => [
                'items' => [
                    'Create Subscription' => 'subscriptions.create',
                    'Export'              => 'subscriptions.export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'View Details' => 'subscriptions.view-details',
                            'Edit'         => 'subscriptions.edit',
                            'Delete'       => 'subscriptions.delete',
                            'Activate'     => 'subscriptions.activate',
                            'Pause'        => 'subscriptions.pause',
                            'Cancel'       => 'subscriptions.cancel',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.subscriptions.show',
                                'label' => 'Subscription Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.subscriptions.show' => [
                'items' => [
                    'Status Management'     => 'subscriptions.update-status',
                    'Customer Info'         => 'subscriptions.customer-info',
                    'Billing Address'       => 'subscriptions.billing-address',
                    'Payment Details'       => 'subscriptions.payment-details',
                    'Subscription Plan'     => 'subscriptions.plan',
                    'Billing History'       => 'subscriptions.billing-history',
                    'Timeline'              => 'subscriptions.timeline',
                    'Activate'              => 'subscriptions.activate',
                    'Pause'                 => 'subscriptions.pause',
                    'Cancel'                => 'subscriptions.cancel',
                    'Process Billing'       => 'subscriptions.process-billing',
                ],
            ],

            // ========================
            // ===== PRODUCTS =====
            // ========================
            'admin.categories.index' => [
                'items' => [
                    'Create Category'  => 'categories.create',
                    'Edit Category'    => 'categories.edit',
                    'Delete Category'  => 'categories.delete',
                ],
            ],
            'admin.digital-categories.index' => [
                'items' => [
                    'Create'  => 'digital-categories.create',
                    'Edit'    => 'digital-categories.edit',
                    'Delete'  => 'digital-categories.delete',
                ],
            ],
            'admin.products.in-house' => [
                'items' => [
                    'Create Product' => 'products.inhouse-create',
                    'Export'         => 'products.inhouse-export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'Quick Edit'    => 'products.inhouse-view',
                            'Edit'          => 'products.edit',
                            'Delete'        => 'products.inhouse-delete',
                            'Duplicate'     => 'products.inhouse-duplicate',
                        ],
                        'children' => [
                            'Quick Edit' => [
                                'route' => 'admin.products.show',
                                'label' => 'Product Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.products.digital.index' => [
                'items' => [
                    'Create Digital' => 'products.digital-create',
                    'Export'         => 'products.digital-export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'Edit'   => 'products.digital-edit',
                            'Delete' => 'products.digital-delete',
                        ],
                    ],
                ],
            ],
            'admin.products.bulk-import' => [
                'items' => [
                    'Import File'  => 'products.bulk-import-file',
                    'Download CSV' => 'products.bulk-download-csv',
                ],
            ],
            'admin.products.bulk-export' => [
                'items' => [
                    'Export All'    => 'products.bulk-export-all',
                    'Export Filter' => 'products.bulk-export-filter',
                ],
            ],
            'admin.products.bulk-discount' => [
                'items' => [
                    'Apply Discount'  => 'products.bulk-apply-discount',
                    'Remove Discount' => 'products.bulk-remove-discount',
                ],
            ],
            'admin.brands.index' => [
                'items' => [
                    'Create Brand'  => 'brands.create',
                    'Edit Brand'    => 'brands.edit',
                    'Delete Brand'  => 'brands.delete',
                ],
            ],
            'admin.attributes.index' => [
                'items' => [
                    'Create Attribute'  => 'attributes.create',
                    'Edit Attribute'    => 'attributes.edit',
                    'Delete Attribute'  => 'attributes.delete',
                ],
            ],
            'admin.colors.index' => [
                'items' => [
                    'Create Color'  => 'colors.create',
                    'Edit Color'    => 'colors.edit',
                    'Delete Color'  => 'colors.delete',
                ],
            ],
            'admin.reviews.index' => [
                'items' => [
                    'Approve Review' => 'reviews.approve',
                    'Delete Review'  => 'reviews.delete',
                ],
            ],
            'admin.product-qa.index' => [
                'items' => [
                    'Approve Q&A' => 'product-qa.approve',
                    'Reply'       => 'product-qa.reply',
                    'Delete'      => 'product-qa.delete',
                ],
            ],
            'admin.wishlists.index' => [
                'items' => [
                    'Export' => 'wishlists.export',
                ],
            ],

            // ========================
            // ===== INVENTORY =====
            // ========================
            'admin.inventory.index' => [
                'items' => [
                    'Export'       => 'inventory.export',
                    'Adjust Stock' => 'inventory.adjust-stock',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'View Details'   => 'inventory.view-details',
                            'Stock History'  => 'inventory.view-history',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.inventory.show',
                                'label' => 'Inventory Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.inventory.show' => [
                'items' => [
                    'Adjust Stock'     => 'inventory.adjust-stock',
                    'Transfer Stock'   => 'inventory.transfer-stock',
                    'Stock History'    => 'inventory.view-history',
                    'Stock Alerts'     => 'inventory.stock-alerts',
                ],
            ],
            'admin.inventory.stock-alerts' => [
                'items' => [
                    'Mark Resolved' => 'inventory.alerts-resolve',
                    'Export'        => 'inventory.alerts-export',
                ],
            ],
            'admin.inventory.stock-history' => [
                'items' => [
                    'Export' => 'inventory.history-export',
                ],
            ],

            // ========================
            // ===== DELIVERY =====
            // ========================
            'admin.delivery.index' => [
                'items' => [
                    'Assign Delivery Boy' => 'delivery.assign-boy',
                    'Update Status'       => 'delivery.update-status',
                    'Export'              => 'delivery.export',
                ],
            ],
            'admin.delivery.partners.index' => [
                'items' => [
                    'Add Partner'    => 'delivery.partners-add',
                    'Edit Partner'   => 'delivery.partners-edit',
                    'Delete Partner' => 'delivery.partners-delete',
                ],
            ],
            'admin.delivery.carriers.index' => [
                'items' => [
                    'Add Carrier'    => 'delivery.carriers-add',
                    'Edit Carrier'   => 'delivery.carriers-edit',
                    'Delete Carrier' => 'delivery.carriers-delete',
                ],
            ],
            'admin.delivery.tracking' => [
                'items' => [
                    'Track Shipment' => 'delivery.track-shipment',
                    'Update Status'  => 'delivery.track-update',
                ],
            ],
            'admin.delivery.zones.index' => [
                'items' => [
                    'Add Zone'    => 'delivery.zones-add',
                    'Edit Zone'   => 'delivery.zones-edit',
                    'Delete Zone' => 'delivery.zones-delete',
                ],
            ],
            'admin.delivery.courier-integration' => [
                'items' => [
                    'Configure API' => 'delivery.courier-configure',
                    'Test Connection' => 'delivery.courier-test',
                ],
            ],
            'admin.delivery.delivery-boys.index' => [
                'items' => [
                    'Add Boy'    => 'delivery.boys-add',
                    'Edit Boy'   => 'delivery.boys-edit',
                    'Delete Boy' => 'delivery.boys-delete',
                ],
            ],
            'admin.pickup-points.index' => [
                'items' => [
                    'Add Point'    => 'delivery.pickup-add',
                    'Edit Point'   => 'delivery.pickup-edit',
                    'Delete Point' => 'delivery.pickup-delete',
                ],
            ],
            'admin.delivery.schedules.index' => [
                'items' => [
                    'Add Schedule'    => 'delivery.schedules-add',
                    'Edit Schedule'   => 'delivery.schedules-edit',
                    'Delete Schedule' => 'delivery.schedules-delete',
                ],
            ],
            'admin.delivery.reports' => [
                'items' => [
                    'Export'           => 'delivery.reports-export',
                    'Delivery Summary' => 'delivery.reports-summary',
                ],
            ],

            // ========================
            // ===== REFUNDS =====
            // ========================
            'admin.refunds.index' => [
                'items' => [
                    'Export' => 'refunds.export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'View Details' => 'refunds.view-details',
                            'Approve'      => 'refunds.approve',
                            'Reject'       => 'refunds.reject',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.refunds.show',
                                'label' => 'Refund Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.refunds.show' => [
                'items' => [
                    'Approve Refund'  => 'refunds.approve',
                    'Reject Refund'   => 'refunds.reject',
                    'Process Payment' => 'refunds.process-payment',
                    'Customer Info'   => 'refunds.customer-info',
                    'Order Details'   => 'refunds.order-details',
                    'Timeline'        => 'refunds.timeline',
                ],
            ],
            'admin.refunds.requests' => [
                'items' => [
                    'Approve' => 'refunds.requests-approve',
                    'Reject'  => 'refunds.requests-reject',
                ],
            ],
            'admin.refunds.approved' => [
                'items' => [
                    'Process Payment' => 'refunds.approved-process',
                    'Export'          => 'refunds.approved-export',
                ],
            ],
            'admin.refunds.rejected' => [
                'items' => [
                    'Export' => 'refunds.rejected-export',
                ],
            ],
            'admin.refunds.configuration' => [
                'items' => [
                    'Edit Policy'    => 'refunds.config-policy',
                    'Set Time Limit' => 'refunds.config-time-limit',
                ],
            ],

            // ========================
            // ===== CUSTOMERS =====
            // ========================
            'admin.customers.index' => [
                'items' => [
                    'Create Customer' => 'customers.create',
                    'Export'          => 'customers.export',
                    'Import'          => 'customers.import',
                ],
                'groups' => [
                    'Table Columns' => [
                        'items' => [
                            'Contact Info'  => 'customers.view-contact',
                            'Address'       => 'customers.view-address',
                            'Orders'        => 'customers.view-orders',
                            'Payments'      => 'customers.view-payments',
                            'Financial'     => 'customers.view-financial',
                            'Activity'      => 'customers.view-activity',
                            'Notes'         => 'customers.view-notes',
                        ],
                    ],
                    'Table Actions' => [
                        'items' => [
                            'View Details'  => 'customers.view-details',
                            'Edit'          => 'customers.edit',
                            'Delete'        => 'customers.delete',
                            'Login As'      => 'customers.login-as',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.customers.show',
                                'label' => 'Customer Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.customers.show' => [
                'items' => [
                    'Edit Customer'     => 'customers.edit',
                    'Customer Orders'   => 'customers.view-orders',
                    'Customer Payments' => 'customers.view-payments',
                    'Customer Activity' => 'customers.view-activity',
                    'Customer Notes'    => 'customers.view-notes',
                    'Login As'          => 'customers.login-as',
                    'Delete Customer'   => 'customers.delete',
                ],
            ],
            'admin.customers.edit' => [
                'items' => [
                    'Contact Info' => 'customers.edit-contact',
                    'Address'      => 'customers.edit-address',
                    'Financial'    => 'customers.edit-financial',
                ],
            ],
            'admin.customers.groups.index' => [
                'items' => [
                    'Create Group'  => 'customers.groups-create',
                    'Edit Group'    => 'customers.groups-edit',
                    'Delete Group'  => 'customers.groups-delete',
                ],
            ],
            'admin.customers.segmentation.index' => [
                'items' => [
                    'Create Segment'  => 'customers.segments-create',
                    'Edit Segment'    => 'customers.segments-edit',
                    'Delete Segment'  => 'customers.segments-delete',
                    'Apply Segment'   => 'customers.segments-apply',
                ],
            ],
            'admin.customers.loyalty.index' => [
                'items' => [
                    'Set Points Rate'    => 'customers.loyalty-set-rate',
                    'Award Points'       => 'customers.loyalty-award',
                    'Deduct Points'      => 'customers.loyalty-deduct',
                    'Export'             => 'customers.loyalty-export',
                ],
            ],
            'admin.customers.membership.index' => [
                'items' => [
                    'Create Plan'       => 'customers.membership-create',
                    'Edit Plan'         => 'customers.membership-edit',
                    'Delete Plan'       => 'customers.membership-delete',
                    'Assign Plan'       => 'customers.membership-assign',
                ],
            ],
            'admin.customers.wallet.index' => [
                'items' => [
                    'Add Balance'     => 'customers.wallet-add',
                    'Deduct Balance'  => 'customers.wallet-deduct',
                    'Transaction Log' => 'customers.wallet-log',
                    'Export'          => 'customers.wallet-export',
                ],
            ],

            // ========================
            // ===== SELLERS =====
            // ========================
            'admin.sellers.index' => [
                'items' => [
                    'Export' => 'sellers.export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'View Details' => 'sellers.view-details',
                            'Edit'         => 'sellers.edit',
                            'Login As'     => 'sellers.login-as',
                        ],
                        'children' => [
                            'View Details' => [
                                'route' => 'admin.sellers.show',
                                'label' => 'Seller Detail',
                            ],
                        ],
                    ],
                ],
            ],
            'admin.sellers.show' => [
                'items' => [
                    'Edit Seller'       => 'sellers.edit',
                    'Seller Products'   => 'sellers.view-products',
                    'Seller Revenue'    => 'sellers.view-revenue',
                    'Seller Orders'     => 'sellers.view-orders',
                    'Payout History'    => 'sellers.view-payouts',
                    'Login As'          => 'sellers.login-as',
                ],
            ],
            'admin.sellers.edit' => [
                'items' => [
                    'Update Profile' => 'sellers.edit-profile',
                    'Commission'     => 'sellers.edit-commission',
                    'Account Status' => 'sellers.edit-status',
                ],
            ],
            'admin.sellers.payouts' => [
                'items' => [
                    'Process Payout' => 'sellers.payouts-process',
                    'Export'         => 'sellers.payouts-export',
                ],
            ],
            'admin.sellers.payout-requests' => [
                'items' => [
                    'Approve Payout' => 'sellers.payouts-approve',
                    'Reject Payout'  => 'sellers.payouts-reject',
                    'Export'         => 'sellers.payouts-export',
                ],
            ],
            'admin.sellers.commission' => [
                'items' => [
                    'Set Commission' => 'sellers.commission-set',
                    'Bulk Update'    => 'sellers.commission-bulk',
                ],
            ],
            'admin.sellers.verification' => [
                'items' => [
                    'Approve Seller' => 'sellers.verification-approve',
                    'Reject Seller'  => 'sellers.verification-reject',
                    'Request Docs'   => 'sellers.verification-request-docs',
                ],
            ],

            // ========================
            // ===== AFFILIATE =====
            // ========================
            'admin.affiliate.users.index' => [
                'items' => [
                    'Export' => 'affiliate.users-export',
                ],
                'groups' => [
                    'Table Actions' => [
                        'items' => [
                            'View Details'   => 'affiliate.users-view',
                            'Approve'        => 'affiliate.users-approve',
                            'Suspend'        => 'affiliate.users-suspend',
                        ],
                    ],
                ],
            ],
            'admin.affiliate.configuration' => [
                'items' => [
                    'Set Commission' => 'affiliate.config-commission',
                    'Set Payout Min' => 'affiliate.config-min-payout',
                    'Cookie Duration'=> 'affiliate.config-cookie',
                ],
            ],
            'admin.affiliate.payouts' => [
                'items' => [
                    'Process Payout' => 'affiliate.payouts-process',
                    'Export'         => 'affiliate.payouts-export',
                ],
            ],
            'admin.affiliate.requests' => [
                'items' => [
                    'Approve' => 'affiliate.requests-approve',
                    'Reject'  => 'affiliate.requests-reject',
                ],
            ],
            'admin.affiliate.categories.index' => [
                'items' => [
                    'Create Category'  => 'affiliate.categories-create',
                    'Edit Category'    => 'affiliate.categories-edit',
                    'Delete Category'  => 'affiliate.categories-delete',
                ],
            ],
            'admin.affiliate.products.index' => [
                'items' => [
                    'Add Product'   => 'affiliate.products-add',
                    'Remove Product'=> 'affiliate.products-remove',
                    'Set Commission'=> 'affiliate.products-commission',
                ],
            ],
            'admin.affiliate.links.index' => [
                'items' => [
                    'Create Link' => 'affiliate.links-create',
                    'Delete Link' => 'affiliate.links-delete',
                ],
            ],
            'admin.affiliate.banners.index' => [
                'items' => [
                    'Upload Banner' => 'affiliate.banners-upload',
                    'Delete Banner' => 'affiliate.banners-delete',
                ],
            ],
            'admin.affiliate.reports' => [
                'items' => [
                    'Export' => 'affiliate.reports-export',
                ],
            ],

            // ========================
            // ===== MEDIA =====
            // ========================
            'admin.media.index' => [
                'items' => [
                    'Upload'            => 'media.upload',
                    'Delete'            => 'media.delete',
                    'Bulk Delete'       => 'media.bulk-delete',
                    'Move to Folder'    => 'media.move-folder',
                ],
            ],

            // ========================
            // ===== REPORTS =====
            // ========================
            'admin.reports.in-house-product-sale' => [
                'items' => [
                    'Export' => 'reports.inhouse-export',
                ],
            ],
            'admin.reports.seller-sales' => [
                'items' => [
                    'Export' => 'reports.seller-export',
                ],
            ],
            'admin.reports.inventory' => [
                'items' => [
                    'Export' => 'reports.inventory-export',
                ],
            ],
            'admin.reports.wishlist' => [
                'items' => [
                    'Export' => 'reports.wishlist-export',
                ],
            ],
            'admin.reports.user-searches' => [
                'items' => [
                    'Export' => 'reports.searches-export',
                ],
            ],
            'admin.reports.commission-history' => [
                'items' => [
                    'Export' => 'reports.commission-export',
                ],
            ],
            'admin.reports.wallet-history' => [
                'items' => [
                    'Export' => 'reports.wallet-export',
                ],
            ],
            'admin.jakat.index' => [
                'items' => [
                    'Export'       => 'reports.jakat-export',
                    'Reset'        => 'reports.jakat-reset',
                ],
            ],

            // ========================
            // ===== MARKETING =====
            // ========================
            'admin.marketing.flash-deals.index' => [
                'items' => [
                    'Create Deal'  => 'marketing.deals-create',
                    'Edit Deal'    => 'marketing.deals-edit',
                    'Delete Deal'  => 'marketing.deals-delete',
                ],
            ],
            'admin.marketing.newsletters.index' => [
                'items' => [
                    'Create Newsletter' => 'marketing.newsletters-create',
                    'Send Now'          => 'marketing.newsletters-send',
                    'Schedule'          => 'marketing.newsletters-schedule',
                    'Delete'            => 'marketing.newsletters-delete',
                ],
            ],
            'admin.marketing.bulk-sms.index' => [
                'items' => [
                    'Send SMS'  => 'marketing.sms-send',
                    'Template'  => 'marketing.sms-template',
                ],
            ],
            'admin.marketing.subscribers.index' => [
                'items' => [
                    'Export'       => 'marketing.subscribers-export',
                    'Import'       => 'marketing.subscribers-import',
                    'Delete'       => 'marketing.subscribers-delete',
                ],
            ],
            'admin.coupons.index' => [
                'items' => [
                    'Create Coupon'  => 'marketing.coupons-create',
                    'Edit Coupon'    => 'marketing.coupons-edit',
                    'Delete Coupon'  => 'marketing.coupons-delete',
                    'Export'         => 'marketing.coupons-export',
                ],
            ],
            'admin.marketing.abandoned-cart.index' => [
                'items' => [
                    'Send Reminder' => 'marketing.abandoned-send',
                    'Configure'     => 'marketing.abandoned-configure',
                ],
            ],
            'admin.marketing.gift-cards.index' => [
                'items' => [
                    'Create Card'  => 'marketing.giftcards-create',
                    'Deactivate'   => 'marketing.giftcards-deactivate',
                    'Export'       => 'marketing.giftcards-export',
                ],
            ],
            'admin.marketing.push-notifications.index' => [
                'items' => [
                    'Send Push'  => 'marketing.push-send',
                    'Template'   => 'marketing.push-template',
                ],
            ],
            'admin.marketing.price-rules.index' => [
                'items' => [
                    'Create Rule'  => 'marketing.pricerules-create',
                    'Edit Rule'    => 'marketing.pricerules-edit',
                    'Delete Rule'  => 'marketing.pricerules-delete',
                    'Enable/Disable' => 'marketing.pricerules-toggle',
                ],
            ],

            // ========================
            // ===== SUPPORT =====
            // ========================
            'admin.support.tickets.index' => [
                'items' => [
                    'Create Ticket' => 'support.tickets-create',
                    'Assign'        => 'support.tickets-assign',
                    'Close'         => 'support.tickets-close',
                    'Export'        => 'support.tickets-export',
                ],
            ],
            'admin.chat.index' => [
                'items' => [
                    'View Chat'    => 'support.chat-view',
                    'Send Message' => 'support.chat-send',
                    'Close Chat'   => 'support.chat-close',
                ],
            ],
            'admin.chat.ai-settings.index' => [
                'items' => [
                    'Configure AI'  => 'support.chat-ai-configure',
                    'Test AI'       => 'support.chat-ai-test',
                ],
            ],
            'admin.chat.widget-settings.index' => [
                'items' => [
                    'Customize Widget' => 'support.chat-widget-customize',
                    'Position'         => 'support.chat-widget-position',
                ],
            ],
            'admin.chat.predefined.index' => [
                'items' => [
                    'Create Reply'  => 'support.chat-replies-create',
                    'Edit Reply'    => 'support.chat-replies-edit',
                    'Delete Reply'  => 'support.chat-replies-delete',
                ],
            ],
            'admin.settings.whatsapp' => [
                'items' => [
                    'Configure'     => 'support.whatsapp-configure',
                    'Test'          => 'support.whatsapp-test',
                ],
            ],
            'admin.support.product-queries.index' => [
                'items' => [
                    'Reply'   => 'support.product-queries-reply',
                    'Resolve' => 'support.product-queries-resolve',
                    'Delete'  => 'support.product-queries-delete',
                ],
            ],

            // ========================
            // ===== OTP =====
            // ========================
            'admin.otp.configuration' => [
                'items' => [
                    'Set Gateway'   => 'otp.config-gateway',
                    'Set Expiry'    => 'otp.config-expiry',
                    'Set Length'    => 'otp.config-length',
                ],
            ],
            'admin.otp.sms-templates' => [
                'items' => [
                    'Create Template'  => 'otp.templates-create',
                    'Edit Template'    => 'otp.templates-edit',
                    'Delete Template'  => 'otp.templates-delete',
                ],
            ],
            'admin.otp.credentials' => [
                'items' => [
                    'Set API Key'    => 'otp.credentials-api-key',
                    'Set Sender ID'  => 'otp.credentials-sender',
                    'Test Gateway'   => 'otp.credentials-test',
                ],
            ],

            // ========================
            // ===== CONTENT =====
            // ========================
            'admin.pages.index' => [
                'items' => [
                    'Create Page'  => 'content.pages-create',
                    'Edit Page'    => 'content.pages-edit',
                    'Delete Page'  => 'content.pages-delete',
                ],
            ],
            'admin.blogs.index' => [
                'items' => [
                    'Create Post'  => 'content.blogs-create',
                    'Edit Post'    => 'content.blogs-edit',
                    'Delete Post'  => 'content.blogs-delete',
                    'Export'       => 'content.blogs-export',
                ],
            ],
            'admin.blog-categories.index' => [
                'items' => [
                    'Create Category'  => 'content.blog-categories-create',
                    'Edit Category'    => 'content.blog-categories-edit',
                    'Delete Category'  => 'content.blog-categories-delete',
                ],
            ],
            'admin.blog-tags.index' => [
                'items' => [
                    'Create Tag'  => 'content.blog-tags-create',
                    'Edit Tag'    => 'content.blog-tags-edit',
                    'Delete Tag'  => 'content.blog-tags-delete',
                ],
            ],
            'admin.form-builder.index' => [
                'items' => [
                    'Create Form'  => 'content.forms-create',
                    'Edit Form'    => 'content.forms-edit',
                    'Delete Form'  => 'content.forms-delete',
                ],
            ],
            'admin.faqs.index' => [
                'items' => [
                    'Create FAQ'  => 'content.faqs-create',
                    'Edit FAQ'    => 'content.faqs-edit',
                    'Delete FAQ'  => 'content.faqs-delete',
                ],
            ],
            'admin.content.widgets.index' => [
                'items' => [
                    'Add Widget'    => 'content.widgets-add',
                    'Edit Widget'   => 'content.widgets-edit',
                    'Delete Widget' => 'content.widgets-delete',
                ],
            ],

            // ========================
            // ===== APPEARANCE =====
            // ========================
            'admin.themes.index' => [
                'items' => [
                    'Activate Theme'  => 'appearance.themes-activate',
                    'Install Theme'   => 'appearance.themes-install',
                    'Delete Theme'    => 'appearance.themes-delete',
                ],
            ],
            'admin.menus.index' => [
                'items' => [
                    'Create Menu'  => 'appearance.menus-create',
                    'Edit Menu'    => 'appearance.menus-edit',
                    'Delete Menu'  => 'appearance.menus-delete',
                ],
            ],
            'admin.sliders.index' => [
                'items' => [
                    'Add Slide'    => 'appearance.sliders-add',
                    'Edit Slide'   => 'appearance.sliders-edit',
                    'Delete Slide' => 'appearance.sliders-delete',
                ],
            ],
            'admin.banners.index' => [
                'items' => [
                    'Add Banner'    => 'appearance.banners-add',
                    'Edit Banner'   => 'appearance.banners-edit',
                    'Delete Banner' => 'appearance.banners-delete',
                ],
            ],
            'admin.hero.index' => [
                'items' => [
                    'Edit Hero'    => 'appearance.hero-edit',
                    'Reset Hero'   => 'appearance.hero-reset',
                ],
            ],
            'admin.homepage.index' => [
                'items' => [
                    'Edit Sections'    => 'appearance.homepage-edit-sections',
                    'Reorder Sections' => 'appearance.homepage-reorder',
                ],
            ],

            // ========================
            // ===== SETTINGS =====
            // ========================
            'admin.settings.general' => [
                'items' => [
                    'Edit' => 'settings.general-edit',
                ],
            ],
            'admin.settings.languages' => [
                'items' => [
                    'Add Language'     => 'settings.languages-add',
                    'Edit Language'    => 'settings.languages-edit',
                    'Delete Language'  => 'settings.languages-delete',
                    'Set Default'      => 'settings.languages-default',
                ],
            ],
            'admin.settings.currency' => [
                'items' => [
                    'Add Currency'      => 'settings.currency-add',
                    'Edit Currency'     => 'settings.currency-edit',
                    'Delete Currency'   => 'settings.currency-delete',
                    'Set Default'       => 'settings.currency-default',
                ],
            ],
            'admin.settings.vat-tax' => [
                'items' => [
                    'Add Tax Rate'    => 'settings.tax-add',
                    'Edit Tax Rate'   => 'settings.tax-edit',
                    'Delete Tax Rate' => 'settings.tax-delete',
                ],
            ],
            'admin.settings.email' => [
                'items' => [
                    'Edit SMTP'     => 'settings.email-smtp',
                    'Test Email'    => 'settings.email-test',
                ],
            ],
            'admin.payment-gateways.index' => [
                'items' => [
                    'Activate Gateway'  => 'settings.payment-activate',
                    'Configure'         => 'settings.payment-configure',
                    'Deactivate'        => 'settings.payment-deactivate',
                    'Set Default'       => 'settings.payment-default',
                ],
            ],
            'admin.settings.order-configuration' => [
                'items' => [
                    'Edit Config' => 'settings.order-config-edit',
                ],
            ],
            'admin.settings.file-system' => [
                'items' => [
                    'Edit Config' => 'settings.filesystem-edit',
                    'Clear Cache' => 'settings.filesystem-clear-cache',
                ],
            ],
            'admin.settings.social-login' => [
                'items' => [
                    'Activate Provider' => 'settings.social-activate',
                    'Configure'         => 'settings.social-configure',
                ],
            ],
            'admin.settings.shipping' => [
                'items' => [
                    'Edit Config' => 'settings.shipping-edit',
                ],
            ],
            'admin.seo.index' => [
                'items' => [
                    'Edit Meta'    => 'settings.seo-meta',
                    'Sitemap'      => 'settings.seo-sitemap',
                    'Robots.txt'   => 'settings.seo-robots',
                ],
            ],
            'admin.settings.footer' => [
                'items' => [
                    'Edit Content' => 'settings.footer-edit',
                ],
            ],
            'admin.settings.email-templates.index' => [
                'items' => [
                    'Edit Template'  => 'settings.email-templates-edit',
                    'Reset Default'  => 'settings.email-templates-reset',
                ],
            ],
            'admin.settings.notifications.index' => [
                'items' => [
                    'Edit Config' => 'settings.notifications-edit',
                ],
            ],
            'admin.api-keys.index' => [
                'items' => [
                    'Create Key'  => 'settings.api-create',
                    'Revoke Key'  => 'settings.api-revoke',
                ],
            ],
            'admin.backup' => [
                'items' => [
                    'Create Backup'    => 'settings.backup-create',
                    'Download Backup'  => 'settings.backup-download',
                    'Restore Backup'   => 'settings.backup-restore',
                    'Delete Backup'    => 'settings.backup-delete',
                ],
            ],

            // ========================
            // ===== LOCATIONS =====
            // ========================
            'admin.locations.countries.index' => [
                'items' => [
                    'Add Country'    => 'locations.countries-add',
                    'Edit Country'   => 'locations.countries-edit',
                    'Delete Country' => 'locations.countries-delete',
                ],
            ],
            'admin.locations.states.index' => [
                'items' => [
                    'Add State'    => 'locations.states-add',
                    'Edit State'   => 'locations.states-edit',
                    'Delete State' => 'locations.states-delete',
                ],
            ],
            'admin.locations.cities.index' => [
                'items' => [
                    'Add City'    => 'locations.cities-add',
                    'Edit City'   => 'locations.cities-edit',
                    'Delete City' => 'locations.cities-delete',
                    'Import'      => 'locations.cities-import',
                ],
            ],
            'admin.locations.areas.index' => [
                'items' => [
                    'Add Area'    => 'locations.areas-add',
                    'Edit Area'   => 'locations.areas-edit',
                    'Delete Area' => 'locations.areas-delete',
                ],
            ],

            // ========================
            // ===== WAREHOUSE =====
            // ========================
            'admin.warehouses.index' => [
                'items' => [
                    'Create Warehouse'  => 'warehouse.create',
                    'Edit Warehouse'    => 'warehouse.edit',
                    'Delete Warehouse'  => 'warehouse.delete',
                ],
            ],
            'admin.warehouses.create' => [
                'items' => [
                    'Save' => 'warehouse.create',
                ],
            ],

            // ========================
            // ===== STAFFS =====
            // ========================
            'admin.staffs.index' => [
                'items' => [
                    'Add Staff'      => 'staffs.add',
                    'Edit Staff'     => 'staffs.edit',
                    'Delete Staff'   => 'staffs.delete',
                    'Export'         => 'staffs.export',
                ],
            ],
            'admin.staffs.warehouse' => [
                'items' => [
                    'Assign Warehouse' => 'staffs.warehouse-assign',
                    'Remove Staff'     => 'staffs.warehouse-remove',
                ],
            ],
            'admin.permissions.index' => [
                'items' => [
                    'Create Key'       => 'staffs.permissions-create',
                    'Delete Key'       => 'staffs.permissions-delete',
                ],
            ],

            // ========================
            // ===== SYSTEM =====
            // ========================
            'admin.system.update' => [
                'items' => [
                    'Check Update'   => 'system.update-check',
                    'Apply Update'   => 'system.update-apply',
                ],
            ],
            'admin.system.server-status' => [
                'items' => [
                    'View Status' => 'system.status-view',
                ],
            ],
            'admin.system.activity-logs.index' => [
                'items' => [
                    'View Logs'   => 'system.logs-view',
                    'Clear Logs'  => 'system.logs-clear',
                    'Export'      => 'system.logs-export',
                ],
            ],
            'admin.system.data-export.index' => [
                'items' => [
                    'Export Data'  => 'system.data-export',
                    'Import Data'  => 'system.data-import',
                ],
            ],

            // ========================
            // ===== POS =====
            // ========================
            'admin.pos.terminal' => [
                'items' => [
                    'Create Order'   => 'pos.create-order',
                    'View Orders'    => 'pos.view-orders',
                    'Hold Order'     => 'pos.hold-order',
                    'Print Receipt'  => 'pos.print-receipt',
                ],
            ],
            'admin.pos.cash-register' => [
                'items' => [
                    'Open Register'  => 'pos.register-open',
                    'Close Register' => 'pos.register-close',
                    'Cash In/Out'    => 'pos.register-cash',
                    'View History'   => 'pos.register-history',
                ],
            ],
            'admin.pos.reports' => [
                'items' => [
                    'Sales Report'   => 'pos.reports-sales',
                    'Export'         => 'pos.reports-export',
                ],
            ],

            // ========================
            // ===== ADDON =====
            // ========================
            'admin.addons.index' => [
                'items' => [
                    'Install'    => 'addon.install',
                    'Uninstall'  => 'addon.uninstall',
                    'Activate'   => 'addon.activate',
                    'Deactivate' => 'addon.deactivate',
                ],
            ],

            // ========================
            // ===== MULTI-STORE =====
            // ========================
            'admin.multi-store.index' => [
                'items' => [
                    'Create Store'  => 'multistore.create',
                    'Edit Store'    => 'multistore.edit',
                    'Delete Store'  => 'multistore.delete',
                ],
            ],
            'admin.multi-store.create' => [
                'items' => [
                    'Save' => 'multistore.create',
                ],
            ],

            // ========================
            // ===== ANALYTICS =====
            // ========================
            'admin.analytics.index' => [
                'items' => [],
            ],

            // ========================
            // ===== DASHBOARD =====
            // ========================
            'admin.dashboard' => [
                'items' => [],
            ],
        ];
    }

    /**
     * Map submenu parent routes to their child pages.
     * Returns: route → [ ['item_label' => ..., 'route' => ..., 'label' => ...] ]
     */
    public static function childPages(): array
    {
        $children = [];
        foreach (self::pageComponents() as $route => $page) {
            if (isset($page['groups'])) {
                foreach ($page['groups'] as $group) {
                    if (isset($group['children'])) {
                        foreach ($group['children'] as $itemLabel => $childInfo) {
                            $children[$route][] = [
                                'attached_to' => $itemLabel,
                                'route'       => $childInfo['route'],
                                'label'       => $childInfo['label'],
                            ];
                        }
                    }
                }
            }
        }
        return $children;
    }

    /**
     * Get all page action permission names flat list.
     */
    public static function allPageActionNames(): array
    {
        $names = [];
        foreach (self::pageActions() as $route => $actions) {
            foreach ($actions as $action) {
                $names[] = $action;
            }
        }
        return $names;
    }

    /**
     * Get page actions for a specific route.
     */
    public static function pageActionsForRoute(string $routeName): array
    {
        return self::pageActions()[$routeName] ?? [];
    }

    /**
     * Get list of module keys that are hidden from sidebar.
     */
    public static function hiddenModules(): array
    {
        if (self::$hiddenModules !== null) {
            return self::$hiddenModules;
        }

        $raw = Setting::get('sidebar_hidden_modules', '[]');
        $decoded = json_decode($raw, true);
        return self::$hiddenModules = is_array($decoded) ? $decoded : [];
    }

    /**
     * Toggle a module's sidebar visibility.
     * Returns the new state (true = visible, false = hidden).
     */
    public static function toggleModuleVisibility(string $moduleKey): bool
    {
        $hidden = self::hiddenModules();
        if (in_array($moduleKey, $hidden)) {
            $hidden = array_values(array_filter($hidden, fn($m) => $m !== $moduleKey));
            Setting::set('sidebar_hidden_modules', json_encode($hidden));
            self::$hiddenModules = $hidden;
            return true;
        } else {
            $hidden[] = $moduleKey;
            Setting::set('sidebar_hidden_modules', json_encode($hidden));
            self::$hiddenModules = $hidden;
            return false;
        }
    }

    /**
     * Check if a module is visible in the sidebar.
     */
    public static function isModuleVisible(string $module): bool
    {
        return !in_array($module, self::hiddenModules());
    }

    /**
     * Get all permissions grouped by module prefix, with computed metadata.
     * Returns: ['products' => ['key' => 'products', 'label' => 'Products', 'actions' => ['view','create',...]]]
     */
    public static function modules(): array
    {
        if (self::$cachedModules !== null) {
            return self::$cachedModules;
        }

        $moduleActions = self::moduleActions();
        $modules = [];

        foreach ($moduleActions as $moduleKey => $actions) {
            $modules[$moduleKey] = [
                'key'     => $moduleKey,
                'label'   => self::humanizeModule($moduleKey),
                'icon'    => self::iconForModule($moduleKey),
                'actions' => $actions,
            ];
        }

        $priorityOrder = ['dashboard', 'analytics', 'products', 'inventory', 'orders', 'delivery',
            'refund', 'customers', 'sellers', 'affiliate', 'media', 'reports', 'marketing',
            'support', 'otp', 'content', 'appearance', 'settings', 'locations', 'warehouse',
            'staffs', 'system', 'pos', 'addon', 'multistore'];

        $sorted = [];
        foreach ($priorityOrder as $key) {
            if (isset($modules[$key])) {
                $sorted[$key] = $modules[$key];
                unset($modules[$key]);
            }
        }
        foreach ($modules as $key => $mod) {
            $sorted[$key] = $mod;
        }

        return self::$cachedModules = $sorted;
    }

    /**
     * Get all legacy module-level keys (just the prefix names).
     */
    public static function legacyKeys(): array
    {
        return array_keys(static::modules());
    }

    /**
     * Get all granular permission names from the database.
     */
    public static function allGranular(): Collection
    {
        return self::allPermissions()->pluck('name');
    }

    /**
     * Get all permissions as a collection with module/action parsed.
     */
    private static function allPermissions(): Collection
    {
        if (self::$cachedPermissions !== null) {
            return self::$cachedPermissions;
        }

        return self::$cachedPermissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->map(function ($perm) {
                $parts = explode('.', $perm->name);
                $perm->module = $parts[0] ?? $perm->name;
                $perm->action = $parts[1] ?? 'view';
                return $perm;
            });
    }

    /**
     * Map a permission key to a default redirect route.
     */
    public static function permissionToRoute(string $permission): ?string
    {
        $modules = self::modules();
        if (!isset($modules[$permission])) {
            return null;
        }

        // Route naming convention: admin.{module}.index
        // Fall back to known mappings for special cases
        $specialRoutes = [
            'dashboard'  => 'admin.dashboard',
            'products'   => 'admin.products.in-house',
            'marketing'  => 'admin.marketing.flash-deals.index',
            'content'    => 'admin.blogs.index',
            'appearance' => 'admin.appearance.index',
            'system'     => 'admin.system.update',
            'otp'        => 'admin.otp.configuration',
            'locations'  => 'admin.locations.cities.index',
            'warehouse'  => 'admin.warehouses.index',
            'staffs'     => 'admin.staffs.index',
            'multistore' => 'admin.multi-store.index',
            'addon'      => 'admin.addons.index',
            'media'      => 'admin.media.index',
        ];

        if (isset($specialRoutes[$permission])) {
            return $specialRoutes[$permission];
        }

        $routeName = 'admin.' . $permission . '.index';
        // We can't validate routes at runtime easily, just return the convention
        return $routeName;
    }

    /**
     * Check if a permission key is a module-level key (no dot) vs granular (has dot).
     */
    public static function isModuleKey(string $permission): bool
    {
        return !str_contains($permission, '.');
    }

    /**
     * Get the module prefix from a permission name.
     */
    public static function moduleFromPermission(string $permission): string
    {
        return explode('.', $permission)[0];
    }

    /**
     * Convert a module key to a human-readable label.
     */
    private static function humanizeModule(string $key): string
    {
        $labels = [
            'multistore' => 'Multi-Store',
            'otp'        => 'OTP Management',
            'pos'        => 'POS Management',
            'addon'      => 'Addon Manager',
            'staffs'     => 'Staff Management',
            'orders'     => 'Sales Management',
        ];

        if (isset($labels[$key])) {
            return $labels[$key];
        }

        return ucwords(str_replace('-', ' ', $key)) . ' Management';
    }

    /**
     * Get a Bootstrap icon class for a module.
     */
    private static function iconForModule(string $key): string
    {
        $icons = [
            'dashboard'  => 'bi-speedometer2',
            'analytics'  => 'bi-graph-up-arrow',
            'products'   => 'bi-box',
            'inventory'  => 'bi-boxes',
            'orders'     => 'bi-cart-check',
            'sales'      => 'bi-cart-check',
            'delivery'   => 'bi-truck',
            'refund'     => 'bi-arrow-return-left',
            'customers'  => 'bi-people',
            'sellers'    => 'bi-shop-window',
            'affiliate'  => 'bi-link-45deg',
            'media'      => 'bi-images',
            'reports'    => 'bi-graph-up',
            'marketing'  => 'bi-megaphone',
            'support'    => 'bi-headset',
            'otp'        => 'bi-shield-lock',
            'content'    => 'bi-file-earmark-text',
            'appearance' => 'bi-palette2',
            'settings'   => 'bi-gear-fill',
            'locations'  => 'bi-geo-alt',
            'warehouse'  => 'bi-building',
            'staffs'     => 'bi-person-badge',
            'system'     => 'bi-cpu',
            'pos'        => 'bi-calculator',
            'multistore' => 'bi-shop',
            'addon'      => 'bi-puzzle',
        ];

        return $icons[$key] ?? 'bi-gear';
    }

    /**
     * Flush cached permissions (call after creating/deleting permissions).
     */
    public static function flushCache(): void
    {
        self::$cachedPermissions = null;
        self::$cachedModules = null;
        self::$hiddenSubmenus = null;
        self::$hiddenModules = null;
    }

    /**
     * Get submenu key from route name (e.g., 'admin.products.index' => 'admin.products.index').
     */
    public static function submenuKeyFromRoute(string $routeName): ?string
    {
        foreach (self::submenus() as $module => $items) {
            if (isset($items[$routeName])) {
                return $routeName;
            }
        }
        return null;
    }
}
