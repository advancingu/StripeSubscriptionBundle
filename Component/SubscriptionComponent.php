<?php

/*
 * This file is part of the AdvancinguStripeSubscriptionBundle package.
 *
 * (c) 2013 Markus Weiland <mw@graph-ix.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Advancingu\StripeSubscriptionBundle\Component;

use Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface;
use Advancingu\StripeSubscriptionBundle\Exception\SubscriptionFailedException;

/**
 * Permits subscripting and unsubscribing subscribers from one of predefined plans.
 * 
 * Uses Stripe for billing.
 */
class SubscriptionComponent
{
    /** @var $logger \Symfony\Component\HttpKernel\Log\LoggerInterface */
    private $logger;
    
    /** @var $stripeApiKey string */
    private $stripeApiKey;
    
    /** 
     * @var array(string:array(string:mixed)) Definitions of non-trial plans.
     * Top array keys equal the plan names registered with Stripe.
     * @see https://manage.stripe.com/plans
     */
    private $plans;
    
    public function __construct(\Symfony\Component\HttpKernel\Log\LoggerInterface $logger, $stripeApiKey, array $plans)
    {
        $this->logger = $logger;
        $this->stripeApiKey = $stripeApiKey;
        $this->plans = $plans;
    }
    
    /**
     * Subscribes $subscriber to plan $plan.
     * 
     * @param \Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber
     * @param string $plan
     * @throws \InvalidArgumentException|\Advancingu\StripeSubscriptionBundle\Exception\SubscriptionFailedException
     */
    public function subscribe(\Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber, $plan)
    {
        if ($plan === null)
        {
            return $this->unsubscribe($subscriber);
        }
        
        if (!in_array($plan, array_keys($this->plans)))
        {
            throw new \InvalidArgumentException('"$plan" must be a valid plan name.');
        }
        
        if ($subscriber->getStripeCustomerId() === null)
        {
            throw new \Exception(sprintf('No Stripe customer ID associated with subscriber "%s".', $subscriber->getId()));
        }
        
        \Stripe::setApiKey($this->stripeApiKey);
        $customer = \Stripe_Customer::retrieve($subscriber->getStripeCustomerId());
        
        $this->logger->debug(sprintf('About to subscribe subscriber "%s" to plan "%s".', 
            $subscriber->getId(), $plan));
        $result = $customer->updateSubscription(array("plan" => $plan));
        $this->logger->debug(sprintf("Subscription result for subscriber \"%s\":\n%s", 
            $subscriber->getId(), $result->__toString()));
        
        if ($result->__get('object') !== 'subscription')
        {
            throw new SubscriptionFailedException(sprintf('Subscribing to plan "%s" for subscriber "%s" failed.', 
                $plan, $subscriber->getId()));
        }
    }
    
    /**
     * Immediately unsubscribes $subscriber from his current plan.
     * 
     * @param \Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber
     */
    public function unsubscribe(\Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber)
    {
        if ($subscriber->getStripeCustomerId() === null)
        {
            throw new \Exception(sprintf('No Stripe customer ID associated with subscriber "%s".', $subscriber->getId()));
        }
        
        \Stripe::setApiKey($this->stripeApiKey);
        $customer = \Stripe_Customer::retrieve($subscriber->getStripeCustomerId());
        
        $this->logger->debug(sprintf('About to cancel subscription for subscriber "%s".', $subscriber->getId()));
        $result = $customer->cancelSubscription();
        $this->logger->debug(sprintf("Unsubscribe result for subscriber \"%s\":\n%s", $subscriber->getId(), $result->__toString()));

        if ($result->__get('status') !== 'canceled')
        {
            throw new SubscriptionFailedException(sprintf('Unsubscribing subscriber "%s" from plan failed.', $subscriber->getId()));
        }
    }
    
    /**
     * Creates a customer object at Stripe using $cardToken.
     * 
     * @param string $cardToken Credit card token created by Stripe.js.
     * @param string $localId An identifier to use as a customer description in Stripe. Usually a local database ID.
     * @return string The ID of the Stripe customer object created (Stripe customer ID).
     */
    public function createStripeCustomer($cardToken, $localId)
    {
        \Stripe::setApiKey($this->stripeApiKey);
        
        $result = \Stripe_Customer::create(array(
            "description" => $localId,
            "card" => $cardToken
        ));
        
        return $result->__get('id');
    }
    
    /**
     * Returns the name of the subscriber's current non-trial 
     * plan or null if none, based on the current roles of $subscriber.
     * 
     * @param \Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber
     * @return string|null 
     */
    public function getCurrentPlanName(\Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber)
    {
        $currentPlanName = null;
        $roleNames = array();
        
        foreach ($this->plans as $planName => $plan)
        {
            $roleNames[$plan['role']] = $planName;
        }
        
        foreach ($subscriber->getRoles() as $role)
        {
            if (in_array($role, array_keys($roleNames)))
            {
                $currentPlanName = $roleNames[$role];
                break;
            }
        }
        
        return $currentPlanName;
    }
}
