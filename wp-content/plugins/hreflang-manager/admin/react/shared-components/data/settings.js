const {__} = wp.i18n;

const settings = {
    pluginName: 'Hreflang Manager',
    footerLinks: [
        {
            linkName: __('Hreflang Manager version 1.33', 'hreflang-manager'),
            linkUrl: 'https://daext.com/hreflang-manager/',
        },
        {
            linkName: __('Knowledge Base', 'hreflang-manager'),
            linkUrl: 'https://daext.com/kb-category/hreflang-manager/',
        },
        {
            linkName: __('Support', 'hreflang-manager'),
            linkUrl: 'https://daext.com/support/',
        },
        {
            linkName: __('Change Log', 'hreflang-manager'),
            linkUrl: 'https://codecanyon.net/item/hreflang-manager/6543147#item-description__updates',
        },
        {
            linkName: __('Premium Plugins', 'hreflang-manager'),
            linkUrl: 'https://daext.com/products/',
        }
    ],
    pages: window.DAEXTDAHM_PARAMETERS.options_configuration_pages,
};

export default settings;