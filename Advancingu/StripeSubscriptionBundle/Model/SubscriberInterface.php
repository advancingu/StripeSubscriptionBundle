<?php

/*
 * This file is part of the AdvancinguStripeSubscriptionBundle package.
 *
 * (c) 2013 Markus Weiland <mw@graph-ix.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Advancingu\StripeSubscriptionBundle\Model;

interface SubscriberInterface
{
    /**
     * Default internal plan ID for the free plan that 
     * requires no actual subscription. */
    const PLAN_FREE = 1;
    
    /**
     * @return array(int:string) Array of internal plan IDs and 
     * their corresponding plan name registered with Stripe at 
     * https://manage.stripe.com/plans .
     */
    public function getPlanIdentifiers();
    
    /**
     * @return string Stripe customer object ID created with 
     * SubscriptionComponent::createStripeCustomer()
     */
    public function getStripeCustomerId();
    
    /**
     * @return string Internal ID of the subscriber object, e.g. 
     * a User's database ID.
     */
    public function getId();
}