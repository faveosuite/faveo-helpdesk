<?php
namespace Aws\EKSAuth;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon EKS Auth** service.
 * @method \Aws\Result assumeRoleForPodIdentity(array $args = [])
 * @phpstan-method \Aws\Result assumeRoleForPodIdentity(array{clusterName?: string, token?: string, eksNodeName?: string, instanceId?: string, zone?: string, ...} $args = [])
 * @method \GuzzleHttp\Promise\Promise assumeRoleForPodIdentityAsync(array $args = [])
 * @phpstan-method \GuzzleHttp\Promise\Promise assumeRoleForPodIdentityAsync(array{clusterName?: string, token?: string, eksNodeName?: string, instanceId?: string, zone?: string, ...} $args = [])
 */
class EKSAuthClient extends AwsClient {}
