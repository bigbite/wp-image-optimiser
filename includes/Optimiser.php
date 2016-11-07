<?php

namespace WP_Image_Optimiser;

use Imagick;
use ImagickException;

class Optimiser {

	protected $imagick;

	protected $image;
	protected $dir;
	protected $name;

	protected static $defaults = [
		'imageType'   => 'jpg',
		'maxWidth'    => 2000,
		'maxHeight'   => 2000,
		'quality'     => 80,
		'bestFit'     => true,
		'blur'        => 1.0,
		'filter'      => Imagick::FILTER_UNDEFINED,
		'compression' => Imagick::COMPRESSION_JPEG,
		'resolution'  => 72,
	];

	protected $settings = [];

	public function __construct( array $settings = [] ) {
		$settings = \wp_parse_args( $settings, static::$defaults );

		$this->settings = $settings;
		$this->imagick  = new Imagick;
	}

	public static function defaults() {
		return static::$defaults;
	}

	public function load( $image, $name ) {
		$this->imagick->clear();

		$this->image = $image;
		$this->name  = $name;
		$this->dir   = dirname( $image );

		$this->setResolution()->read();

		return $this;
	}

	public function optimise() {
		return $this->resizeIfOversize()
			->compress()
			->prepare();
	}

	public function save() {
		$this->imagick->writeImage( $this->image );
		$this->imagick->clear();

		return $this;
	}

	protected function setResolution() {
		$res = $this->settings['resolution'];

		$this->imagick->setResolution( $res, $res );

		return $this;
	}

	protected function read() {
		$this->imagick->readImage( $this->image );

		if ( ! $this->imagick->valid() ) {
			throw new ImagickException( sprintf( 'File %s is not a valid image.', $this->name ) );
		}

		return $this;
	}

	protected function resizeIfOversize() {
		if ( ! $this->isOversize() ) {
			return $this;
		}

		return $this->resize();
	}

	protected function compress() {
		$this->imagick->setImageCompression( $this->settings['compression'] );
		$this->imagick->setImageCompressionQuality( $this->settings['quality'] );

		return $this;
	}

	protected function prepare() {
		$this->imagick->setImageFormat( $this->settings['imageType'] );
		$this->imagick->stripImage();

		return $this;
	}

	protected function isOversize() {
		$tooWide = $this->settings['maxWidth']  < $this->imagick->getImageWidth();
		$tooTall = $this->settings['maxHeight'] < $this->imagick->getImageHeight();

		return $tooWide || $tooTall;
	}

	protected function resize() {
		$this->imagick->resizeImage(
			$this->settings['maxWidth'],
			$this->settings['maxHeight'],
			$this->settings['filter'],
			$this->settings['blur'],
			$this->settings['bestFit']
		);

		return $this;
	}

}
