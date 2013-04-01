<?php

class UnderscoreCollectionsTest extends PHPUnit_Framework_TestCase {

  public function testEach() {
    // from js
    $test =& $this;
    __::each(array(1,2,3), function($num, $i) use ($test) {
      $test->assertEquals($num, $i+1, 'each iterators provide value and iteration count');
    });

    $answers = array();
    $context = (object) array('multiplier'=>5);
    __::each(array(1,2,3), function($num) use (&$answers, $context) {
      $answers[] = $num * $context->multiplier;
    });
    $this->assertEquals(array(5,10,15), $answers, 'context object property accessed');

    $answers = array();
    $obj = (object) array('one'=>1, 'two'=>2, 'three'=>3);
    __::each($obj, function($value, $key) use (&$answers) {
      $answers[] = $key;
    });
    $this->assertEquals(array('one','two','three'), $answers, 'iterating over objects works');

    $answer = null;
    __::each(array(1,2,3), function($num, $index, $arr) use (&$answer) {
      if(__::contains($arr, $num)) $answer = true;
    });
    $this->assertTrue($answer, 'can reference the original collection from inside the iterator');

    $answers = 0;
    __::each(null, function() use (&$answers) {
      $answers++;
    });
    $this->assertEquals(0, $answers, 'handles a null property');

    // extra
    $test =& $this;
    __(array(1,2,3))->each(function($num, $i) use ($test) {
      $test->assertEquals($num, $i+1, 'each iterators provide value and iteration count within OO-style call');
    });

    // docs
    $str = '';
    __::each(array(1, 2, 3), function($num) use (&$str) { $str .= $num . ','; });
    $this->assertEquals('1,2,3,', $str);

    $str = '';
    $multiplier = 2;
    __::each(array(1, 2, 3), function($num, $index) use ($multiplier, &$str) {
      $str .= $index . '=' . ($num * $multiplier) . ',';
    });
    $this->assertEquals('0=2,1=4,2=6,', $str);
  }

  public function testMap() {
    // from js
    $this->assertEquals(array(2,4,6), __::map(array(1,2,3), function($num) {
      return $num * 2;
    }), 'doubled numbers');

    $ifnull = __::map(null, function() {});
    $this->assertTrue(is_array($ifnull) && count($ifnull) === 0, 'handles a null property');

    $multiplier = 3;
    $func = function($num) use ($multiplier) { return $num * $multiplier; };
    $tripled = __::map(array(1,2,3), $func);
    $this->assertEquals(array(3,6,9), $tripled);

    $doubled = __(array(1,2,3))->map(function($num) { return $num * 2; });
    $this->assertEquals(array(2,4,6), $doubled, 'OO-style doubled numbers');

    $this->assertEquals(array(2, 4, 6), __::map(array(1, 2, 3), function($n) { return $n * 2; }));
    $this->assertEquals(array(2, 4, 6), __(array(1, 2, 3))->map(function($n) { return $n * 2; }));

    $doubled = __::collect(array(1, 2, 3), function($num) { return $num * 2; });
    $this->assertEquals(array(2, 4, 6), $doubled, 'aliased as "collect"');

    // docs
    $this->assertEquals(array(3,6,9), __::map(array(1, 2, 3), function($num) { return $num * 3; }));
    $this->assertEquals(array(3,6,9), __::map(array('one'=>1, 'two'=>2, 'three'=>3), function($num, $key) { return $num * 3; }));
  }

  public function testReduce() {
    // from js
    $sum = __::reduce(array(1,2,3), function($sum, $num) { return $sum + $num; }, 0);
    $this->assertEquals(6, $sum, 'can sum up an array');

    $context = array('multiplier'=>3);
    $sum = __::reduce(array(1,2,3), function($sum, $num) use ($context) { return $sum + $num * $context['multiplier']; }, 0);
    $this->assertEquals(18, $sum, 'can reduce with a context object');

    $sum = __::reduce(array(1,2,3), function($sum, $num) { return $sum + $num; }, 0);
    $this->assertEquals(6, $sum, 'default initial value');

    $ifnull = null;
    try { __::reduce(null, function() {}); }
    catch(Exception $e) { $ifnull = $e; }
    $this->assertFalse($ifnull === null, 'handles a null (without initial value) properly');

    $this->assertEquals(138, __::reduce(null, function(){}, 138), 'handles a null (with initial value) properly');

    $sum = __(array(1,2,3))->reduce(function($sum, $num) { return $sum + $num; });
    $this->assertEquals(6, $sum, 'OO-style reduce');

    $sum = __::inject(array(1,2,3), function($sum, $num) { return $sum + $num; }, 0);
    $this->assertEquals(6, $sum, 'aliased as "inject"');

    $sum = __::foldl(array(1,2,3), function($sum, $num) { return $sum + $num; }, 0);
    $this->assertEquals(6, $sum, 'aliased as "foldl"');

    // docs
    $this->assertEquals(6, __::reduce(array(1, 2, 3), function($memo, $num) { return $memo + $num; }, 0));
  }

  public function testReduceRight() {
    // from js
    $list = __::reduceRight(array('foo', 'bar', 'baz'), function($memo, $str) { return $memo . $str; }, '');
    $this->assertEquals('bazbarfoo', $list, 'can perform right folds');

    $ifnull = null;
    try { __::reduceRight(null, function() {}); }
    catch(Exception $e) { $ifnull = $e; }
    $this->assertFalse($ifnull === null, 'handles a null (without initial value) properly');

    $this->assertEquals(138, __::reduceRight(null, function(){}, 138), 'handles a null (with initial value) properly');

    // extra
    $list = __(array('moe','curly','larry'))->reduceRight(function($memo, $str) { return $memo . $str; }, '');
    $this->assertEquals('larrycurlymoe', $list, 'can perform right folds in OO-style');

    $list = __::foldr(array('foo', 'bar', 'baz'), function($memo, $str) { return $memo . $str; }, '');
    $this->assertEquals('bazbarfoo', $list, 'aliased as "foldr"');

    $list = __::foldr(array('foo', 'bar', 'baz'), function($memo, $str) { return $memo . $str; });
    $this->assertEquals('bazbarfoo', $list, 'default initial value');

    // docs
    $list = array(array(0, 1), array(2, 3), array(4, 5));
    $flat = __::reduceRight($list, function($a, $b) { return array_merge($a, $b); }, array());
    $this->assertEquals(array(4, 5, 2, 3, 0, 1), $flat);
  }

  public function testFind() {
    // from js
    $this->assertEquals(2, __::find(array(1,2,3), function($num) { return $num * 2 === 4; }), 'found the first "2" and broke the loop');

    // extra
    $iterator = function($n) { return $n % 2 === 0; };
    $this->assertEquals(2, __::find(array(1, 2, 3, 4, 5, 6), $iterator));
    $this->assertEquals(false, __::find(array(1, 3, 5), $iterator));
    $this->assertEquals(false, __(array(1,3,5))->find($iterator), 'works with OO-style calls');
    $this->assertEquals(__::find(array(1,3,5), $iterator), __::detect(array(1,3,5), $iterator), 'alias works');

    // docs
    $this->assertEquals(2, __::find(array(1, 2, 3, 4), function($num) { return $num % 2 === 0; }));
  }

  public function testFilter() {
    // from js
    $evens = __::filter(array(1,2,3,4,5,6), function($num) { return $num % 2 === 0; });
    $this->assertEquals(array(2, 4, 6), $evens, 'selected each even number');

    // extra
    $odds = __(array(1,2,3,4,5,6))->filter(function($num) { return $num % 2 !== 0; });
    $this->assertEquals(array(1,3,5), $odds, 'works with OO-style calls');

    $evens = __::filter(array(1,2,3,4,5,6), function($num) { return $num % 2 === 0; });
    $this->assertEquals(array(2,4,6), $evens, 'aliased as filter');

    $iterator = function($num) { return $num % 2 !== 0; };
    $this->assertEquals(__::filter(array(1,3,5), $iterator), __::select(array(1,3,5), $iterator), 'alias works');

    // docs
    $this->assertEquals(array(2,4), __::filter(array(1, 2, 3, 4), function($num) { return $num % 2 === 0; }));
  }

  public function testWhere() {
    // from js
    $list = array(
        array('a' => 1, 'b' => 2),
        array('a' => 2, 'b' => 2),
        array('a' => 1, 'b' => 3),
        array('a' => 1, 'b' => 4),
    );
    $result = __::where($list, array('a' => 1));
    $this->assertEquals(count($result), 3);
    $this->assertEquals($result[count($result) - 1]['b'], 4);

    $result = __::where($list, array('b' => 2));
    $this->assertEquals(count($result), 2);
    $this->assertEquals($result[0]['a'], 1);

    // extra
    $result = __::where($list, null);
    $this->assertEquals($result, array(), 'handles a null property');

    $ifnull = __::where(null, null);
    $this->assertTrue(is_array($ifnull) && count($ifnull) === 0, 'handles a null properly');

    $result = __($list)->where(array('a' => 1, 'b' => 3));
    $this->assertEquals(count($result), 1, 'works with OO-style calls');

    // docs
    // TODO
  }

  public function testFindWhere() {
    // from js
    $list = array(
        array('a' => 1, 'b' => 2),
        array('a' => 2, 'b' => 2),
        array('a' => 1, 'b' => 3),
        array('a' => 1, 'b' => 4),
        array('a' => 2, 'b' => 4),
    );
    $result = __::findWhere($list, array('a' => 1));
    $this->assertEquals($result, array('a' => 1, 'b' => 2));

    $result = __::findWhere($list, array('b' => 4));
    $this->assertEquals($result, array('a' => 1, 'b' => 4));

    $result = __::findWhere($list, array('a' => 0));
    $this->assertEquals($result, null);

    // extra
    $result = __($list)->findWhere(array('a' => 2));
    $this->assertEquals($result['b'], 2, 'works with OO-style calls');

    // docs
    // TODO

  }

  public function testReject() {
    // from js
    $odds = __::reject(array(1,2,3,4,5,6), function($num) { return $num % 2 === 0; });
    $this->assertEquals(array(1, 3, 5), $odds, 'rejected each even number');

    // extra
    $evens = __(array(1,2,3,4,5,6))->reject(function($num) { return $num % 2 !== 0; });
    $this->assertEquals(array(2,4,6), $evens, 'works with OO-style calls');

    // docs
    $this->assertEquals(array(1, 3), __::reject(array(1, 2, 3, 4), function($num) { return $num % 2 === 0; }));
  }

  public function testEvery() {
    // from js
    $this->assertTrue(__::every(array(), function($v) {return $v;}), 'the empty set');
    $this->assertTrue(__::every(array(true, true, true), function($v) {return $v;}), 'all true values');
    $this->assertFalse(__::every(array(true, false, true), function($v) {return $v;}), 'one false value');
    $this->assertTrue(__::every(array(0, 10, 28), function($num) { return $num % 2 === 0;  }), 'even numbers');
    $this->assertFalse(__::every(array(0, 11, 28), function($num) { return $num % 2 === 0;  }), 'odd numbers');

    // extra
    $this->assertTrue(__::every(array()));
    $this->assertFalse(__::every(array(null)));
    $this->assertFalse(__::every(0));
    $this->assertFalse(__::every('0'));
    $this->assertFalse(__::every(array(0,1)));
    $this->assertTrue(__::every(array(1)));
    $this->assertTrue(__::every(array('1')));
    $this->assertTrue(__::every(array(1,2,3,4)));
    $this->assertTrue(__(array(1,2,3,4))->every(), 'works with OO-style calls');
    $this->assertTrue(__(array(true, true, true))->every(function($v) {return $v;}));

    $this->assertTrue(__(array(true, true, true))->all(function($v) {return $v;}), 'aliased as "all"');

    // docs
    $this->assertFalse(__::every(array(1, 2, 3, 4), function($num) { return $num % 2 === 0; }));
    $this->assertTrue(__::every(array(1, 2, 3, 4), function($num) { return $num < 5; }));
  }

  public function testSome() {
    // from js
    $this->assertFalse(__::some(array()), 'the empty set');
    $this->assertFalse(__::some(array(false, false, false)), 'all false values');
    $this->assertTrue(__::some(array(false, false, true)), 'one true value');
    $this->assertFalse(__::some(array(1, 11, 29), function($num) { return $num % 2 === 0; }), 'all odd numbers');
    $this->assertTrue(__::some(array(1, 10, 29), function($num) { return $num % 2 === 0; }), 'an even number');

    // extra
    $this->assertFalse(__::some(array()));
    $this->assertFalse(__::some(array(null)));
    $this->assertFalse( __::some(array(0)));
    $this->assertFalse(__::some(array('0')));
    $this->assertTrue(__::some(array(0, 1)));
    $this->assertTrue(__::some(array(1)));
    $this->assertTrue(__::some(array('1')));
    $this->assertTrue(__::some(array(1,2,3,4)));
    $this->assertTrue(__(array(1,2,3,4))->some(), 'works with OO-style calls');
    $this->assertFalse(__(array(1,11,29))->some(function($num) { return $num % 2 === 0; }));

    $this->assertTrue(__::any(array(false, false, true)), 'alias as "any"');
    $this->assertTrue(__(array(1,2,3,4))->any(), 'aliased as "any"');

    // docs
    $this->assertTrue(__::some(array(1, 2, 3, 4), function($num) { return $num % 2 === 0; }));
    $this->assertFalse(__::some(array(1, 2, 3, 4), function($num) { return $num === 5; }));
  }

  public function testContains() {
    // from js
    $this->assertTrue(__::contains(array(1,2,3), 2), 'two is in the array');
    $this->assertFalse(__::contains(array(1,3,9), 2), 'two is not in the array');
    $this->assertTrue(__(array(1,2,3))->contains(2), 'OO-style includ');

    // extra
    $collection = array(true, false, 0, 1, -1, 'foo', array(), array('meh'));
    $this->assertTrue(__::contains($collection, true));
    $this->assertTrue(__::contains($collection, false));
    $this->assertTrue(__::contains($collection, 0));
    $this->assertTrue(__::contains($collection, 1));
    $this->assertTrue(__::contains($collection, -1));
    $this->assertTrue(__::contains($collection, 'foo'));
    $this->assertTrue(__::contains($collection, array()));
    $this->assertTrue(__::contains($collection, array('meh')));
    $this->assertFalse(__::contains($collection, 'true'));
    $this->assertFalse(__::contains($collection, '0'));
    $this->assertFalse(__::contains($collection, '1'));
    $this->assertFalse(__::contains($collection, '-1'));
    $this->assertFalse(__::contains($collection, 'bar'));
    $this->assertFalse(__::contains($collection, 'Foo'));

    $this->assertTrue(__::contains((object) array('moe'=>1, 'larry'=>3, 'curly'=>9), 3), '__::includ on objects checks their values');

    // docs
    $this->assertTrue(__::contains(array(1, 2, 3), 3));
  }

  // TODO wrong invoke implement/test
  public function testInvoke() {
    // from js
    // the sort example from js doesn't work here because sorting occurs in place in PHP
    $list = array(new Something(2), new Something(5));
    $list_invoked = __::invoke($list, 'increment');
    $this->assertEquals(array(3, 6), array($list_invoked[0]->value(), $list_invoked[1]->value()));

    $list = array(new Something(2), new Something(5));
    $list_invoked = __::invoke($list, 'increment', 2, 1);
    $this->assertEquals(array(5, 8), array($list_invoked[0]->value(), $list_invoked[1]->value()));

    $list = array(new Something(2), new Something(5));
    $list_invoked = __($list)->invoke('increment');
    $this->assertEquals(array(3, 6), array($list_invoked[0]->value(), $list_invoked[1]->value()), 'works with OO-style call');

    // docs
    // TODO
  }

  public function testPluck() {
    // from js
    $people = array(
      array('name'=>'moe', 'age'=>30),
      array('name'=>'curly', 'age'=>50)
    );
    $this->assertEquals(array('moe', 'curly'), __::pluck($people, 'name'), 'pulls names out of objects');

    // extra: array
    $stooges = array(
      array('name'=>'moe',   'age'=> 40),
      array('name'=>'larry', 'age'=> 50, 'foo'=>'bar'),
      array('name'=>'curly', 'age'=> 60)
    );
    $this->assertEquals(array('moe', 'larry', 'curly'), __::pluck($stooges, 'name'));
    $this->assertEquals(array(40, 50, 60), __::pluck($stooges, 'age'));
    $this->assertEquals(array('bar'), __::pluck($stooges, 'foo'));
    $this->assertEquals(array('bar'), __($stooges)->pluck('foo'), 'works with OO-style call');

    // extra: object
    $stooges_obj = new StdClass;
    foreach($stooges as $stooge) {
      $name = $stooge['name'];
      $stooges_obj->$name = (object) $stooge;
    }
    $this->assertEquals(array('moe', 'larry', 'curly'), __::pluck($stooges_obj, 'name'));
    $this->assertEquals(array(40, 50, 60), __::pluck($stooges_obj, 'age'));
    $this->assertEquals(array('bar'), __::pluck($stooges_obj, 'foo'));
    $this->assertEquals(array('bar'), __($stooges_obj)->pluck('foo'), 'works with OO-style call');

    // extra: function
    // TODO

    // docs
    $stooges = array(
      array('name'=>'moe', 'age'=>40),
      array('name'=>'larry', 'age'=>50),
      array('name'=>'curly', 'age'=>60)
    );
    $this->assertEquals(array('moe', 'larry', 'curly'), __::pluck($stooges, 'name'));
  }

  public function testMax() {
    // from js
    $this->assertEquals(3, __::max(array(1,2,3)), 'can perform a regular max');
    $this->assertEquals(1, __::max(array(1,2,3), function($num) { return -$num; }), 'can performa a computation-based max');

    // extra
    $stooges = array(
      array('name'=>'moe',   'age'=>40),
      array('name'=>'larry', 'age'=>50),
      array('name'=>'curly', 'age'=>60)
    );
    $this->assertEquals($stooges[2], __::max($stooges, function($stooge) { return $stooge['age']; }));
    $this->assertEquals($stooges[0], __::max($stooges, function($stooge) { return $stooge['name']; }));
    $this->assertEquals($stooges[0], __($stooges)->max(function($stooge) { return $stooge['name']; }), 'works with OO-style call');

    // docs
    $stooges = array(
      array('name'=>'moe', 'age'=>40),
      array('name'=>'larry', 'age'=>50),
      array('name'=>'curly', 'age'=>60)
    );
    $this->assertEquals(array('name'=>'curly', 'age'=>60), __::max($stooges, function($stooge) { return $stooge['age']; }));
  }

  public function testMin() {
    // from js
    $this->assertEquals(1, __::min(array(1,2,3)), 'can perform a regular min');
    $this->assertEquals(3, __::min(array(1,2,3), function($num) { return -$num; }), 'can performa a computation-based max');

    // extra
    $stooges = array(
      array('name'=>'moe',   'age'=>40),
      array('name'=>'larry', 'age'=>50),
      array('name'=>'curly', 'age'=>60)
    );
    $this->assertEquals($stooges[0], __::min($stooges, function($stooge) { return $stooge['age']; }));
    $this->assertEquals($stooges[2], __::min($stooges, function($stooge) { return $stooge['name']; }));
    $this->assertEquals($stooges[2], __($stooges)->min(function($stooge) { return $stooge['name']; }), 'works with OO-style call');

    // docs
    $stooges = array(
      array('name'=>'moe', 'age'=>40),
      array('name'=>'larry', 'age'=>50),
      array('name'=>'curly', 'age'=>60)
    );
    $this->assertEquals(array('name'=>'moe', 'age'=>40), __::min($stooges, function($stooge) { return $stooge['age']; }));
  }

  public function testSortBy() {
    // from js
    $people = array(
      (object) array('name'=>'curly', 'age'=>50),
      (object) array('name'=>'moe', 'age'=>30)
    );
    $people_sorted = __::sortBy($people, function($person) { return $person->age; });
    $this->assertEquals(array('moe', 'curly'), __::pluck($people_sorted, 'name'), 'stooges sorted by age');

    // extra
    $stooges = array(
      array('name'=>'moe',   'age'=>40),
      array('name'=>'larry', 'age'=>50),
      array('name'=>'curly', 'age'=>60)
    );
    $this->assertEquals($stooges, __::sortBy($stooges, function($stooge) { return $stooge['age']; }));
    $this->assertEquals(array($stooges[2], $stooges[1], $stooges[0]), __::sortBy($stooges, function($stooge) { return $stooge['name']; }));
    $this->assertEquals(array(5, 4, 6, 3, 1, 2), __::sortBy(array(1, 2, 3, 4, 5, 6), function($num) { return sin($num); }));
    $this->assertEquals($stooges, __($stooges)->sortBy(function($stooge) { return $stooge['age']; }), 'works with OO-style call');

    // docs
    $this->assertEquals(array(3, 2, 1), __::sortBy(array(1, 2, 3), function($n) { return -$n; }));
  }

  public function testGroupBy() {
    // from js
    $parity = __::groupBy(array(1,2,3,4,5,6), function($num) { return $num % 2; });
    $this->assertEquals(array(array(2,4,6), array(1,3,5)), $parity, 'created a group for each value');

    // extra
    $parity = __(array(1,2,3,4,5,6))->groupBy(function($num) { return $num % 2; });
    $this->assertEquals(array(array(2,4,6), array(1,3,5)), $parity, 'created a group for each value using OO-style call');

    $vals = array(
      array('name'=>'rejected', 'yesno'=>'no'),
      array('name'=>'accepted', 'yesno'=>'yes'),
      array('name'=>'allowed', 'yesno'=>'yes'),
      array('name'=>'denied', 'yesno'=>'no')
    );
    $grouped = __::groupBy($vals, 'yesno');
    $this->assertEquals('rejected denied', join(' ', __::pluck($grouped['no'], 'name')), 'pulls no entries');
    $this->assertEquals('accepted allowed', join(' ', __::pluck($grouped['yes'], 'name')), 'pulls yes entries');

    $values = array(
      array('name'=>'Apple',   'grp'=>'a'),
      array('name'=>'Bacon',   'grp'=>'b'),
      array('name'=>'Avocado', 'grp'=>'a')
    );
    $expected = array(
      'a'=>array(
        array('name'=>'Apple',   'grp'=>'a'),
        array('name'=>'Avocado', 'grp'=>'a')
      ),
      'b'=>array(
        array('name'=>'Bacon',   'grp'=>'b')
      )
    );
    $this->assertEquals($expected, __::groupBy($values, 'grp'));

    // docs
    $result = __::groupBy(array(1, 2, 3, 4, 5), function($n) { return $n % 2; });
    $this->assertEquals(array(0=>array(2, 4), 1=>array(1, 3, 5)), $result);
  }

  public function testCountBy() {
    // from js
    $parity = __::countBy(array(1,2,3,4,5,6), function($num) { return $num % 2; });
    $this->assertEquals(array(3, 3), $parity, 'count for each value');

    // extra
    $parity = __(array(1,2,3,4,5,6))->countBy(function($num) { return $num % 2; });
    $this->assertEquals(array(3, 3), $parity, 'count for each value using OO-style call');

    $vals = array(
      array('name'=>'rejected', 'yesno'=>'no'),
      array('name'=>'accepted', 'yesno'=>'yes'),
      array('name'=>'allowed', 'yesno'=>'yes'),
      array('name'=>'denied', 'yesno'=>'no')
    );
    $this->assertEquals(array('no' => 2, 'yes' => 2), __::countBy($vals, 'yesno'));

    $values = array(
      array('name'=>'Apple',   'grp'=>'a'),
      array('name'=>'Bacon',   'grp'=>'b'),
      array('name'=>'Avocado', 'grp'=>'a')
    );
    $this->assertEquals(array('a' => 2, 'b' => 1), __::countBy($values, 'grp'));

    // docs
    // TODO
  }

  public function testShuffle() {
    // from js
    $numbers = range(1, 10);
    $shuffled = __::shuffle($numbers);
    sort($shuffled);

    $this->assertEquals(join(',', $numbers), join(',', $shuffled), 'contains the same members before and after shuffle');
  }
  public function testToArray() {
    // from js
    $numbers = __::toArray((object) array('one'=>1, 'two'=>2, 'three'=>3));
    $this->assertEquals('1, 2, 3', join(', ', $numbers), 'object flattened into array');

    // docs
    $stooge = new StdClass;
    $stooge->name = 'moe';
    $stooge->age = 40;
    $this->assertEquals(array('name'=>'moe', 'age'=>40), __::toArray($stooge));
  }

  public function testSize() {
    // from js
    $items = (object) array(
      'one'   =>1,
      'two'   =>2,
      'three' =>3
    );
    $this->assertEquals(3, __::size($items), 'can compute the size of an object');

    // extra
    $this->assertEquals(0, __::size(array()));
    $this->assertEquals(1, __::size(array(1)));
    $this->assertEquals(3, __::size(array(1, 2, 3)));
    $this->assertEquals(6, __::size(array(null, false, array(), array(1,2,array('a','b')), 1, 2)));
    $this->assertEquals(3, __(array(1,2,3))->size(), 'works with OO-style calls');

    // docs
    $stooge = new StdClass;
    $stooge->name = 'moe';
    $stooge->age = 40;
    $this->assertEquals(2, __::size($stooge));
  }

  /*

  public function testSortedIndex() {
    // from js
    $numbers = array(10, 20, 30, 40, 50);
    $num = 35;
    $index = __::sortedIndex($numbers, $num);
    $this->assertEquals(3, $index, '35 should be inserted at index 3');

    // extra
    $this->assertEquals(3, __($numbers)->sortedIndex(35), '35 should be inserted at index 3 with OO-style call');

    // docs
    $this->assertEquals(3, __::sortedIndex(array(10, 20, 30, 40), 35));
  }

  */
}

class Something {
    private $value = '';
    public function __construct($value) {
        $this->value = $value;
    }

    public function value() {
        return $this->value;
    }

    public function increment($n=1, $m = 0) {
        $this->value = $this->value + $n + $m;
        return $this;
    }

}
