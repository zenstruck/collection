# zenstruck/collection

[![CI](https://github.com/zenstruck/collection/actions/workflows/ci.yml/badge.svg)](https://github.com/zenstruck/collection/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/zenstruck/collection/graph/badge.svg?token=SD4WLSHY7X)](https://codecov.io/gh/zenstruck/collection)

A `Collection` interface for iterating, paginating and filtering _any_ set of data - arrays, iterators,
`doctrine/collections` instances and Doctrine ORM queries - all behind the same API:

```php
use function Zenstruck\collect;

$page = collect($users) // array, iterable, closure or doctrine collection
    ->filter(fn(User $user) => $user->isActive())
    ->map(fn(User $user) => $user->email())
    ->paginate(page: 2, limit: 10)
;

\count($page);       // 10 (the number of items on this page)
$page->totalCount(); // 79 (the total number of items)
$page->lastPage();   // 8

foreach ($page as $email) {
    // ...
}
```

It really shines with **Doctrine ORM**, where a custom `EntityResult` object makes everything _lazy by
default_ - no more loading huge amounts of entities into memory at once:

```php
use Zenstruck\Collection\Doctrine\ORM\EntityResult;

$qb = $em->createQueryBuilder()
    ->select('p')
    ->from(Post::class, 'p')
    ->where('p.status = :status')
    ->setParameter('status', 'published')
;

$published = new EntityResult($qb); // nothing has been executed yet
```

An `EntityResult` is an unexecuted query you can pass around and derive from. It is lazy and immutable, so
each of these runs its own optimized query:

```php
\count($published);                       // SELECT COUNT(...)
$published->first();                      // ...LIMIT 1
$published->paginate(page: 2, limit: 10); // a single, paginated query
$published->asArray('id', 'title')->first(); // ['id' => 1, 'title' => '...'] - no entity hydrated

foreach ($published as $post) {
    // ...
}
```

Features:

1. [**Collections**](#collections): A lazy, countable, paginatable `Collection` interface with implementations
   for arrays, iterables and callables.
2. [**Pagination**](#pagination): Paginate any collection, with page metadata and lazy page-by-page iteration.
3. [**Doctrine**](#doctrine):
   1. [**ORM**](#orm): Lazy, specification-driven repositories with DTO/scalar hydration.
   2. [**Batch Processing**](#batch-processing): Memory-safe iteration/mutation of large result sets.
   3. [**Collection Bridge**](#collection-bridge): Filter and paginate `doctrine/collections` instances -
      including relations - without initializing them.
   4. [**Specifications**](#specifications): Express filters/sorts as objects that are converted into native
      queries.
4. [**Symfony Integration**](#symfony-integration): Autowire a lazy-first repository for any entity.
5. [**Static Analysis**](#static-analysis): Fully generic - PHPStan knows what your collections contain.

## Installation

```bash
composer require zenstruck/collection
```

The core `Collection` API has no dependencies. The following packages unlock the optional integrations:

| Package                       | Required for                                                    |
|-------------------------------|-----------------------------------------------------------------|
| `doctrine/orm` (>=2.20.7)     | [ORM](#orm) and [Batch Processing](#batch-processing)            |
| `doctrine/collections`        | [Collection Bridge](#collection-bridge)                         |
| `doctrine/doctrine-bundle`    | [Symfony Integration](#symfony-integration)                     |
| `symfony/expression-language` | The [`#[ForObject]`](#symfony-integration) autowiring attribute  |
| `pagerfanta/pagerfanta`       | [`PagerfantaAdapter`](#pagerfanta)                              |

## Collections

`Zenstruck\Collection` is the interface everything in this package implements. It is an `IteratorAggregate`
and `Countable` that adds transformation ([`filter()`](#filtering), `map()`, `keyBy()`), reduction (`first()`,
`find()`, `reduce()`) and [pagination](#pagination) - and, where the implementation allows, does all of it
_lazily_.

### Creating

The `Zenstruck\collect()` function wraps any source in the most appropriate implementation:

```php
use function Zenstruck\collect;

collect(['a', 'b']);                 // ArrayCollection
collect(new \ArrayIterator(['a']));  // LazyCollection
collect(fn() => fetch_rows());       // LazyCollection (callback not executed yet)
collect($doctrineCollection);        // DoctrineBridgeCollection
collect();                           // empty LazyCollection
```

| Source                                    | Implementation                              |
|-------------------------------------------|---------------------------------------------|
| `array`                                   | [`ArrayCollection`](#arraycollection)       |
| `Traversable`                             | [`LazyCollection`](#lazycollection)         |
| `callable(): iterable`                    | [`LazyCollection`](#lazycollection)         |
| `Doctrine\Common\Collections\Collection`  | [Collection Bridge](#collection-bridge)     |
| `Zenstruck\Collection`                    | itself (returned as-is)                     |
| `null`                                    | empty [`LazyCollection`](#lazycollection)   |

Each implementation can also be constructed directly if you want a specific one.

### The API

```php
/** @var Zenstruck\Collection<Post> $posts */

// transformations - each returns a new Collection, the original is untouched
$posts->filter(fn(Post $post) => $post->isPublished());
$posts->map(fn(Post $post) => $post->title());
$posts->keyBy(fn(Post $post) => $post->id());
$posts->take(10);    // first 10
$posts->take(10, 5); // 10, starting at offset 5

// reductions
$posts->first();                                          // first item or null
$posts->first($default);                                  // first item or $default
$posts->find(fn(Post $post) => $post->isPublished());     // first match or null
$posts->reduce(fn(int $count, Post $post) => $count + 1, 0);
$posts->isEmpty();
\count($posts);

foreach ($posts as $post) {
    // ...
}

$posts->eager();     // load everything into an ArrayCollection
$posts->paginate();  // see "Pagination" below
$posts->dump();      // dump the items and return $this
$posts->dd();        // dump the items and die
```

#### Filtering

`filter()` and `find()` take a `callable(V,K):bool`:

```php
$posts->filter(fn(Post $post, int $key) => $post->isPublished());
```

Doctrine-backed collections additionally accept [specification objects](#specifications), which are converted
into a real query instead of filtering in PHP.

> [!NOTE]
> Specifications are only understood by Doctrine-backed collections. Passing one to `ArrayCollection`,
> `LazyCollection` or any other in-memory implementation throws
> `Zenstruck\Collection\Exception\InvalidSpecification`.

### Lazy vs Eager

`LazyCollection` (and the Doctrine implementations) do no work until iterated. Transformations _stay_ lazy -
they wrap the source rather than run it:

```php
$titles = collect(fn() => fetch_rows()) // nothing has run yet
    ->filter(fn(array $row) => $row['published'])
    ->map(fn(array $row) => $row['title'])
    ->take(10)
; // still nothing has run

foreach ($titles as $title) {
    // NOW the source is iterated - and stops after 10 matches
}
```

| Method                                    | Runs the source?                       | How much it reads                     |
|-------------------------------------------|----------------------------------------|---------------------------------------|
| `filter()`, `map()`, `keyBy()`, `take()`  | No - returns a new lazy collection     | Nothing                               |
| `first()`                                 | Yes                                    | Stops at the first item               |
| `find()`                                  | Yes                                    | Stops at the first match              |
| `count()`, `isEmpty()`                    | Only if the source isn't `Countable`   | Counts, keeping nothing               |
| `reduce()`                                | Yes                                    | All of it, keeping nothing            |
| `eager()`                                 | Yes                                    | All of it, kept in memory             |

> [!IMPORTANT]
> "Runs the source" is not the same as "loads the source". Only `eager()` (and `ArrayCollection`, which is
> array-backed to begin with) holds the whole collection in memory - everything else streams one item at a
> time. That's also why `eager()` is useful: it lets you iterate repeatedly without re-running an expensive
> source.

> [!WARNING]
> Generators can't be rewound, so `LazyCollection` rejects them outright - wrap in a closure instead:
> ```php
> new LazyCollection($generator);        // throws \InvalidArgumentException
> new LazyCollection(fn() => $items());  // ok - re-invoked on each iteration
> ```
> A closure returning an `array`/`Traversable` is only executed once and cached; a closure returning a
> _generator_ is re-executed every time the collection is iterated.

> [!TIP]
> `count()` on a source that isn't `Countable` has to iterate all of it. If you have a cheaper way to count,
> use [`CallbackCollection`](#composing-collections).

### ArrayCollection

An eager, immutable, array-backed implementation with a much larger API. "Mutations" (`set()`, `unset()`,
`push()`) return a new instance:

```php
use Zenstruck\Collection\ArrayCollection;

$collection = new ArrayCollection(['a' => 1, 'b' => 2]);

$collection->set('c', 3); // new instance, $collection is unchanged
```

Named constructors:

| Constructor                                     | Description                                            |
|-------------------------------------------------|--------------------------------------------------------|
| `ArrayCollection::for($source)`                 | Same as the constructor, but chainable                 |
| `ArrayCollection::wrap($value)`                 | Wraps a non-iterable in an array (`null` => empty)     |
| `ArrayCollection::explode(',', 'a,b')`          | Via `explode()` (`''` normalizes to empty)             |
| `ArrayCollection::range(1, 10)`                 | Via `range()`                                          |
| `ArrayCollection::fill(0, 5, 'x')`              | Via `array_fill()`                                     |

In addition to the `Collection` API:

| Method                        | Description                                                          |
|-------------------------------|----------------------------------------------------------------------|
| `all()`                       | The underlying `array`                                               |
| `get($key, $default = null)`  | Value for `$key`                                                     |
| `has($key)` / `contains($v)`  | Key exists / value exists (strict)                                   |
| `keys()` / `values()`         | Keys as values / re-indexed values                                   |
| `set($key, $value)`           | New instance with `$key` set                                         |
| `unset(...$keys)`             | New instance without `$keys`                                         |
| `only(...$keys)`              | New instance with _only_ `$keys`                                     |
| `push(...$values)`            | New instance with `$values` appended                                 |
| `merge(...$collections)`      | Via `array_merge()`                                                  |
| `slice($offset, $length)`     | Preserves keys                                                       |
| `reverse()`                   | Preserves keys                                                       |
| `groupBy($function)`          | Group into a collection of lists                                     |
| `combine($values)`            | Use the items as keys for `$values`                                  |
| `combineWithSelf()`           | Use the items as both keys and values                                |
| `implode($separator = '')`    | Join into a string                                                   |
| `sort()` / `sortDesc()`       | By value, optional flags or comparator                               |
| `sortBy($function)`           | By a computed value                                                  |
| `sortByDesc($function)`       | By a computed value, reversed                                        |
| `sortKeys()` / `sortKeysDesc()` | By key                                                             |

`map()` and `filter()` preserve keys. `keyBy()` and `groupBy()` accept `Stringable` keys and cast them to
string.

### LazyCollection

Wraps a `Traversable` or a `callable(): iterable`. This is what you want for anything expensive - a generator
over a large file, an HTTP paginator, a database cursor:

```php
use Zenstruck\Collection\LazyCollection;

$users = new LazyCollection(function() {
    $page = 1;

    while ($response = $api->get('/users', ['page' => $page++])) {
        yield from $response->toArray();
    }
});

$users->take(50); // only fetches as many pages as needed
```

### Composing Collections

| Class                  | Purpose                                                                          |
|------------------------|-----------------------------------------------------------------------------------|
| `ChainCollection`      | Iterate multiple collections as one                                              |
| `CallbackCollection`   | Separate callbacks for iterating and counting                                    |
| `FactoryCollection`    | Lazily pass each item of another collection through a factory                    |

```php
use Zenstruck\Collection\CallbackCollection;
use Zenstruck\Collection\ChainCollection;
use Zenstruck\Collection\FactoryCollection;

use function Zenstruck\collect;

new ChainCollection([$collection1, $collection2]);       // keys are discarded
new ChainCollection([$collection1, $collection2], true); // keys are preserved

// count without iterating
new CallbackCollection(fn() => $api->results(), fn() => $api->totalCount());

// $post is only created for items you actually iterate over
new FactoryCollection(collect($rows), fn(array $row) => Post::fromArray($row));
```

> [!NOTE]
> `FactoryCollection` decorates another `Collection`, so wrap plain iterables in `collect()` first.

> [!WARNING]
> When preserving keys with `ChainCollection`, duplicate keys across the inner collections will overwrite each
> other if the result is converted to an array (ie via `eager()`).

## Pagination

Any collection can be paginated with `paginate()`, which returns a `Page` - an iterable of just that page's
items, plus the metadata you need to render a pager:

```php
/** @var Zenstruck\Collection<Post> $posts */

$page = $posts->paginate();                  // page 1, 20 per page
$page = $posts->paginate(page: 3, limit: 50);

foreach ($page as $post) {
    // only the 50 posts on page 3
}
```

The items are fetched once and cached, so iterating the same `Page` more than once won't re-run the source.

| Method                          | Description                                                        |
|---------------------------------|--------------------------------------------------------------------|
| `currentPage()`                 | The current page number                                            |
| `limit()`                       | Items per page                                                     |
| `count()`                       | Number of items on _this_ page                                     |
| `totalCount()`                  | Number of items in the entire collection                           |
| `firstPage()`                   | Always `1`                                                         |
| `lastPage()` / `pageCount()`    | The last page number (`1` when empty)                              |
| `nextPage()` / `previousPage()` | The adjacent page number, or `null` at the boundary                |
| `haveToPaginate()`              | Whether there is more than one page                                |

> [!NOTE]
> Out of range arguments are normalized rather than rejected: a page less than `1` becomes `1` and a limit
> less than `1` becomes the default (`20`).

### Strict Mode

By default, `currentPage()` returns whatever page you asked for, even if it's past the end. Enable strict mode
to clamp it to the last page instead:

```php
$page = $posts->paginate(page: 999)->strict();

$page->currentPage(); // 4 (the last page) instead of 999
```

> [!IMPORTANT]
> Strict mode is worth it when the page number comes from user input and you don't want an empty page for
> `?page=999`. The tradeoff is that determining the last page requires counting the collection - an extra
> query for Doctrine sources.

### Iterating Pages

`pages()` returns a `Pages` object - a lazy, page-by-page view of the entire collection. Each page is a
separate query, so this is a memory-safe way to walk a large result set:

```php
foreach ($posts->pages(100) as $page) {
    foreach ($page as $post) {
        // ...
    }
}

$pages = $posts->pages(100);

$pages->get(3);  // the Page for page 3
\count($pages);  // the number of pages (0 when the collection is empty)
```

> [!NOTE]
> `count()` on `Pages` is the number of _pages_, while `count()` on a `Page` is the number of _items_ on that
> page. Use `Page::totalCount()` for the total number of items.

### Pagerfanta

If you'd rather render pagers with [Pagerfanta](https://github.com/BabDev/Pagerfanta), any collection can be
adapted:

```php
use Pagerfanta\Pagerfanta;
use Zenstruck\Collection\Pagerfanta\PagerfantaAdapter;

$pagerfanta = new Pagerfanta(new PagerfantaAdapter($posts));
```

## Doctrine

### ORM

#### EntityResult

`EntityResult` is a `Collection` that wraps a query builder. Nothing is executed until you ask for something,
and every method that narrows or transforms it returns a new instance - the original is reusable.

```php
use Zenstruck\Collection\Doctrine\ORM\EntityResult;
use Zenstruck\Collection\Doctrine\ORM\EntityResultQueryBuilder;

// wrap any query builder
$result = new EntityResult($qb);

// ...or use the one that can create the result itself
$result = EntityResultQueryBuilder::forEntity($em, Post::class, 'p')
    ->where('p.status = :status')
    ->setParameter('status', 'published')
    ->result()
;
```

`EntityResultQueryBuilder` extends Doctrine's `QueryBuilder`, so everything you already know still works. It
adds:

| Method                          | Description                                                       |
|---------------------------------|-------------------------------------------------------------------|
| `result()`                      | Create the `EntityResult`                                         |
| `readonly()`                    | Don't track the results in the identity map                       |
| `cacheResult($lifetime, $key)`  | Enable the result cache                                           |
| `modifyQuery($callable)`        | Adjust the `Query` before it runs (query hints, etc.)             |

> [!WARNING]
> Iterating an `EntityResult` streams the rows one at a time but never clears the entity manager, so every
> entity it hydrates stays in memory. For large result sets, use
> [`batchIterate()`/`batchProcess()`](#batch-processing) instead.

##### Executing

An `EntityResult` is a query _definition_. It runs when you ask it for something, and each of these runs its
own query, tailored to what you asked:

```php
\count($result);          // SELECT COUNT(...)
$result->first();         // ...LIMIT 1 (or null when there are no rows)
$result->first($default); // ...or your default
$result->paginate();      // one paginated query - see "Pagination"
$result->take(10, 20);    // 10 rows, starting at offset 20
$result->eager();         // everything, as an ArrayCollection
$result->isEmpty();

foreach ($result as $post) {
    // ...
}
```

Because it's immutable, deriving is free and the original stays usable:

```php
$recent = $result->filter(Spec::gt('publishedAt', $cutoff)); // $result is unchanged
```

##### Hydration

By default you get entities back - what the query selects, hydrated the way Doctrine normally would. These
methods return a new `EntityResult` that hydrates each row differently instead:

| Method                             | Each row becomes                                     |
|------------------------------------|------------------------------------------------------|
| `asArray(...$fields)`              | `array<string,mixed>`, limited to `$fields` if given |
| `asScalar($field = null)`          | `bool\|float\|int\|string`                           |
| `asString()`/`asInt()`/`asFloat()` | The scalar, cast to that type                        |
| `as($callable)`                    | Whatever `$callable` returns                         |

```php
$result->asArray();              // ['id' => 1, 'title' => 'My Post', ...]
$result->asArray('id', 'title'); // ['id' => 1, 'title' => 'My Post']
$result->asInt('id');            // 1, 2, 3...
```

`as()` is the general case: it hands you each row and uses whatever you return. What a "row" is depends on
what the query selects and which of the above you combined it with - an entity, an array, or a scalar:

```php
// entities in, DTOs out
$dtos = $result->as(fn(Post $post) => PostDto::from($post));

// only select what the DTO needs, and never hydrate an entity at all
$dtos = EntityResultQueryBuilder::forEntity($em, Post::class, 'p')
    ->select('p.id, p.title')
    ->result()
    ->as(fn(array $row) => new PostDto($row['id'], $row['title']))
;

// or let asArray() pick the fields
$dtos = $result
    ->asArray('id', 'title')
    ->as(fn(array $row) => new PostDto(...$row))
;
```

The modifier applies everywhere the result produces values - iteration, `first()`, `take()`, `eager()`,
`paginate()` and [batch processing](#batch-processing) all give you `PostDto` objects.

> [!WARNING]
> There is only one modifier slot: `as()` replaces anything already set, including the casts behind
> `asInt()`/`asFloat()`/`asString()` and the wrapper behind [`withAggregates()`](#aggregates). Combine `as()`
> with `asArray()` or a field-selecting query, not with those.

##### Single Values

A query that selects one aggregate value works the same way - ask for the scalar and take the first row:

```php
$total = EntityResultQueryBuilder::forEntity($em, Post::class, 'p')
    ->select('SUM(p.views)')
    ->result()
    ->asInt()
    ->first()
;
```

##### Write Queries

`EntityResultQueryBuilder` is a query builder like any other, so it can also carry a `DELETE` or an `UPDATE`.
`first()` executes it and returns the number of affected rows:

```php
$deleted = EntityResultQueryBuilder::forEntity($em, Post::class, 'p')
    ->delete()
    ->where('p.status = :status')
    ->setParameter('status', 'spam')
    ->result()
    ->asInt()
    ->first()
;

$updated = EntityResultQueryBuilder::forEntity($em, Post::class, 'p')
    ->update()
    ->set('p.status', ':status')
    ->setParameter('status', 'archived')
    ->where('p.publishedAt < :cutoff')
    ->setParameter('cutoff', $cutoff)
    ->result()
    ->asInt()
    ->first()
;
```

> [!WARNING]
> A write query has no rows to give you, so iterating one - or calling `eager()` on it - throws a
> `\LogicException`. Use `first()`. Note also that asking twice runs the query twice.

##### Readonly Results

`readonly()` detaches each entity from the entity manager as it's hydrated. Use it for anything you're only
going to read - nothing is tracked for changes, and nothing is flushed:

```php
foreach ($result->readonly() as $post) {
    // $post is not managed
}
```

##### Aggregates

When your query selects extra columns alongside the entity, `withAggregates()` wraps each row in an
`EntityWithAggregates`, which proxies to the entity and exposes the extra columns:

```php
$result = EntityResultQueryBuilder::forEntity($em, Post::class, 'p')
    ->leftJoin('p.comments', 'c')
    ->addSelect('COUNT(c.id) AS commentCount')
    ->groupBy('p.id')
    ->result()
    ->withAggregates()
;

foreach ($result as $post) {
    $post->title();      // proxied to the Post
    $post->commentCount; // the aggregate column
    $post->entity();     // the Post itself
    $post->aggregates(); // ['commentCount' => 12]
}
```

> [!WARNING]
> Only call `withAggregates()` when the query really does select extra columns - iterating throws a
> `\LogicException` otherwise. Doctrine can't iterate aggregate results directly, so they're chunked into
> groups of 20, each requiring an additional query.

##### Tuning Pagination

Counting and paginating go through Doctrine's `Paginator`, which is configurable:

```php
$result = $result->disableFetchJoins();     // faster when the query has no fetch-joined collections
$result = $result->disableOutputWalkers();
$result = $result->enableOutputWalkers();   // required for some queries (ie HAVING, complex ORDER BY)
```

Like everything else on an `EntityResult`, these return a new instance rather than changing the original.

> [!NOTE]
> Output walkers are disabled automatically when a hydration mode or `as()` modifier is set.

#### Repositories

`ObjectRepository` is this package's repository interface, and it is deliberately small: `find()` for a single
object, `filter()`/`query()` for an `EntityResult`, plus `count()` and iteration.

What's missing is the point. There is no `findAll()` and no `findBy()` - nothing in the API hands you an array
of entities, so a repository call can't be the thing that exhausts your memory. Anything that returns more
than one object returns a lazy `EntityResult` that you narrow, paginate or
[batch iterate](#batch-processing) before it ever touches the database:

```php
use Zenstruck\Collection\Spec;

/** @var Zenstruck\Collection\Doctrine\ObjectRepository<Post> $posts */

$posts->find(1);                     // a single Post, or null
$posts->find(['slug' => 'my-post']);

$published = $posts->filter(Spec::eq('status', 'published')); // no query yet

$published->paginate(page: 2);
$published->asArray('id', 'title');

\count($posts);

foreach ($posts as $post) {
    // ...
}
```

> [!TIP]
> `find()`, `filter()` and `query()` all accept [specifications](#specifications) - reusable filter objects
> that are converted into the query itself.

Three implementations are available - they differ only in what your repository _also_ is: nothing else, or a
Doctrine repository (in standard and Symfony-autowireable variants).

##### EntityRepository

The standalone implementation. Use it directly for any entity:

```php
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Spec;

$posts = new EntityRepository($em, Post::class);

$posts->find(1);
$posts->filter(Spec::eq('status', 'published'));
```

Or extend it for your custom repositories, passing the entity class up to the parent constructor:

```php
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityResult;

/**
 * @extends EntityRepository<Post>
 */
final class PostRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Post::class);
    }

    /**
     * @return EntityResult<Post>
     */
    public function published(): EntityResult
    {
        return $this->qb('p')
            ->where('p.status = :status')
            ->setParameter('status', 'published')
            ->result()
        ;
    }
}
```

> [!TIP]
> The protected `qb()` helper returns an [`EntityResultQueryBuilder`](#entityresult) already scoped to your
> entity, using the alias you pass it (`e` by default). It's a Doctrine `QueryBuilder`, so build the query
> however you like - `->result()` is waiting at the end of the chain.

> [!TIP]
> The protected `em()` helper gives you the entity manager, for anything the query builder can't do.

> [!IMPORTANT]
> This is _not_ a Doctrine repository. It doesn't extend `Doctrine\ORM\EntityRepository` and has none of its
> methods (`findAll()`, `findOneBy()`, `matching()`, ...) - just the `ObjectRepository` API above. If you want
> both, use one of the bridges below.

##### ORMEntityRepository

A Doctrine `EntityRepository` _and_ an `ObjectRepository`. Use it when you want the new API without giving up
the Doctrine one - `findOneBy()` and `filter()` both work:

```php
use Zenstruck\Collection\Doctrine\ORM\Bridge\ORMEntityRepository;

/**
 * @extends ORMEntityRepository<Post>
 */
final class PostRepository extends ORMEntityRepository
{
}
```

```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    // ...
}
```

`createQueryBuilder()` is overridden to return an `EntityResultQueryBuilder`, so `->result()` is available on
the query builders you already build.

> [!NOTE]
> If your repository already extends something else, add the `EntityRepositoryBridge` trait to it directly -
> that's all these bridge classes do.

##### ORMServiceEntityRepository

The same bridge, but extending DoctrineBundle's `ServiceEntityRepository` so the repository is autowireable in
a Symfony application:

```php
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Collection\Doctrine\ORM\Bridge\ORMServiceEntityRepository;

/**
 * @extends ORMServiceEntityRepository<Post>
 */
final class PostRepository extends ORMServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
}
```

Inject it like any other service - see [Symfony Integration](#symfony-integration) for autowiring a repository
for entities that don't have a repository class at all.

##### Finding

`find()` returns a single entity, or `null` if there isn't one:

```php
use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection\Spec;

$posts->find(1);                                   // by id
$posts->find(['slug' => 'my-post']);               // by criteria
$posts->find(Spec::eq('slug', 'my-post'));         // by specification
$posts->find(fn(QueryBuilder $qb, string $alias) => $qb->andWhere("{$alias}.views > 100"));
```

##### Filtering

`filter()` narrows the repository down to an `EntityResult` you can iterate, paginate or hydrate:

```php
$published = $posts->filter(Spec::eq('status', 'published'));
$published = $posts->filter(['status' => 'published']);
$everything = $posts->filter(null);

$published->paginate();
$published->asArray('id', 'title');
```

An `EntityResult` can be filtered further, but its specifications are converted to a `Criteria` rather than
applied to the query builder, so it understands a smaller set than the repository does:

```php
$published->filter(Spec::contains('title', 'symfony')); // ok
$published->filter(DoctrineSpec::delete());             // throws InvalidSpecification
```

##### Querying

`query()` accepts everything `filter()` does, and is the one to reach for when the specification changes the
database rather than narrowing a read:

```php
use Zenstruck\Collection\Doctrine\DoctrineSpec;

$deleted = $posts
    ->query(DoctrineSpec::andX(
        DoctrineSpec::lt('publishedAt', $cutoff),
        DoctrineSpec::delete(),
    ))
    ->first() // executes the DELETE and returns the number of affected rows
;
```

##### Invokable Objects

`find()`, `filter()` and `query()` also accept any invokable object, called with the query builder and the
root alias. It's a reusable, testable place to put a query you'd otherwise inline:

```php
use Doctrine\ORM\QueryBuilder;

final class Trending
{
    public function __invoke(QueryBuilder $qb, string $alias): void
    {
        $qb
            ->andWhere("{$alias}.views > 1000")
            ->addOrderBy("{$alias}.views", 'DESC')
        ;
    }
}

$posts->filter(new Trending());
$posts->find(new Trending());
```

> [!WARNING]
> This only applies to repositories. An [`EntityResult`](#entityresult) or a
> [bridged collection](#collection-bridge) treats an invokable object as a plain `callable(V,K):bool` and
> filters in PHP instead. Wrap it in [`Spec::callback()`](#callbacks) if you need it understood by both.

##### Iterating and Counting

```php
\count($posts);

foreach ($posts as $post) {
    // ...
}
```

> [!WARNING]
> Iterating a repository directly uses [batch iteration](#batch-processing): the entity manager is cleared
> every 100 entities and nothing is ever flushed. Don't hold on to entities from a previous chunk, and don't
> modify them - those changes are silently discarded.

### Batch Processing

Doctrine keeps every entity it hydrates in memory, so a loop over a large table grows until it dies - and
mutating one raises the question of when to flush. Batch processing answers both by working in chunks:

```php
use Zenstruck\Collection\Doctrine\Batch;

/** @var iterable<Post> $posts */

// read: nothing is flushed, the entity manager is cleared after each chunk
foreach (Batch::iterate($posts, $em) as $post) {
    $csvExporter->addRow([$post->id(), $post->title()]);
}

// write: flushed and cleared after each chunk
foreach (Batch::process($posts, $em) as $post) {
    $post->recalculateScore(); // no persist() or flush() needed
}
```

|                    | `Batch::iterate()`           | `Batch::process()`                                |
|--------------------|------------------------------|---------------------------------------------------|
| After each chunk   | `clear()`                    | `flush()` then `clear()`                          |
| Transaction        | None                         | The entire loop, rolled back if it throws         |
| Use for            | Reading                      | Creating/updating/deleting                        |

> [!WARNING]
> `Batch::iterate()` never flushes. Changes you make to an entity while iterating are silently discarded when
> the chunk is cleared - use `Batch::process()` if you intend to write.

> [!IMPORTANT]
> `Batch::process()` opens _one_ transaction for the whole loop, not one per chunk. Nothing is left half-done
> if it fails, but the transaction is held for the entire run - worth keeping in mind when processing very
> large sets, where it means long-lived locks.

Both take a chunk size, defaulting to `100`:

```php
Batch::process($posts, $em, chunkSize: 500);
```

The items can be _any_ iterable, including data that isn't entities at all:

```php
foreach (Batch::process($csvRows, $em) as $row) {
    $em->persist(Post::fromRow($row)); // flushed every 100 rows, in one transaction
}
```

For a `Query` or `QueryBuilder`, `iteratorFor()`/`processorFor()` wrap it in a Doctrine `Paginator` first:

```php
foreach (Batch::iteratorFor($qb) as $post) {
    // ...
}

foreach (Batch::processorFor($query, chunkSize: 50) as $post) {
    // ...
}
```

> [!WARNING]
> Entities hydrated in earlier chunks are detached once that chunk is done. Don't collect them in an array as
> you go, and don't hold a reference to one across iterations - re-fetch it instead.

> [!TIP]
> When the batch iterator/processor source is countable, the returned iterator is also countable. This makes
> it super flexible for use with `SymfonyStyle::progressIterate()`:
> ```php
> use Symfony\Component\Console\Style\SymfonyStyle;
>
> /** @var SymfonyStyle $io */
>
> // if $csvRows is countable, this is a progress bar with a limit - otherwise it's open ended
> foreach ($io->progressIterate(Batch::process($csvRows, $em)) as $row) {
>     $em->persist(Post::fromRow($row));
> }
> ```

> [!NOTE]
> An [`EntityResult`](#entityresult) has both built in - `$posts->batchIterate()` and `$posts->batchProcess()`
> do the same thing without needing the entity manager passed in. Both take the same chunk size argument.

### Collection Bridge

`DoctrineBridgeCollection` wraps a `doctrine/collections` instance and implements _both_ interfaces at once,
so it is a `Doctrine\Common\Collections\Collection` and a `Zenstruck\Collection`. Everything Doctrine's
collection can do still works, and the lazy/paginating/specification API comes along with it:

```php
use function Zenstruck\collect;

$comments = collect($post->getComments()); // a DoctrineBridgeCollection

// the Doctrine API
$comments->add($comment);
$comments->removeElement($comment);
$comments->containsKey(3);

// ...and this package's
$comments->paginate(page: 2, limit: 10);
$comments->filter(Spec::eq('approved', true));
$comments->map(fn(Comment $comment) => $comment->author());
```

The interesting part is what happens with an _uninitialized_ relation. Doctrine only needs the whole
collection in memory if you make it load - so filtering with a [specification](#specifications) becomes a
`Criteria` (executed as a query against the relation), and iterating pages the relation instead of
initializing it:

```php
// a single query with a WHERE, not "load all comments, then filter"
$approved = $comments->filter(Spec::eq('approved', true));

// paginated queries, not one big fetch
foreach ($comments as $comment) {
    // ...
}
```

> [!NOTE]
> A `Criteria` can be passed directly if you prefer it to specifications - both end up in the same place.

> [!TIP]
> This works for in-memory collections too, not just relations: Doctrine's own `ArrayCollection` is
> `Selectable`, so `new DoctrineBridgeCollection(['a', 'b'])` accepts specifications where
> [`ArrayCollection`](#arraycollection) would reject them.

### Specifications

A _specification_ is an object that describes a filter or a sort. Unlike a callback, it can be inspected -
which is what lets the Doctrine implementations turn it into a query instead of loading everything and
filtering in PHP. Build them with the `Spec` factory:

```php
use Zenstruck\Collection\Spec;

$posts->filter(Spec::andX(
    Spec::eq('status', 'published'),
    Spec::contains('title', 'symfony'),
    Spec::sortDesc('publishedAt'),
));
```

| Factory                                     | Description                                        |
|---------------------------------------------|----------------------------------------------------|
| `Spec::eq($field, $value)`                  | Matches when `$field == $value`                    |
| `Spec::lt($field, $value)`                  | Matches when `$field < $value`                     |
| `Spec::lte($field, $value)`                 | Matches when `$field <= $value`                    |
| `Spec::gt($field, $value)`                  | Matches when `$field > $value`                     |
| `Spec::gte($field, $value)`                 | Matches when `$field >= $value`                    |
| `Spec::in($field, $values)`                 | Matches when `$field` is one of `$values`          |
| `Spec::isNull($field)`                      | Matches when `$field` is `null`                    |
| `Spec::contains($field, $value)`            | Matches when `$field` contains `$value`            |
| `Spec::startsWith($field, $value)`          | Matches when `$field` starts with `$value`         |
| `Spec::endsWith($field, $value)`            | Matches when `$field` ends with `$value`           |
| `Spec::between($field, $begin, $end)`       | Matches when `$begin <= $field <= $end`            |
| `Spec::andX(...$specs)`                     | Matches when every `$spec` matches                 |
| `Spec::orX(...$specs)`                      | Matches when at least one `$spec` matches          |
| `Spec::not($spec)`                          | Matches when `$spec` does not match                |
| `Spec::sortAsc($field)`                     | Orders by `$field`, ascending                      |
| `Spec::sortDesc($field)`                    | Orders by `$field`, descending                     |
| `Spec::callback($callable)`                 | Drops down to the underlying query object          |

> [!NOTE]
> Both `between()` bounds are included unless you say otherwise:
> ```php
> use Zenstruck\Collection\Specification\Filter\Between;
>
> Spec::between('publishedAt', $start, $end);                           // both included
> Spec::between('publishedAt', $start, $end, Between::EXCLUSIVE);       // both excluded
> Spec::between('publishedAt', $start, $end, Between::INCLUSIVE_BEGIN); // begin included, end excluded
> Spec::between('publishedAt', $start, $end, Between::EXCLUSIVE_BEGIN); // begin excluded, end included
> ```

#### String Wildcards

`contains()`, `startsWith()` and `endsWith()` treat `*` as a wildcard anywhere within the value:

```php
Spec::contains('title', 'my*post');   // LIKE '%my%post%'
Spec::startsWith('title', 'my*post'); // LIKE 'my%post%'
Spec::endsWith('title', 'my*post');   // LIKE '%my%post'
```

A leading or trailing `*` is stripped - the specification already adds one on that side:

```php
Spec::contains('title', '*symfony*'); // identical to Spec::contains('title', 'symfony')
```

`*` is the _only_ wildcard. SQL's own wildcards are escaped, so they match literally and user input is safe to
pass straight through:

```php
Spec::contains('title', '50%');     // titles containing "50%"
Spec::startsWith('code', 'a_b');    // codes starting with "a_b" - the underscore isn't a wildcard
```

> [!WARNING]
> All of this is repository-only. An [`EntityResult`](#entityresult) or a
> [bridged collection](#collection-bridge) passes the value straight through to the `Criteria`, so `*` matches
> a literal asterisk and `%`/`_` are left to the database as wildcards.

#### What Understands What

Specifications go through one of two interpreters, and they don't support the same things:

| Source                                              | Understands                                              |
|-----------------------------------------------------|----------------------------------------------------------|
| [Repositories](#repositories) (`find()`/`filter()`/`query()`) | Everything, including the ORM-only specifications below |
| [`EntityResult`](#entityresult) (`filter()`/`find()`) | The table above, converted to a `Criteria`               |
| [Collection Bridge](#collection-bridge)             | The table above, converted to a `Criteria`               |
| In-memory collections                               | Nothing - callables only                                 |

Anything a source doesn't understand throws `Zenstruck\Collection\Exception\InvalidSpecification`.

#### ORM-only Specifications

`DoctrineSpec` extends `Spec`, so it's a drop-in replacement that adds specifications only a repository can
apply:

| Factory                                | Description                                               |
|----------------------------------------|-----------------------------------------------------------|
| `DoctrineSpec::instanceOf($class)`     | Restrict to a subclass (inheritance mapping)              |
| `DoctrineSpec::readonly()`             | Don't track the results in the identity map               |
| `DoctrineSpec::delete()`               | Turn the query into a `DELETE`                            |
| `DoctrineSpec::cache($lifetime, $key)` | Enable the result cache                                   |
| `DoctrineSpec::innerJoin($field)`      | Inner join a relation                                     |
| `DoctrineSpec::leftJoin($field)`       | Left join a relation                                      |
| `DoctrineSpec::antiJoin($field)`       | Left join a relation and require it to be empty           |

Joins can be fetch-joined with `eager()` and narrowed with `scope()`, which applies a specification against
the _joined_ alias rather than the root one:

```php
use Zenstruck\Collection\Doctrine\DoctrineSpec;

$posts->filter(DoctrineSpec::andX(
    DoctrineSpec::eq('status', 'published'),
    DoctrineSpec::innerJoin('category')
        ->eager()                                  // also SELECT the category
        ->scope(DoctrineSpec::eq('name', 'php')),  // category.name = 'php'
    DoctrineSpec::antiJoin('comments'),            // ...that nobody has commented on
));
```

#### Callbacks

`Spec::callback()` hands you the underlying query object when no specification fits. A repository gives you
the query builder and its root alias:

```php
use Doctrine\ORM\QueryBuilder;

$posts->filter(Spec::callback(
    fn(QueryBuilder $qb, string $alias) => $qb->andWhere("{$alias}.views > 100"),
));
```

An `EntityResult` or a bridged collection gives you the `Criteria` instead. Either modify it directly or
return an `Expression` to have it added for you:

```php
use Doctrine\Common\Collections\Criteria;

$comments->filter(Spec::callback(
    fn(Criteria $criteria) => $criteria->andWhere(Criteria::expr()->gt('score', 10)),
));

$comments->filter(Spec::callback(
    fn(Criteria $criteria) => Criteria::expr()->gt('score', 10),
));
```

#### Custom Specifications

Implement `Nested` to name a specification you keep repeating. Both interpreters unwrap them recursively, so
yours works wherever the specifications it's built from work - and composes like any other:

```php
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Specification\Nested;

final class Published implements Nested
{
    public function specification(): mixed
    {
        return Spec::andX(
            Spec::eq('status', 'published'),
            Spec::isNull('deletedAt'),
        );
    }
}

$posts->filter(new Published());
$posts->filter(Spec::not(new Published()));
```

## Symfony Integration

Enable the bundle:

```php
// config/bundles.php

return [
    // ...
    Zenstruck\Collection\Symfony\ZenstruckCollectionBundle::class => ['all' => true],
];
```

There is nothing to configure. When DoctrineBundle is installed, the repository services below are registered
automatically.

### A Repository For Any Entity

Not every entity deserves its own repository class. `ObjectRepositoryFactory` builds one on demand for any
entity you have:

```php
use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;

final class PostController
{
    public function __construct(private ObjectRepositoryFactory $repositories)
    {
    }

    public function index(): Response
    {
        $posts = $this->repositories->create(Post::class); // an ObjectRepository<Post>

        // ...
    }
}
```

Or skip the factory entirely and let `#[ForObject]` inject the repository itself:

```php
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Symfony\Attributes\ForObject;

final class PostController
{
    public function __construct(
        #[ForObject(Post::class)]
        private ObjectRepository $posts,
    ) {
    }
}
```

> [!NOTE]
> Repositories are cached per entity class, so asking for the same one twice gives you the same instance. The
> cache is reset between requests in long-running runtimes.

### Custom Repositories as Services

[`ORMServiceEntityRepository`](#ormserviceentityrepository) is autowireable out of the box. If you'd rather
extend [`EntityRepository`](#entityrepository) - and skip writing a constructor - put `#[ForObject]` on the
class instead:

```php
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityResult;
use Zenstruck\Collection\Symfony\Attributes\ForObject;

/**
 * @extends EntityRepository<Post>
 */
#[ForObject(Post::class)]
final class PostRepository extends EntityRepository
{
    /**
     * @return EntityResult<Post>
     */
    public function published(): EntityResult
    {
        return $this->qb('p')
            ->where('p.status = :status')
            ->setParameter('status', 'published')
            ->result()
        ;
    }
}
```

Inject `PostRepository` like any other service.

> [!WARNING]
> The attribute works by injecting the entity class into `EntityRepository`'s constructor, so your repository
> must not define one of its own - the container throws a `LogicException` when compiling if it does.

> [!NOTE]
> `#[ForObject]` is an autowiring expression, so it requires `symfony/expression-language`.

## Static Analysis

Everything in this package is generic, and the types survive the whole chain - filtering, hydrating,
paginating. This library is analyzed at [PHPStan](https://phpstan.org/) level 8 and ships the annotations for
your code to be as well.

`Collection<V,K>` is templated on both its values and its keys, but the key defaults to `array-key`, so
`Collection<Post>` is usually all you need to write:

```php
/** @var Collection<Post> $posts */

$posts->first();                                  // Post|null
$posts->map(fn(Post $post) => $post->title());    // Collection<string>
$posts->paginate();                               // Page<Post,int>
$posts->eager()->all();                           // array<Post>
```

`collect()` narrows to the implementation it actually returns, so you keep the extra API of whatever you
passed it:

```php
collect(['a', 'b']);      // ArrayCollection<string>
collect($doctrineThing);  // DoctrineBridgeCollection<Post>
```

The Doctrine types follow the same rule - what you hydrate is what you get back:

```php
/** @var EntityRepository<Post> $posts */

$posts->find(1);                                    // Post|null
$posts->query(null);                                // EntityResult<Post>
$posts->query(null)->asInt('id');                   // EntityResult<int>
$posts->query(null)->as(fn(Post $p) => $p->dto());  // EntityResult<PostDto>
$posts->query(null)->withAggregates();              // EntityResult<EntityWithAggregates<Post>>
```

## Security Policy

If you discover a security vulnerability, please do not open a public issue or pull request. Instead, please
review this repository's [Security Policy](https://github.com/zenstruck/collection/security) for instructions
on how to report it responsibly.
