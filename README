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

The bundle requires Symfony 2.2 or greater and the Stripe PHP API. 
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
            my_default_plan:     # The plan's ID as defined with Stripe.
                i18nKey: plan.default.name   # Key inside translation domain for a plan.
                messageDomain: messages      # Translation domain to use for plan name.
                price: 2000      # In cents.
                currency: CAD    # Three character ISO currency code. Must be supported by your Stripe account.

To use the included HTML templates, add your Stripe public key to Twig's global variables:

    twig:
        globals:
            stripe_public_key: "%advancingu_stripe_subscription.keys.public%"

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE
