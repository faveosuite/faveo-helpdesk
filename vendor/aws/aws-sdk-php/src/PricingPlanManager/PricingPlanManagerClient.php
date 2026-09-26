<?php
namespace Aws\PricingPlanManager;

use Aws\AwsClient;

/**
 * This client is used to interact with the **PricingPlanManager** service.
 * @method \Aws\Result approvePaidSubscription(array $args = [])
 * @phpstan-method \Aws\Result approvePaidSubscription(array{arn?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise approvePaidSubscriptionAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise approvePaidSubscriptionAsync(array{arn?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \Aws\Result associateResourcesToSubscription(array $args = [])
 * @phpstan-method \Aws\Result associateResourcesToSubscription(array{arn?: string, resourceArns?: list<string>, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise associateResourcesToSubscriptionAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise associateResourcesToSubscriptionAsync(array{arn?: string, resourceArns?: list<string>, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \Aws\Result cancelSubscription(array $args = [])
 * @phpstan-method \Aws\Result cancelSubscription(array{arn?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelSubscriptionAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise cancelSubscriptionAsync(array{arn?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \Aws\Result cancelSubscriptionChange(array $args = [])
 * @phpstan-method \Aws\Result cancelSubscriptionChange(array{arn?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelSubscriptionChangeAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise cancelSubscriptionChangeAsync(array{arn?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \Aws\Result createSubscription(array $args = [])
 * @phpstan-method \Aws\Result createSubscription(array{
 *     planFamily?: string,
 *     planTier?: string,
 *     usageLevel?: string,
 *     resourceArns?: list<string>,
 *     approvalMode?: 'IMMEDIATE'|'MANUAL',
 *     clientToken?: string,
 *     ...,
 * } $args = [])
 * @method \GuzzleHttp\Promise\Promise createSubscriptionAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise createSubscriptionAsync(array{
 *     planFamily?: string,
 *     planTier?: string,
 *     usageLevel?: string,
 *     resourceArns?: list<string>,
 *     approvalMode?: 'IMMEDIATE'|'MANUAL',
 *     clientToken?: string,
 *     ...,
 * } $args = [])
 * @method \Aws\Result disassociateResourcesFromSubscription(array $args = [])
 * @phpstan-method \Aws\Result disassociateResourcesFromSubscription(array{arn?: string, resourceArns?: list<string>, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise disassociateResourcesFromSubscriptionAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise disassociateResourcesFromSubscriptionAsync(array{arn?: string, resourceArns?: list<string>, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \Aws\Result getSubscription(array $args = [])
 * @phpstan-method \Aws\Result getSubscription(array{arn?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise getSubscriptionAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise getSubscriptionAsync(array{arn?: string, ...} $args = [])
 * @method \Aws\Result listSubscriptions(array $args = [])
 * @phpstan-method \Aws\Result listSubscriptions(array{nextToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise listSubscriptionsAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise listSubscriptionsAsync(array{nextToken?: string, ...} $args = [])
 * @method \Aws\Result updateSubscription(array $args = [])
 * @phpstan-method \Aws\Result updateSubscription(array{arn?: string, planTier?: string, usageLevel?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise updateSubscriptionAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise updateSubscriptionAsync(array{arn?: string, planTier?: string, usageLevel?: string, ifMatch?: string, clientToken?: string, ...} $args = [])
 */
class PricingPlanManagerClient extends AwsClient {}
