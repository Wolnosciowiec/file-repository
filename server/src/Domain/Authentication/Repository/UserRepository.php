<?php declare(strict_types=1);

namespace App\Domain\Authentication\Repository;

use App\Domain\Authentication\Entity\User;

interface UserRepository extends \App\Domain\Common\Repository\UserRepository
{
    public function persist(User $user): void;
    public function remove(User $user): void;
    public function flush(): void;

    /**
     * @return User[]
     */
    public function getExpiredUserAccounts(): array;

    /**
     * @param string $pattern
     * @param int $page
     * @param int $count
     * @param bool $searchById
     *
     * @return User[]
     */
    public function findUsersBy(string $pattern, int $page = 1, int $count = 50, bool $searchById = true): array;

    public function findMaxPagesOfUsersBy(string $pattern, int $limit = 50): int;

    public function findOneByEmail(string $email): ?User;

    public function findOneById(string $id): ?User;
}
