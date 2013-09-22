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
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\SecurityExtraBundle\Exception\RequiredRolesMissingException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AccessDeniedListener implements AccessDeniedHandlerInterface
{
    /** @var $securityContext \Symfony\Component\Security\Core\SecurityContextInterface */
    private $securityContext;
    
    /** @var $router \Symfony\Component\Routing\RouterInterface */
    private $router;
    
    /** @var $session \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;
    
    /** @var $translator \Symfony\Component\Translation\TranslatorInterface */
    private $translator;
    
    /** @var $parameters array(string:string) */
    private $parameters;
    
    /** @var $planRoles array(string) */
    private $planRoles;
    
    public function __construct(
            \Symfony\Component\Security\Core\SecurityContextInterface $securityContext, 
            \Symfony\Component\Routing\RouterInterface $router, 
            \Symfony\Component\HttpFoundation\Session\SessionInterface $session, 
            \Symfony\Component\Translation\TranslatorInterface $translator,
            array $parameters)
    {
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
        $this->parameters = $parameters;
        
        $this->planRoles = array();
        foreach ($this->parameters['plans'] as $plan)
        {
            $this->planRoles[] = $plan['role'];
        }
    }
        
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        if ($accessDeniedException instanceof RequiredRolesMissingException)
        {
            $requiredRoles = $accessDeniedException->getRoles();
            $planRoles = array();
            
            foreach ($requiredRoles as $role)
            {
                $user = $this->securityContext->getToken()->getUser();
                // check if required role is part of a plan and if this role is missing for the user
                if (in_array($role, $this->planRoles) && !in_array($role, $user->getRoles()))
                {
                    return $this->restrictedResponse();
                }
            }
        }
    }
    
    private function restrictedResponse()
    {
        $this->session->getFlashBag()->set('danger',
            $this->translator->trans(
                $this->parameters['subscription_required_i18nKey'],
                array(),
                $this->parameters['subscription_required_message_domain']
            )
        );
        
        $response = new RedirectResponse($this->router->generate($this->parameters['subscribe_route']));
        
        return $response;
    }
}
