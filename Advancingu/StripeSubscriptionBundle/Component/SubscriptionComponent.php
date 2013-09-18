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
    /** @var $logger \Symfony\Bridge\Monolog\Logger */
    private $logger;
    
    /** @var $stripeApiKey string */
    private $stripeApiKey;
    
    public function __construct(\Symfony\Bridge\Monolog\Logger $logger, $stripeApiKey)
    {
        $this->logger = $logger;
        $this->stripeApiKey = $stripeApiKey;
    }
    
    /**
     * Subscribes $subscriber to plan $planId.
     * 
     * @param \Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber
     * @param int $planId
     * @throws \InvalidArgumentException|\Advancingu\StripeSubscriptionBundle\Exception\SubscriptionFailedException
     */
    public function subscribe(\Advancingu\StripeSubscriptionBundle\Model\SubscriberInterface $subscriber, $planId)
    {
        if (!in_array($planId, array_keys($subscriber->getPlanIdentifiers())))
        {
            throw new \InvalidArgumentException('"$planId" must be a plan constant integer.');
        }
        
        if ($subscriber->getStripeCustomerId() === null)
        {
            throw new \Exception(sprintf('No Stripe customer ID associated with subscriber "%s".', $subscriber->getId()));
        }
        
        if ($planId === SubscriberInterface::PLAN_FREE)
        {
            return $this->unsubscribe($subscriber);
        }
        
        $planStripeIdents = $subscriber->getPlanIdentifiers();
        $planName = $planStripeIdents[$planId];

        \Stripe::setApiKey($this->stripeApiKey);
        $customer = \Stripe_Customer::retrieve($subscriber->getStripeCustomerId());
        
        $this->logger->debug(sprintf('About to subscribe subscriber "%s" to plan named "%s".', $subscriber->getId(), $planName));
        $result = $customer->updateSubscription(array("plan" => $planName));
        $this->logger->debug(sprintf("Subscription result for subscriber \"%s\":\n%s", $subscriber->getId(), $result->__toString()));
        
        if ($result->__get('object') !== 'subscription')
        {
            throw new SubscriptionFailedException(sprintf('Subscribing to plan with ID "%d" for subscriber "%s" failed.', $planId, $subscriber->getId()));
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
}
