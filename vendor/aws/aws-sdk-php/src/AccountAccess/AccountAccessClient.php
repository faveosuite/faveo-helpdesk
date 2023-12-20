<?php
namespace Aws\AccountAccess;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Account Access** service.
 * @method \Aws\Result createApplication(array $args = [])
 * @phpstan-method \Aws\Result createApplication(array{
 *     identitySource?: array{identityCenter?: array{instanceArn?: string, ...}, ...},
 *     tags?: array<string, string>,
 *     ...,
 * } $args = [])
 * @method \GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise createApplicationAsync(array{
 *     identitySource?: array{identityCenter?: array{instanceArn?: string, ...}, ...},
 *     tags?: array<string, string>,
 *     ...,
 * } $args = [])
 * @method \Aws\Result createEntitlement(array $args = [])
 * @phpstan-method \Aws\Result createEntitlement(array{
 *     applicationArn?: string,
 *     entitlement?: array{principalRole?: array{principal?: array, roleArn?: string, ...}, ...},
 *     ...,
 * } $args = [])
 * @method \GuzzleHttp\Promise\Promise createEntitlementAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise createEntitlementAsync(array{
 *     applicationArn?: string,
 *     entitlement?: array{principalRole?: array{principal?: array, roleArn?: string, ...}, ...},
 *     ...,
 * } $args = [])
 * @method \Aws\Result deleteApplication(array $args = [])
 * @phpstan-method \Aws\Result deleteApplication(array{applicationArn?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise deleteApplicationAsync(array{applicationArn?: string, ...} $args = [])
 * @method \Aws\Result deleteEntitlement(array $args = [])
 * @phpstan-method \Aws\Result deleteEntitlement(array{applicationArn?: string, entitlementId?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEntitlementAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise deleteEntitlementAsync(array{applicationArn?: string, entitlementId?: string, ...} $args = [])
 * @method \Aws\Result getApplication(array $args = [])
 * @phpstan-method \Aws\Result getApplication(array{applicationArn?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise getApplicationAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise getApplicationAsync(array{applicationArn?: string, ...} $args = [])
 * @method \Aws\Result getEntitlement(array $args = [])
 * @phpstan-method \Aws\Result getEntitlement(array{applicationArn?: string, entitlementId?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise getEntitlementAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise getEntitlementAsync(array{applicationArn?: string, entitlementId?: string, ...} $args = [])
 * @method \Aws\Result listApplications(array $args = [])
 * @phpstan-method \Aws\Result listApplications(array{maxResults?: int, nextToken?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise listApplicationsAsync(array{maxResults?: int, nextToken?: string, ...} $args = [])
 * @method \Aws\Result listEntitlements(array $args = [])
 * @phpstan-method \Aws\Result listEntitlements(array{
 *     applicationArn?: string,
 *     filter?: array{principalRole?: array{principal?: array, roleArn?: string, account?: string, ...}, ...},
 *     nextToken?: string,
 *     maxResults?: int,
 *     ...,
 * } $args = [])
 * @method \GuzzleHttp\Promise\Promise listEntitlementsAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise listEntitlementsAsync(array{
 *     applicationArn?: string,
 *     filter?: array{principalRole?: array{principal?: array, roleArn?: string, account?: string, ...}, ...},
 *     nextToken?: string,
 *     maxResults?: int,
 *     ...,
 * } $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @phpstan-method \Aws\Result listTagsForResource(array{resourceArn?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array{resourceArn?: string, ...} $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @phpstan-method \Aws\Result tagResource(array{resourceArn?: string, tags?: array<string, string>, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise tagResourceAsync(array{resourceArn?: string, tags?: array<string, string>, ...} $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @phpstan-method \Aws\Result untagResource(array{resourceArn?: string, tagKeys?: list<string>, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise untagResourceAsync(array{resourceArn?: string, tagKeys?: list<string>, ...} $args = [])
 */
class AccountAccessClient extends AwsClient {}
