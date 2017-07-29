<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\DataFixtures;
	
	
	use Faker\Provider\Base;
	use Faker\Generator;
	
	use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
	use libphonenumber\PhoneNumberToTimeZonesMapper;
	use libphonenumber\PhoneNumberUtil;
	use libphonenumber\PhoneNumberFormat;
	
	
	class Provider extends Base {
		
		/** @var  PhoneNumberUtil */
		protected $phoneNumberUtil;
		
		/** @var  PhoneNumberOfflineGeocoder */
		protected $phoneNumberOfflineGeocoder;
		
		/** @var  PhoneNumberToTimeZonesMapper */
		protected $phoneNumberToTimezoneMapper;
		
		
		public function __construct(Generator $generator, PhoneNumberUtil $phoneNumberUtil, PhoneNumberOfflineGeocoder $phoneNumberOfflineGeocoder, PhoneNumberToTimeZonesMapper $phoneNumberToTimezoneMapper) {
			parent::__construct($generator);
			$this->phoneNumberUtil = $phoneNumberUtil;
			$this->phoneNumberOfflineGeocoder = $phoneNumberOfflineGeocoder;
			$this->phoneNumberToTimezoneMapper = $phoneNumberToTimezoneMapper;
		}
		
		
		public function phoneE1614()
		{
			return $this->phoneNumberUtil->parse('030 9940 400 '.rand(0,100),'DE');
		}
		
	}
