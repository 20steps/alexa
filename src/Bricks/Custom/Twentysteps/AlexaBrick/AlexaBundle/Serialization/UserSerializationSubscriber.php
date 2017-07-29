<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Serialization;

use JMS\Serializer\EventDispatcher\ObjectEvent;

use JMS\DiExtraBundle\Annotation as DI;

use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumberToTimeZonesMapper;
use libphonenumber\PhoneNumberUtil;

use Doctrine\ORM\EntityManager;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Interfaces\CoreCDNModule;

use Bricks\AbstractCustomBundle\Serialization\AbstractSerializationSubscriber;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;

/**
 * Add data after serialization
 *
 * @DI\Service("bricks.custom.twentysteps_alexa.serialization_subscriber.user")
 * @DI\Tag("jms_serializer.event_subscriber")
 */
class UserSerializationSubscriber extends AbstractSerializationSubscriber
{
    /** @var CoreCDNModule $cdnModule */
    private $cdnModule;

    private $host;
    private $protocol;
    private $em;

    /** @var  PhoneNumberUtil */
	protected $phoneNumberUtil;
	
	/** @var  PhoneNumberOfflineGeocoder */
	protected $phoneNumberOfflineGeocoder;
	
	/** @var  PhoneNumberToTimeZonesMapper */
	protected $phoneNumberToTimezoneMapper;

	/**
	 * @DI\InjectParams({
	 *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
	 *     "userModule" = @DI\Inject("bricks.custom.twentysteps_alexa.module.user"),
	 *     "deviceModule" = @DI\Inject("bricks.basic.mobile.module.device"),
	 *     "cdnModule" = @DI\Inject("core_cdn"),
	 *     "host" = @DI\Inject("%bricks_custom_twentysteps_alexa_host%"),
	 *     "protocol" = @DI\Inject("%bricks_custom_twentysteps_alexa_protocol%"),
	 *     "phoneNumberUtil" = @DI\Inject("libphonenumber.phone_number_util"),
	 *     "phoneNumberOfflineGeocoder" = @DI\Inject("libphonenumber.phone_number_offline_geocoder"),
	 *     "phoneNumberToTimezoneMapper" = @DI\Inject("libphonenumber.phone_number_to_time_zones_mapper"),
	 *     "logger" = @DI\Inject("logger")
	 * })
	 */
	public function __construct(EntityManager $em, $userModule, $deviceModule, $cdnModule, $host, $protocol, PhoneNumberUtil $phoneNumberUtil, PhoneNumberOfflineGeocoder $phoneNumberOfflineGeocoder, PhoneNumberToTimeZonesMapper $phoneNumberToTimezoneMapper, $logger) {
        parent::__construct($userModule, $deviceModule, $logger);
        $this->em = $em;
        $this->cdnModule = $cdnModule;
        $this->host = $host;
        $this->protocol = $protocol;
	    $this->phoneNumberUtil = $phoneNumberUtil;
	    $this->phoneNumberOfflineGeocoder = $phoneNumberOfflineGeocoder;
	    $this->phoneNumberToTimezoneMapper = $phoneNumberToTimezoneMapper;
    }

    /**
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize')
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        parent::onPostSerialize($event);

        $object=$event->getObject();

        if ($object instanceof User) {
            /** @var User $user */
            $user = $object;
        }
    }
}