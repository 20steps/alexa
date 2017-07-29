<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\JWT;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
	use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler as BaseAuthenticationSuccessHandler;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
	use Lexik\Bundle\JWTAuthenticationBundle\Events;
	use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
	use Symfony\Component\EventDispatcher\EventDispatcherInterface;
	use Symfony\Component\HttpFoundation\JsonResponse;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
	use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
	
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Shells\CoreShell;
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Entity\Project;
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Interfaces\BricksScope;
	use Bricks\AbstractCustomBundle\Entity\AbstractUser;
	use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
	use JMS\Serializer\SerializationContext;
	use twentysteps\Commons\EnsureBundle\Ensure;
	
	/**
	 * AuthenticationSuccessHandler
	 *
	 * If referenced as JWT success handler in the security.yml this reduces the number
	 * of calls for login from two to one by sending uesr info as part of token creation.
	 *
	 * Bricks/Angular will automatically use this if available.
	 *
	 */
	class AuthenticationSuccessHandler extends BaseAuthenticationSuccessHandler
	{
		
		/** @var CoreShell $core */
		private $core;
		private $serializer;
		private $em;
		private $exclusionStrategy;
		private $businessShell;

		/**
		 * @param JWTManager               $jwtManager
		 * @param EventDispatcherInterface $dispatcher
		 */
		public function __construct(JWTManager $jwtManager, EventDispatcherInterface $dispatcher, CoreShell $core, $em, $serializer, $exclusionStrategy, $businessShell)
		{
			parent::__construct($jwtManager,$dispatcher);
			$this->core = $core;
			$this->em = $em;
			$this->serializer = $serializer;
			$this->exclusionStrategy = $exclusionStrategy;
			$this->businessShell = $businessShell;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function onAuthenticationSuccess(Request $request, TokenInterface $token)
		{
			/** @var User $user */
			$user = $token->getUser();
			
			// do not allow non-staff users to login to crm
			$scopeOptions = $request->get('scopeOptions');
			if ($scopeOptions) {
				if (substr($scopeOptions,0,3)=='crm') {
					if (!$user->isStaff()) {
						Ensure::fail('Non staff users not allowed to login in CRM');
					}
				}
			}
			
			$jwt  = $this->jwtManager->create($user);
			
			$response = new JsonResponse();
			
			$event = new AuthenticationSuccessEvent(array('token' => $jwt), $user, $request, $response);
			
			$this->dispatcher->dispatch(Events::AUTHENTICATION_SUCCESS, $event);
			
			$customData= array(
				'status' => 'success',
				'statistics' => array(
					'duration' => 0
				),
				'data' => array(
					'user' => $this->serializeUser($user)
				)
			);
			
			$response->setData(array_merge($event->getData(),$customData));
			
			return $response;
		}
		
		// private helpers
		
		protected function serializeUser(AbstractUser $user) {
			
			/** @var Project $project */
			$project=$this->em->getRepository('BricksCoreCoreBundle:Project')->findOneByCode('alexa');
			
			$this->core->injection()->enterScope(BricksScope::PROJECT(), $project);
			$this->businessShell->getWrapperModule()->wrapUser($user);
			
			// serialize
			$userData = json_decode(
				$this->serializer->serialize(
					$user,'json',$this->getSerializationContext($user),
					true
				)
			);
			
			$this->core->injection()->leaveScope();
			
			return $userData;
		}
		
		protected function getSerializationContext($user) {
			$serializationContext = SerializationContext::create();
			$serializationContext->enableMaxDepthChecks();
			$serializationContext->setGroups(["self","linked","public"]);
			$serializationContext->addExclusionStrategy($this->exclusionStrategy);
			$serializationContext->setAttribute('requestingUser',$user);
			return $serializationContext;
		}
		
		
	}
