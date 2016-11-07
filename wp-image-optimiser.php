<?php

namespace WP_Image_Optimiser;

/*
Plugin Name: WP Image Optimiser
Plugin URI: https://github.com/jonmcpartland/wp-image-optimiser/
Description: Optimise images upon upload. Requires Imagick.
Version: 0.1.0
Author: Jon McPartland
Author URI: https://jon.mcpart.land
Textdomain: wp-image-optimiser
*/

require_once __DIR__ . '/includes/Optimiser.php';

use ImagickException;

new class {

	protected $optimiser;

	protected static $allowedMimes = [
		'image/jpeg',
		'image/gif',
		'image/png',
		'image/bmp',
		'image/tiff',
		'image/x-icon',
	];

	public function __construct() {
		\add_filter( 'wp_handle_upload_prefilter', [ $this, 'upload' ] );

		$this->load();
	}

	public function upload( $file ) {
		// bail if not an image
		if ( ! in_array( $file['type'], static::$allowedMimes ) ) {
			return $file;
		}

		try {
			$this->optimiser->load( $file['tmp_name'], $file['name'] );
		} catch ( ImagickException $e ) {
			unset( $file['tmp_name'] );

			return $file;
		}

		$this->optimiser->optimise()->save();

		return $file;
	}

	protected function load() {
		$settings = \apply_filters( 'wp_image_optimiser_settings', Optimiser::defaults() );
		$this->optimiser = new Optimiser( $settings );
	}

};
