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
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\Order;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Symfony\Component\VarExporter\LazyGhostTrait;
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

        if (\class_exists(DefaultSchemaManagerFactory::class)) {
            $connectionConfig->setSchemaManagerFactory(new DefaultSchemaManagerFactory());
        }

        $ormConfig = \PHP_VERSION_ID >= 80400 && \method_exists(ORMSetup::class, 'createConfig')
            ? ORMSetup::createConfig(true)
            : ORMSetup::createConfiguration(true);
        $ormConfig->setMetadataDriverImpl(new AttributeDriver([], true));

        if (\PHP_VERSION_ID >= 80400 && \method_exists($ormConfig, 'enableNativeLazyObjects')) {
            $ormConfig->enableNativeLazyObjects(true);
        }

        if (\method_exists($ormConfig, 'setLazyGhostObjectEnabled') && \trait_exists(LazyGhostTrait::class)) {
            $ormConfig->setLazyGhostObjectEnabled(true);
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

    protected function createCriteria(?Expression $expression = null, ?string $descendingBy = null): Criteria
    {
        if (\is_a(ReadableCollection::class, Selectable::class, true)) {
            return new Criteria($expression, $descendingBy === null ? null : [$descendingBy => \SortDirection::Descending]);
        }

        $orderings = $descendingBy === null ? null : [$descendingBy => \enum_exists(Order::class) ? Order::Descending : 'DESC'];

        return \method_exists(Criteria::class, 'isRawFieldValueAccessEnabled')
            ? new Criteria($expression, $orderings, firstResult: 0, accessRawFieldValues: true)
            : new Criteria($expression, $orderings);
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
