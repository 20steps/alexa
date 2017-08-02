<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Bricks\AbstractCustomBundle\Exception\EmailAlreadyTakenException;
use Bricks\AbstractCustomBundle\Exception\EmailInvalidException;
use Bricks\AbstractCustomBundle\Exception\PasswordToShortException;
use Bricks\AbstractCustomBundle\Exception\UserNotActivatedException;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\DTO\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;

use FOS\RestBundle\Controller\Annotations\View;

use twentysteps\Commons\EnsureBundle\Ensure;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Controller\AbstractBricksController;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\UserModule;


class UserController extends AbstractBricksController {
	
	/**
	 * @var UserModule $userModule ;
	 * @BS\Inject()
	 */
	protected $userModule;
	
	/**
	 * @param Request $request
	 */
	protected function setupController(Request $request) {
		parent::setupController($request);
		// force html format as some spiders send strange requests ...
		$request->setRequestFormat('html');
	}
	
	
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function registerAction(Request $request) {
		$context = [
			'body_class' => 'register',
			'title' => 'Register'
		];
		if ($request->getMethod()==Request::METHOD_POST) {
			$request->request->set('username',$request->request->get('email'));
			$registration = new Registration();
			$registration->setFromRequest($request);
			try {
				$result = $this->userModule->register($registration);
				$context['message'] = $result['message'];
				$context['is_enabled'] = $result['is_enabled'];
			} catch(UserNotActivatedException $e) {
				$context['message']='user_not_activated';
				$context['is_enabled']=false;
			} catch(EmailInvalidException $e) {
				$context['message']='email_invalid';
				$context['is_enabled']=false;
			} catch(EmailAlreadyTakenException $e) {
				$context['message']='email_already_taken';
				$context['is_enabled']=false;
			} catch(PasswordToShortException $e) {
				$context['message']='password_to_short';
				$context['is_enabled']=false;
			} catch(\Exception $e) {
				$context['message']=$e->getMessage();
				$context['is_enabled']=false;
			}
		}
		return $context;
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function confirmAction(Request $request) {
		$context = [
			'body_class' => 'confirm_registration',
			'title' => 'Confirm registration'
		];
		
		$token = $request->query->get('token');
		if ($token) {
			$flash = $this->userModule->activateRegistration($token);
			$context['message'] = $flash['message'];
		}
		
		
		return $context;
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function resendActivationLinkAction(Request $request) {
		$context = [
			'body_class' => 'resend_activation_link',
			'title' => 'Resend activation link'
		];

		if ($request->getMethod()==Request::METHOD_POST) {
			$email = Ensure::isNotNull($request->request->get('email'),'Email parameter missing');
			$this->userModule->resendActivationLinkByEmailOrUsername($email);
			$context['message']='resent_activation_link';
		}
		
		return $context;
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function loginAction(Request $request) {
		$session = $request->getSession();
		
		$context = [
			// last username entered by the user
			'last_username' => $session->get(Security::LAST_USERNAME),
			'body_class' => 'login',
			'title' => 'Login'
		];
		
		// get the login error if there is one
		if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
		} else {
			$error = $session->get(Security::AUTHENTICATION_ERROR);
			$session->remove(Security::AUTHENTICATION_ERROR);
			if ($error) {
				$context['message']='Wrong credentials';
			}
		}
		
		$context['error'] = $error;

		return $context;
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function homeAction(Request $request) {
		/**
		 * @var User $user
		 */
		$user = Ensure::isNotNull($this->getUser(),'user not logged in');
		$user = $this->getBrickShell()->getUserModule()->find($user->getId());
		$user->completeSettingsUsingDefaults();
		$context = [
			'user' => $user,
			'body_class' => 'home',
			'title' => 'Settings'
		];
		if ($request->getMethod()==Request::METHOD_POST) {
			$settings = $request->request->all();
			foreach ($settings as $key => $value) {
				$value = trim($value);
				if ($key=='firstName') {
					$user->setFirstName($value);
				} else {
					$user->updateSetting($key,$value);
				}
			}
			$this->userModule->updateUser($user);
			$context['message']='Settings saved';
		}
		return $context;
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function resetPasswordAction(Request $request) {
		$context = [
			'body_class' => 'reset_password',
			'title' => 'Reset password'
		];
		if ($request->getMethod()==Request::METHOD_POST) {
			if (!$request->request->has('token')) {
				$this->userModule->resetPasswordByEmailOrUsername($request->request->get('password'));
				// do not show user if user was found or not for security reasons
				$context['message']='reset_password_triggered';
			} else {
				$flash = $this->userModule->resetPasswordUpdateByToken($token,$request->request->get('password'));
				$context['message']=$flash['message'];
			}
		} else {
			if ($request->query->has('token')) {
				$token = $request->query->get('token');
				$flash = $this->userModule->isResetPasswordTokenValid($token);
				$context['message']=$flash['message'];
			}
		}
		return $context;
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function loginAlexaAction(Request $request) {
		$session = $request->getSession();
		$context = [
			// last username entered by the user
			'last_username' => $session->get(Security::LAST_USERNAME),
			'body_class' => 'login',
			'title' => 'Login'
		];
		
		$session->set('alexa_state',$request->query->get('state',rand(0,99)));
		$session->set('alexa_client_id',$request->query->get('client_id',$this->getParameter('bricks_custom_twentysteps_alexa_alexa_oauth2_client_id')));
		$session->set('alexa_response_type',$request->query->get('response_type','code'));
		$session->set('alexa_scope',$request->query->get('scope','_TWENTYSTEPS_ALEXA_USER'));
		$session->set('alexa_redirect_uri',$request->query->get('redirect_uri','https://20steps.de'));
		
		// get the login error if there is one
		if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
		} else {
			$error = $session->get(Security::AUTHENTICATION_ERROR);
			$session->remove(Security::AUTHENTICATION_ERROR);
			if ($error) {
				$context['message']='Wrong credentials';
			}
		}
	
		$context['error'] = $error;
		
		return $context;
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function registerAlexaAction(Request $request) {
		$context = [
			'body_class' => 'register',
			'title' => 'Register'
		];
		if ($request->getMethod()==Request::METHOD_POST) {
			$context['message']='Registration succeeded';
			$context['message']='Not yet implemented';
		}
		return $context;
	}
	
	
	/**
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function preAuthorizeAlexaAction(Request $request) {
		$session = $request->getSession();
		return $this->redirectToRoute('fos_oauth_server_authorize',[
			'state' => $session->get('alexa_state','na'),
			'client_id' => $session->get('alexa_client_id','na'),
			'response_type' => $session->get('alexa_response_type','na'),
			'scope' => $session->get('alexa_scope','na'),
			'redirect_uri' => $session->get('alexa_redirect_uri','na')
		]);
		
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function homeAlexaAction(Request $request) {
		$context = [
			'body_class' => 'home',
			'message' => 'Hallo Alexa',
			'title' => 'Settings'
		];
		return $context;
	}
	
}