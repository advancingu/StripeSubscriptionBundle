<?php

/*
 * This file is part of the AdvancinguStripeSubscriptionBundle package.
 *
 * (c) 2013 Markus Weiland <mw@graph-ix.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Advancingu\StripeSubscriptionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('advancingu_stripe_subscription');

        $rootNode
            ->children()
                ->scalarNode('stripe_public_key')
                    ->isRequired()
                    ->defaultNull()
                ->end()
                ->scalarNode('stripe_secret_key')
                    ->isRequired()
                    ->defaultNull()
                ->end()
                ->scalarNode('payee_name')
                    ->defaultNull()
                ->end()
                ->arrayNode('plans')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('i18nKey')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('messageDomain')
                                ->defaultValue('messages')
                                ->cannotBeEmpty()
                            ->end()
                            ->integerNode('price')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->min(0)
                                ->max(100000)
                            ->end()
                            ->scalarNode('currency')
                                ->defaultValue('USD')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('role')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('subscription_check')
                    ->children()
                        ->scalarNode('subscribe_route')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('subscription_required_i18nKey')
                            ->defaultValue('subscription.plan_required')
                        ->end()
                        ->scalarNode('subscription_required_message_domain')
                            ->defaultValue('messages')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('trial_role')
                    ->defaultValue('ROLE_PLAN_TRIAL')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('object_manager')
                    ->defaultValue('doctrine_mongodb.odm.default_document_manager')
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ;
        
        return $treeBuilder;
    }
}
