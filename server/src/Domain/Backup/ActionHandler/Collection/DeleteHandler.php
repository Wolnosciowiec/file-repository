<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class DeleteHandler
{
    private CollectionManager $manager;

    public function __construct(CollectionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param DeleteForm                  $form
     * @param CollectionManagementContext $securityContext
     *
     * @return ?CrudResponse
     *
     * @throws \Exception
     * @throws AuthenticationException
     */
    public function handle(DeleteForm $form, CollectionManagementContext $securityContext): ?CrudResponse
    {
        if (!$form->collection) {
            return null;
        }

        $this->assertHasRights($securityContext, $form);
        $this->manager->delete($form->collection);

        return CrudResponse::deletionSuccessfulResponse($form->collection);
    }

    /**
     * Submit changes to the persistent storage eg. database
     */
    public function flush(): void
    {
        $this->manager->flush();
    }

    /**
     * @param CollectionManagementContext $securityContext
     * @param DeleteForm                  $form
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, DeleteForm $form): void
    {
        if (!$securityContext->canDeleteCollection($form)) {
            throw AuthenticationException::fromDeletionProhibited();
        }
    }
}
