<?php

namespace WeDevs\ERP;

/**
 * ERP i18n class
 */
class ERP_i18n {

    /**
     * Initialize
     */
    public function __construct() {
        add_filter( 'erp_localized_data', [ $this, 'add_i18n_data' ] );
    }

    public function add_i18n_data( $localized_data ) {
        $localized_data['locale_data'] = $this->get_jed_locale_data( 'erp' );

        return $localized_data;
    }

    /**
     * Returns Jed-formatted localization data.
     *
     * @since 0.1.0
     *
     * @param string $domain translation domain
     *
     * @return array
     */
    public function get_jed_locale_data( $domain ) {
        $translations = get_translations_for_domain( $domain );

        $locale = [
            '' => [
                'domain' => $domain,
                'lang'   => is_admin() ? get_user_locale() : get_locale(),
            ],
        ];

        if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
            $locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
        }

        foreach ( $translations->entries as $msgid => $entry ) {
            $locale[ $msgid ] = $entry->translations;
        }

        return $locale;
    }
}
