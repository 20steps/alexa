<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\DTO;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
use Mcfedr\AwsPushBundle\Message\Message as PushMessage;

/**
 * Model for messages that can be sent via mail and/or mobile push and/or desktop push
 */
class Message  {

    private $text;
    private $custom;

    /** @var  boolean $highPriority */
    private $highPriority;

    private $channels;

    public function __construct($text,$custom=array(),$channels='auto',$highPriority=false) {
        $this->setText($text);
        $this->setCustom($custom);
        $this->setChannels($channels);
        $this->setHighPriority($highPriority);
    }

    /** @return PushMessage */
    public function getPushMessage(User $toUser) {
        $pushMessage = new PushMessage();
        $pushMessage->setText($this->getText());
        $pushMessage->setCustom($this->getCustom());
        return $pushMessage;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @param mixed $custom
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
        return $this;
    }

    public function getAction() {
        $custom = $this->getCustom();
        if ($custom && is_array($custom)) {
            if (array_key_exists('action',$custom)) {
                return $custom['action'];
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function isHighPriority()
    {
        return $this->highPriority;
    }

    /**
     * @param mixed $highPriority
     */
    public function setHighPriority($highPriority)
    {
        $this->highPriority = $highPriority;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChannels()
    {
        return $this->channels;
    }

    private function getAllChannels() {
        return array('mail','mobile_push','wamp');
    }

    public function hasChannel($channel) {
        return in_array($channel,$this->getChannels());
    }

    /**
     * @param mixed $channels
     */
    public function setChannels($channels)
    {
        // set and check channels
        $allChannels = $this->getAllChannels();
        if ($channels=='auto') {
            $this->channels = $allChannels;
            return $this;
        }
        if (is_array($channels)) {
        	foreach ($channels as $channel) {
        		$channel = trim($channel);
		        if (!in_array($channel,$allChannels)) {
			        throw new \Exception('Channel not allowed ['.$channels.']');
		        }
	        }
            $this->channels=$channels;
            return $this;
        }
        $disallowedChannels = array_diff($channels,$allChannels);
        if (count($disallowedChannels)>0) {
            throw new \Exception('Channels not allowed ['.implode(',',$disallowedChannels).']');
        }
        $this->channels = $channels;
        return $this;
    }
}