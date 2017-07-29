<?php
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Security\Voter;
	
	use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

	use Bricks\Infrastructure\CoreBrick\CoreBundle\Security\Voter\AbstractResourceActionVoter;

	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;

	class ResourceActionVoter extends AbstractResourceActionVoter {

		/**
		 * {@inheritdoc}
		 */
		protected function voteOnAttribute($operation, $resource, TokenInterface $token) : bool {
			
			// general authorization logic for this custom brick
			
			if ('api_platform.action.get_collection' === $operation) {
				return true;
			}
			if ('api_platform.action.get_item' === $operation) {
				return true;
			}
			
			// not fully authenticated user not allowed
			if (!$this->decisionManager->decide($token, ['IS_AUTHENTICATED_FULLY'])) {
				return false;
			}
			
			// admin allowed to do anything
			if ($this->decisionManager->decide($token, ['ROLE_twentysteps_alexa_ADMIN_USER'])) {
				return true;
			}
			
			// remove me after basic integration of ng-admin ...
			return true;
			
			// hand over to modules and general logic
			return parent::voteOnAttribute($operation,$resource,$token);
		}
		
		// helpers
		
		/** @return AlexaShell */
		protected function getShell() {
			return $this->container->get('twentysteps_alexa');
		}
		
	}