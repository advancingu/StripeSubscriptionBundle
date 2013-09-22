<?php

/*
 * This file is part of the AdvancinguStripeSubscriptionBundle package.
 *
 * (c) 2013 Markus Weiland <mw@graph-ix.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Advancingu\StripeSubscriptionBundle\EventListener;

use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface;

/**
 * Checks a user on authentication if a trial plan existed and 
 * has expired. On expiry, the trial role is removed and the user 
 * is flagged for re-authentication.
 */
class AuthenticationSuccessListener
{
    /** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;
    
    /** @var $parameters array(string:string) */
    private $parameters;
    
    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container, 
        array $parameters
    )
    {
        $this->container = $container;
        $this->parameters = $parameters;
    }
    
    public function handleAuthenticationSuccess(AuthenticationEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof SubscriberInterface)
        {
            // if trial time has expired and user has trial role
            if ($user->getTrialEnd() !== null 
            && $user->getTrialEnd() < new \DateTime() 
              && in_array($this->parameters['trial_role'], $user->getRoles()))
            {
                $om = $this->container->get($this->parameters['object_manager']);
                
                $user->removeRole($this->parameters['trial_role']);
                $user->setTrialEnd(null);
                $om->flush($user);

                // force user roles to be reloaded into session immediately
                $event->getAuthenticationToken()->setAuthenticated(false);
            }
        }
    }
}