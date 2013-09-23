AdvancinguStripeSubscriptionBundle
==================================

The AdvancinguStripeSubscriptionBundle provides a simple Symfony service to 
subscribe users to payable subscriptions using the [Stripe](https://stripe.com) 
payment service. The bundle also includes an HTML table and JS code to 
display available subscription plans and allow users to subscribe to 
a plan using Stripe's payment pop-up window.

An account with [Stripe](https://stripe.com) is required to use 
this bundle.

Installation
------------

The bundle requires Symfony 2.2 or greater, the Stripe PHP API, as well as 
JMSSecurityExtraBundle.

Install the bundle via Composer by adding the following line to your 
```composer.json``` dependencies: 

    "advancingu/stripe-subscription-bundle": "dev-master"


Semantic versioning of this bundle will be introduced once it has stabilized further.

Configuration
-------------

Sample configuration via ```config.yml```:

    parameters:
        advancingu_stripe_subscription.keys.public: %stripe_public_key%
        advancingu_stripe_subscription.keys.secret: %stripe_secret_key%
    
    advancingu_stripe_subscription:
        stripe_public_key: %advancingu_stripe_subscription.keys.public%
        stripe_secret_key: %advancingu_stripe_subscription.keys.secret%
        payee_name: "ACME Industries Inc."
        plans:
            my_default_plan:                 # The plan's ID as defined with Stripe.
                i18nKey: plan.default.name   # Key inside translation domain for a plan.
                messageDomain: messages      # Translation domain to use for plan name.
                price: 2000                  # In cents.
                currency: CAD                # Three character ISO currency code. Must be supported by your Stripe account.
                role: ROLE_PLAN_DEFAULT           # The lowest user role required for permission to execute actions protected by the plan.
        subscription_check:
            subscribe_route: MyWebsiteBundle_default_subscribe            # Route to redirect to if a user tries to access a resource without or with an insufficient plan. 
            subscription_required_i18nKey: subscription.please_subscribe  # Key of the flash message to display.
            subscription_required_message_domain: messages                # Translation domain of flash message.
        trial_role: ROLE_PLAN_TRIAL               # Role name to identify a trial plan subscriber.
        trial_duration: +14 days                  # PHP DateTime modifier string, indicating the duration of a trial
        object_manager: doctrine_mongodb.odm.default_document_manager     # Service ID of the object manager responsible for persisting user instances.

To use the included HTML templates, add your Stripe public key to Twig's global variables:

    twig:
        globals:
            stripe_public_key: "%advancingu_stripe_subscription.keys.public%"

To use the security exception listener, add the listener to your firewall in ``security.yml``:

    security:
        firewalls:
            my_firewall:
                access_denied_handler: advancingu_stripe_subscription.access_denied_listener

Usage
-----

Note: Plan role names must start with ``ROLE_``, otherwise they will not be picked up by Symfony's default security voters.

Tip: Use role hierarchies to define incremental plans.

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE
