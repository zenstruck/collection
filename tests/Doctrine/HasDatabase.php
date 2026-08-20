<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Middleware\DebugMiddleware;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Relation;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Shape;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait HasDatabase
{
    protected EntityManager $em;
    private DebugDataHolder $debugDataHolder;

    /**
     * @before
     */
    protected function setupEntityManager(): void
    {
        $connectionConfig = new Configuration();
        $connectionConfig->setMiddlewares([new DebugMiddleware($this->debugDataHolder = new DebugDataHolder(), null)]);

        $ormConfig = ORMSetup::createAttributeMetadataConfiguration([], true);

        if (\PHP_VERSION_ID > 80400) {
            $ormConfig->enableNativeLazyObjects(true);
        }

        $this->em = new EntityManager(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $connectionConfig),
            $ormConfig,
        );

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->createSchema([
            $this->em->getClassMetadata(Entity::class),
            $this->em->getClassMetadata(Relation::class),
            $this->em->getClassMetadata(Shape::class),
        ]);
    }

    /**
     * @after
     */
    protected function teardownEntityManager(): void
    {
        unset($this->em, $this->debugDataHolder);
    }

    protected function assertQueryCount(int $expected, callable $callback): void
    {
        $this->debugDataHolder->reset();

        $callback();

        $queries = $this->debugDataHolder->getData()['default'] ?? [];

        $this->assertCount($expected, $queries, \sprintf('Expected %d queries but got %d.', $expected, $queries));
    }

    protected function persistEntities(int $count): void
    {
        $this->setupEntityManager();

        for ($i = 0; $i < $count; ++$i) {
            $this->em->persist(new Entity('value '.($i + 1)));
        }

        $this->flushAndClear();
    }

    protected function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
    }
}
