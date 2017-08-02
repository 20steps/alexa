<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Symfony\Component\EventDispatcher\EventDispatcherInterface;
	
	use Symfony\Component\HttpFoundation\RequestStack;
	use twentysteps\Commons\EnsureBundle\Ensure;
	
	use Bricks\Basic\MobileBrick\MobileBundle\Modules\AbstractMailModule;
	use Bricks\Basic\PagesBrick\PagesBundle\Shell\PagesShell;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\DTO\Message;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;
	
	/**
	 * Module for mailing.
	 */
	class MailModule extends AbstractMailModule {
		
		/** @var \Twig_Environment */
		private $twig;
		
		/**
		 * @var PagesShell
		 */
		private $pages;
		
		/** @var  \Swift_Mailer */
		private $mailer;
		
		/** @var string */
		private $mailUser;
		/** @var string */
		private $fromMail;
		/** @var string */
		private $fromName;
		/** @var string */
		private $replyTo;
		/** @var string */
		private $bcc;
		/** @var string */
		private $bccDev;

		/** @var string */
		private $customHost;
		/** @var string */
		private $customProtocol;
		
		/**
		 * @var EventDispatcherInterface
		 */
		private $eventDispatcher;
		
		public function __construct(\Twig_Environment $twig, PagesShell $pages, \Swift_Mailer $mailer, $mailUser, $fromMail, $fromName, $replyTo, $bcc, $bccDev, $customHost, $customProtocol, EventDispatcherInterface $eventDispatcher, $logger) {
			parent::__construct($logger);
			$this->twig = $twig;
			$this->pages = $pages;
			$this->mailer = $mailer;
			$this->mailUser = $mailUser;
			$this->bcc = $bcc;
			$this->bccDev = $bccDev;
			$this->fromMail = $fromMail;
			$this->fromName = $fromName;
			$this->replyTo = $replyTo;
			$this->customHost = $customHost;
			$this->customProtocol = $customProtocol;
			$this->eventDispatcher = $eventDispatcher;
		}
		
		public function sendMessage(Message $message, User $user) {
			$allowed=true;
			$action = $message->getAction();
			if ($action) {
				if ($user->getSetting('mail:'.$action,true)!=true) {
					$allowed=false;
				}
			}
			$locale = $this->getLocale();
			if ($allowed) {
				$this->sendMail(
					$user->getEmailCanonical(),
					$user->getDisplayName(),
					array(
						'user' => $user,
						'text' => $message->getText(),
						'custom' => $message->getCustom()
					),
					'message.'.$locale.'.txt.twig',
					'message.'.$locale.'.html.twig',
					'20steps: '.$message->getText()
				);
			}
		}
		
		public function sendMail($toMail, $toName, $data, $viewText, $viewHTML, $subject='Nachricht von 20steps', $fromMail = null, $fromName = null, $replyTo = null) {
			$fromMail = $fromMail?$fromMail:$this->fromMail;
			$fromName = $fromName?$fromName:$this->fromName;
			$replyTo = $replyTo?$replyTo:$this->replyTo;
			if (!array_key_exists('user',$data)) {
				$data['user']=[
					'email' => $toMail,
					'displayName' => $toName
				];
			}
			$bccs = [];
			if ($this->bcc && strlen($this->bcc)>0) {
				$bccs[]=$this->bcc;
			}
			if ($this->bccDev && strlen($this->bccDev)>0) {
				$bccs[]=$this->bccDev;
			}
			
			if (!$this->getShell()->isLive()) {
				//$toMail = $fromMail;
			}
			
			$message = \Swift_Message::newInstance()
				->setSubject($subject)
				->setFrom([$fromMail => $fromName])
				->setReplyTo($replyTo)
				->setTo($toMail)
				->setBcc($bccs)
				->setBody(
					$this->twig->render(
						'BricksCustomTwentystepsAlexaBundle:mails:'.$viewText,
						array(
							'toMail' => $toMail,
							'toName' => $toName ,
							'subject' => $subject,
							'data' => $data,
							'user' => $data['user'],
							'urlPrefix' => $this->getURLPrefix()
						)
					),
					'text/plain'
				)
				->setBody(
					$this->twig->render(
						'BricksCustomTwentystepsAlexaBundle:mails:'.$viewHTML,
						array(
							'toMail' => $toMail,
							'toName' => $toName ,
							'subject' => $subject,
							'data' => $data,
							'user' => $data['user'],
							'urlPrefix' => $this->getURLPrefix()
						)
					),
					'text/html'
				)
			;
			
			return $this->mailer->send($message);
		}
		
		public function sendMailToUser(User $user, $data, $view, $subject='Nachricht von 20steps', $fromMail = null, $fromName = null, $replyTo = null) {
			$locale = $this->getLocale();
			return $this->sendMail($user->getEmail(), $user->getDisplayName(), $data, $view.'.'.$locale.'.txt.twig', $view.'.'.$locale.'.html.twig', $subject, $fromMail, $fromName, $replyTo);
		}
		
		public function sendMailToAdmins($data, $view, $subject='Nachricht von Bricks by 20steps', $fromMail = null, $fromName = null, $replyTo = null) {
			$users = $this->pages->getUserModule()->findByRole('ROLE_WP_ADMINISTRATOR');
			$locale = $this->getLocale();
			foreach ($users as $user) {
				$this->sendMail($user->getEmail(), $user->getDisplayName(), $data, $view.'.'.$locale.'.txt.twig', $view.'.'.$locale.'.html.twig', $subject, $fromMail, $fromName, $replyTo);
			}
			return true;
		}
		
		public function sendMailToEditors($data, $view, $subject='Nachricht von Bricks by 20steps', $fromMail = null, $fromName = null, $replyTo = null) {
			$users = $this->pages->getUserModule()->findByRole('ROLE_WP_EDITOR');
			$locale = $this->getLocale();
			foreach ($users as $user) {
				$this->sendMail($user->getEmail(), $user->getDisplayName(), $data, $view.'.'.$locale.'.txt.twig', $view.'.'.$locale.'.html.twig', $subject, $fromMail, $fromName, $replyTo);
			}
			return true;
		}
		
		
		// helpers
		
		/**
		 * @return AlexaShell
		 */
		public function getShell() {
			return parent::getShell();
		}
		
		private function getURLPrefix() {
			return $this->customProtocol.'://'.$this->customHost;
		}
		
		/**
		 * @return string
		 */
		private function getLocale($default='de') {
			/**
			 * @var RequestStack $requestStack
			 */
			$requestStack = $this->getCore()->getContainer()->get('request_stack');
			$request = $requestStack->getCurrentRequest();
			if ($request) {
				return $requestStack->getCurrentRequest()->getLocale();
			}
			return $default;
		}
		
	}