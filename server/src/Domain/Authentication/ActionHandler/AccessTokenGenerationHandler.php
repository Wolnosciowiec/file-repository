<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Exception\UserNotFoundException;
use App\Domain\Authentication\Factory\JWTFactory;
use App\Domain\Authentication\Form\AccessTokenGenerationForm;
use App\Domain\Authentication\Response\TokenGenerationResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Authentication\Service\Security\AccessTokenAuditRecorder;

/**
 * Access Token Generation
 * =======================
 *   Handles API access key generation for programmatic access.
 *   Tokens generated by this endpoint can live for long time and be limited by permissions scope
 */
class AccessTokenGenerationHandler
{
    private JWTFactory $factory;
    private AccessTokenAuditRecorder $recorder;

    public function __construct(JWTFactory $factory, AccessTokenAuditRecorder $recorder)
    {
        $this->factory = $factory;
        $this->recorder = $recorder;
    }

    /**
     * @param AccessTokenGenerationForm $form
     * @param AuthenticationManagementContext $context
     *
     * @return TokenGenerationResponse
     *
     * @throws AuthenticationException
     * @throws UserNotFoundException
     */
    public function handle(AccessTokenGenerationForm $form, AuthenticationManagementContext $context): TokenGenerationResponse
    {
        $this->assertHasRights($context, $form->requestedPermissions);

        $token = $this->factory->createForUser($context->getUser(), $form->requestedPermissions, $form->ttl);
        $this->recorder->record($token);

        return TokenGenerationResponse::create($token);
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param array $requestedRoles
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context, array $requestedRoles): void
    {
        if (!$context->canGenerateJWTWithSelectedPermissions($requestedRoles)) {
            throw AuthenticationException::fromForbiddenToGenerateTokenWithMoreRolesThanUserHave();
        }
    }
}