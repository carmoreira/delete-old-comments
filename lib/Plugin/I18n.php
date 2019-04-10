<?php
namespace Arte\WP\Plugin\DeleteComments\Plugin;

/**
 * Internationalization
 */
class I18n {

	/**
	 * Unique identifier for retrieving translated strings
	 *
	 * @var string
	 */
	private $domain;

	/**
	 * Load translations.
	 *
	 * @param string $plugin_dirname Plugin directory name, relative to WP_PLUGIN_DIR.
	 */
	public function load_plugin_textdomain( $plugin_dirname ) {

		load_plugin_textdomain(
			$this->domain,
			false,
			$plugin_dirname . '/languages/'
		);

	}

	/**
	 * Setup the domain
	 *
	 * @param string $domain Unique identifier for retrieving translated strings
	 */
	public function set_domain( $domain ) {
		$this->domain = $domain;
	}
}
