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

use Symfony\Component\Security\Core\User\UserInterface;

interface SubscriberInterface extends UserInterface
{
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
    
    /**
     * @return \DateTime|null Point in time at which the trial access 
     * of a subscriber ends.
     */
    public function getTrialEnd();
}