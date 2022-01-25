<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Concepts</p>
    <p class="subtitle">Collections</p>
  </div>
</section>

<!---{? set title = "Collections @ Elephox" }-->

[toc]

---

<article class="message is-info">
  <div class="message-body">
    Collections are also available as their own independent package: <a href="https://packagist.org/packages/elephox/collection" target="_blank">elephox/collection</a>
  </div>
</article>

# Overview

First, let's define what types of collections there are:

[List](#Lists)
:   A collection of elements associated with a numeric index. The index always starts at 0.

[Map](#Maps)
:   A collection of elements associated with a key value. The key values can be of any type.

[Set](#Sets)
:   A collection of unique elements. A set only accepts elements if it doesn't contain it already.

# Lists

```php
use Elephox\Collection\ArrayList;

$list = ArrayList::from([5, 1, 2, 6, 3, 2]);

echo $list[0]; // echoes '5'
echo $list[5]; // echoes '2'

$list[] = 4; // $list->toArray() == [5, 1, 2, 6, 3, 2, 4]

echo count($list); // echoes '7'
```

An `ArrayList` implements `ArrayAccess`, `Countable` and `IteratorAggregate`, meaning you can use it as a normal array (accessing elements by index, using it in a `foreach`, counting using `count()` and checking for indices using `array_key_exists`).

It is useful for a lot of scenarios, where you would normally use a native PHP array.
`ArrayList`s however have some significant advantages over a normal array: they implement [`KeyedEnumerable`](#keyed-enumerables).

With `KeyedEnumerables`, you have a lot of useful functions for sorting, filtering and mapping than are available with `sort()`, `array_filter()`, `array_map()`, etc.
You are also more flexible, because it is an object and not a scalar value, meaning you can expand the normal functionality with your business logic.

Currently `ArrayList` is the only class implementing `GenericList`.
You are free to implement it yourself and to help you, Elephox provides a trait with only one abstract method for you to implement: [`IsKeyedEnumerable`](#traits).

# Maps

```php
use Elephox\Collection\ArrayMap;

$map = ArrayMap::from(['a' => 1, 'b' => 3, 'c' => 6, 'd' => 2]);

echo $map['a']; // echoes '1'

$map['e'] = 4; // $map->toArray() == ['a' => 1, 'b' => 3, 'c' => 6, 'd' => 2, 'e' => 4]
$map->put('e', 3); // same effect as above, updates the value of $map['e'] to be 3 

echo count($map); // echoes '5'
```

Maps are used to associate keys with values. For an `ArrayMap`, keys are restricted to normal PHP array keys (`int` or `string`).
In case you need to map objects to values (like `SplObjectStorage`), you can use `ObjectMap`s:

```php
use Elephox\Collection\ObjectMap;

$objA = new \stdClass();
$objA->title = 'Object A';

$objB = new \stdClass();
$objB->title = 'Object B';

$map = new ObjectMap();
$map->put($objA, ['status' => 'ok']);
$map->put($objB, ['status' => 'failed']);

foreach ($map as $key => $value) {
    echo $key->title . ' has status ' . $value['status'];
}

// prints:
//
// Object A has status ok
// Object B has status failed
```

An `ObjectMap` has the advantage of being able to use rich objects as keys.
The values can be objects too, without having to keep two arrays in sync to store both objects.

As you might have guessed: `ArrayMap` and `ObjectMap` also implement [`KeyedEnumerable`](#keyed-enumerables)!
This gives you plenty of functions to achieve almost everything you want.

# Sets

```php
use Elephox\Collection\ArraySet;

$set = new ArraySet();

$set->add('hello'); // returns true, because the value wasn't part of the set
$set->add('dear');  // also returns true
$set->add('world'); // also returns true
echo count($set); // echoes '3'

$set->add('hello'); // returns false, because the element was already part of the set
echo count($set); // echoes '3'

$set->remove('hello'); // returns true, because it was part of the set
echo count($set); // echoes '2'

$set->remove('hello'); // returns false, because the element didn't exist in the set to begin with
echo count($set); // echoes '2'

// remove everything that contains 'o' from the set
$set->removeBy(fn ($v) => str_contains($v, 'o')); // returns true, since at least one element was removed
echo count($set); // echoes '1' (only 'dear' remains in the set)
```

Sets are a great way to keep track of a list of unique elements.

Sets allow you to specify a custom compare function, which determines if two elements should be considered equal:

```php
use Elephox\Collection\ArraySet;

$a = new \stdClass();
$a->weight = 0;

$b = new \stdClass();
$b->weight = 1;

$c = new \stdClass();
$c->weight = 2;

$d = new \stdClass();
$d->weight = 1;

$uniqueWeightsSet = new ArraySet(comparer: fn ($a, $b) => $a->weight === $b->weight);
$uniqueWeightsSet->add($a); // true
$uniqueWeightsSet->add($b); // true
$uniqueWeightsSet->add($c); // true
$uniqueWeightsSet->add($d); // false, $d->weight === $a->weight, so the element is considered to be a part of the set already 
```

# Enumerable & KeyedEnumerable

Enumerables in Elephox were inspired and heavily influenced by the <a href="https://docs.microsoft.com/en-us/dotnet/api/system.collections.generic.ienumerable-1" target="_blank">C# IEnumerable</a>.
They provide a lot of functionality by chaining and combining **iterators**.

<article class="message is-info">
  <div class="message-header">
    <p>Nice to know</p>
  </div>
  <div class="message-body">
    An <strong>iterator</strong> is an object which is used by many languages (including PHP) to loop over a collection using <code>while</code>-loops.
    This reduces overhead when implementing other loops such as <code>for</code> and <code>foreach</code> since only <code>while</code> needs to be implemented and the others are inferred with syntactic sugar.
    <br>
    A <a href="https://www.php.net/manual/en/class.iterator.php" target="_blank">PHP iterator</a> only has a small set of functions:
    <ul>
      <li><code>current()</code>: returns the current value of the iterator</li>
      <li><code>key()</code>: returns the current key of the iterator</li>
      <li><code>next()</code>: instructs the iterator to move to the next element</li>
      <li><code>valid()</code>: used to check if the iterator has reached the end of the collection</li>
      <li><code>rewind()</code>: reset the iterator to the beginning</li>
    </ul>
  </div>
</article>

First, let's look at how a `foreach`-loop loops over an array:

```php
$array = [1, 2, 3];
foreach ($array as $index => $value) {
    echo $index . " -> " . $value . "\n";
}

// prints:
// 0 -> 1
// 1 -> 2
// 2 -> 3
```

Now, let's look at what goes on behind the scenes:

```php
$array = [1, 2, 3];

$iterator = new \ArrayIterator($array);
$iterator->rewind();
while ($iterator->valid()) {
    echo $iterator->key() . " -> " . $iterator->current() . "\n";
    
    $iterator->next();
}

// prints:
// 0 -> 1
// 1 -> 2
// 2 -> 3
```

PHP can use an `ArrayIterator` and a `while`-loop to implement a `foreach`-loop!
In fact, a `foreach`-loop can use any object implementing [`Traversable`](https://www.php.net/manual/en/class.traversable.php) (which is a parent of [`Iterator`](https://www.php.net/manual/en/class.iterator.php)).

You cannot directly implement `Traversable` though.
So you need to implement a subtype (`Iterator` or `IteratorAggregate`) to pass the object into a `foreach`-loop.

Enumerables in Elephox all implement the `IteratorAggregate`, meaning they have a method - `getIterator()` -, which returns an `Iterator` for the given enumerable.
The `Enumerable` and `KeyedEnumerable` interfaces use said chaining and combining of iterators to efficiently implement a lot of useful functions.

<article class="message is-info">
  <div class="message-header">
    <p>Bonus fact</p>
  </div>
  <div class="message-body">
    You can also represent a <code>foreach</code>-loop using a <code>for</code>-loop and iterators:<br>
    <div markdown="1">

```php
$array = [1, 2, 3];

$iterator = new \ArrayIterator($array);
for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
    echo $iterator->key() . " -> " . $iterator->current() . "\n";
}

// prints:
// 0 -> 1
// 1 -> 2
// 2 -> 3
```

</div>
    Do you recognize the default <code>$i</code> operations being replaced by iterator calls? Pretty neat, huh?
  </div>
</article>

## The (key-)difference

Elephox differentiates between collections having keys ([maps](#maps) and [lists](#lists)) and collections being keyless ([sets](#sets)).

In keyed collections, the key/index decides whether to add, update or remove an element, whereas in keyless collections, only the value is important.

## Traits

The traits `IsEnumerable` and `IsKeyedEnumerable` help you to implement `GenericEnumerable` and `GenericKeyedEnumerable` yourself.
Both traits have only one abstract method you need to implement: `getIterator(): Iterator`.
Every other method is implemented using the iterator returned from this method.

Example:

```php
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\IsEnumerable;
use ArrayIterator;

class MyArrayEnumerable implements GenericEnumerable {
    use IsEnumerable;

    public function __construct(private array $elements) {}
    
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }
}
```
