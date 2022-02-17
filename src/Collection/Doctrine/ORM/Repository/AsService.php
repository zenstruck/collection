<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

/**
 * For use with Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface.
 * Will make your repositories "auto-wireable".
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait AsService
{
    private ?ManagerRegistry $managerRegistry = null;
    private ?EntityManagerInterface $em = null;

    /**
     * @required
     */
    final public function setManagerRegistry(ManagerRegistry $managerRegistry): void
    {
        $this->managerRegistry = $managerRegistry;
    }

    final protected function em(): EntityManagerInterface
    {
        if ($this->em) {
            return $this->em;
        }

        if (!$this instanceof ObjectRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, ObjectRepository::class));
        }

        $em = $this->managerRegistry()->getManagerForClass($this->getClassName());

        if (!$em instanceof EntityManagerInterface) {
            throw new \RuntimeException(\sprintf('EntityManager for "%s" not found. Is this entity configured?', $this->getClassName()));
        }

        return $this->em = $em;
    }

    private function managerRegistry(): ManagerRegistry
    {
        if (!$this->managerRegistry) {
            throw new \RuntimeException(\sprintf('Doctrine was not set - is doctrine installed and is "%s" autowired?', static::class));
        }

        return $this->managerRegistry;
    }
}
