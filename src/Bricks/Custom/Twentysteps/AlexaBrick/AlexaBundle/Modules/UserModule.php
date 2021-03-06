<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;

use JMS\DiExtraBundle\Annotation\Service;

use Symfony\Component\Routing\Router;
use Twig_Environment;
use Swift_Mailer;

use FOS\UserBundle\Doctrine\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder;
use Gesdinet\JWTRefreshTokenBundle\Doctrine\RefreshTokenManager;

use twentysteps\Commons\EnsureBundle\Ensure;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Entity\Project;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Interfaces\BricksScope;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Interfaces\CoreInjectionModule;

use Bricks\Basic\BusinessBrick\BusinessBundle\Entity\Customer\Contact;
use Bricks\Basic\BusinessBrick\BusinessBundle\Entity\Customer\Customer;
use Bricks\Basic\BusinessBrick\BusinessBundle\Entity\Sales\Task;
use Bricks\Basic\BusinessBrick\BusinessBundle\Shell\BusinessShell;

use Bricks\AbstractCustomBundle\Modules\AbstractUserModule;
use Bricks\AbstractCustomBundle\Entity\AbstractUser;
use Bricks\AbstractCustomBundle\Entity\AbstractLogin;
use Bricks\AbstractCustomBundle\DTO\AbstractRegistration;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\DTO\Registration;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\UserRepository;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\Login;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\LoginRepository;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Event\RegistrationSuccessEvent;


/**
 * Module for users.
 */
class UserModule extends AbstractUserModule {

    /** @var string|null */
    protected $bcc;
	
	/** @var string|null */
	protected $bccDev;

    /** @var BusinessShell $business */
    protected $business;

    /**
     * @var CoreInjectionModule $coreInjectionModule
     * @BS\Inject(brickAlias="core")
     */
    protected $coreInjectionModule;

    /**
     * @var Project
     */
    protected $currentProjectEntity;
	
	/**
	 * @var Router
	 */
    protected $router;


    public function __construct(EntityManager $em, RequestStack $requestStack, EventDispatcherInterface $eventDispatcher, UserManager $userManager, $sts, $sac, RefreshTokenManager $refreshTokenManager, $redis, $jwtEncoder, $liipImagineCacheManager, $avatarDirectory, $avatarPath, $translator, Twig_Environment $twig, Swift_Mailer $mailer, $fromEmail, $fromName, $replyTo, $allowMultipart, $customProtocol, $customHost, $activationTokenValidity, $passwordResetTokenValidity, $bcc, $bccDev, BusinessShell $business, Router $router, $logger) {
        parent::__construct($em, $requestStack, $eventDispatcher, $userManager, $sts, $sac, $refreshTokenManager, $redis, $jwtEncoder, $liipImagineCacheManager, $avatarDirectory, $avatarPath, $translator, $twig, $mailer, $fromEmail, $fromName, $replyTo, $allowMultipart, $customProtocol, $customHost, $activationTokenValidity, $passwordResetTokenValidity, $logger);
        $this->bcc = $bcc;
	    $this->bccDev = $bccDev;
        $this->business = $business;
        $this->router = $router;
    }

    // abstracts
    
	/** @return UserRepository */
    public function getRepository() {
        return $this->em->getRepository('BricksCustomTwentystepsAlexaBundle:User');
    }

    /** @return LoginRepository */
    public function getLoginRepository() {
        return $this->em->getRepository('BricksCustomTwentystepsAlexaBundle:Login');
    }

    public function getBundleShortName() {
        return 'BricksCustomTwentystepsAlexaBundle';
    }

    public function getTranslationDomain() {
        return 'twentysteps_alexa';
    }

    public function getUserRole() {
        return 'ROLE_TWENTYSTEPS_ALEXA_USER';
    }

    public function getLiipAvatarPrefix() {
        return 'twentysteps_alexa_user_avatar';
    }

    /** @return AbstractLogin */
    public function loginFactory() {
        return new Login();
    }

    // overrides

    /** @return User */
    public function find($id) {
        return $this->getRepository()->find($id);
    }

    /** @return User[] */
    public function findAll($enabled = false) {
    	if ($enabled) {
    		return $this->getRepository()->findBy([
    			'enabled' => true
		    ]);
	    }
        return $this->getRepository()->findAll();
    }

    public function updateFromData(AbstractUser $user, $data) {
        /** @var User $user */
        parent::updateFromData($user, $data);
        $this->em->flush();
    }

    public function getDefaultAvatarPath($user) {
        $basePath = '/bundles/brickscustomtwentystepsalexa/SPA/assets/images/avatars/';
        if ($user->isMan()) {
            return $basePath . 'avatar-1.png';
        }
        return $basePath . 'avatar-6.png';
    }

    protected function getActivationWebUrlPrefix($locale = 'de') {
        return '/' . $locale . '/confirm';
    }

    protected function getActivationUrl(AbstractUser $user, $locale = 'de') {
        return $this->getURLForPath($this->getActivationWebUrlPrefix($locale)) . '?token=' . urlencode($user->getActivationToken());
    }

    protected function getPasswordResetWebUrlPrefix($locale = 'de') {
        return '/' . $locale . '/reset-password';
    }

    protected function getResetPasswordUrl(AbstractUser $user, $locale = 'de') {
        return $this->getURLForPath($this->getPasswordResetWebUrlPrefix($locale)) . '?token=' . urlencode($user->getResetPasswordToken());
    }

    protected function autoEnable() {
        return false;
    }

    /**
     * @param AbstractUser $user
     * @return bool
     */
    protected function autoEnableByUser(AbstractUser &$user) {
        /** @var User $user */
        return false;
    }

    /**
     * @param AbstractUser $user
     * @return bool
     */
    protected function shouldSendActivationMail(AbstractUser &$user) {
        /** @var User $user */
        return !$this->autoEnableByUser($user);
    }
	
	/**
	 * @return array|string
	 */
    protected function getBcc() {
    	$bccs = [];
    	if ($this->bcc && strlen($this->bcc) > 0) {
    		$bccs[]=$this->bcc;
	    }
    	if ($this->bccDev && strlen($this->bccDev) > 0) {
    		$bccs[]=$this->bccDev;
	    }
	    return $bccs;
    }

    /** @return AbstractRegistration */
    protected function notifyRegistrationSuccess(AbstractUser $user) {
        /** @var User $user */
        $this->eventDispatcher->dispatch(RegistrationSuccessEvent::NAME, new RegistrationSuccessEvent($user));
    }

    public function processCustomRegistrationData(AbstractUser &$user, AbstractRegistration $registration) {
        $user->addRole('ROLE_TWENTYSTEPS_ALEXA_USER');
    }

	public function processUserAfterActivation(AbstractUser $user) {
    	parent::processUserAfterActivation($user);
		/** @var User $user */
		$this->getShell()->getMailModule()->sendMailToUser($user,
			[
				'user' => $user
			],
			'welcome','Willkommen bei 20steps'
		);
	}

    protected function getMinUsernameLength() {
        return 5;
    }

    protected function getMaxUsernameLength() {
        return 100;
    }

    public function updateProfileFromData($profile) {
        $flash = parent::updateProfileFromData($profile);
        /** @var User $user */
        $user = $this->getCurrentUser();


        if (array_key_exists('firstName', $profile)) {
            $user->setFirstName($profile['firstName']);
        }

        if (array_key_exists('lastName', $profile)) {
            $user->setLastName($profile['lastName']);
        }


        $this->em->flush();
        return $flash;
    }
	

	// custom finders
    
    /** @return User|null */
    public function findOneByEmail($email) {
        return $this->getRepository()->findOneByEmail($email);
    }
	
	/** @return User|null */
	public function findOneByCanonicalEmail($email) {
		return $this->getRepository()->findOneByCanonicalEmail(mb_convert_case($email,MB_CASE_LOWER));
	}

	/** @return User|null */
    public function findOneBySlug($slug) {
        return $this->getRepository()->findOneBySlug($slug);
    }
	
	/**
	 * @param User $user
	 * @return User
	 */
	public function updateUser(User $user) {
		$this->em->persist($user);
		$this->em->flush();
		return $user;
	}

    // helpers

    /** @return Project */
    protected function getCurrentProject() {
        return $this->coreInjectionModule->getActor(BricksScope::PROJECT());
    }

    /** @return Project */
    protected function getCurrentProjectEntity() {
        if (!$this->currentProjectEntity) {
            $cachedProject = $this->getCurrentProject();
            if ($cachedProject) {
                $this->currentProjectEntity = $this->getCore()->project()->find($cachedProject->getId());
            }
        }
        return $this->currentProjectEntity;
    }
	
	/**
	 * @return AlexaShell
	 */
	public function getShell() {
		return parent::getShell();
	}
}