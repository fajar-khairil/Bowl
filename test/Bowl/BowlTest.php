<?php

/**
 * Test case of Bowl
 *
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class BowlTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests parameter handling
     */
    public function testParameters()
    {
        $bowl = new \Bowl\Bowl();
        $bowl['foo'] = 'bar';

        $this->assertEquals('bar', $bowl['foo']);

        unset($bowl['foo']);

        $this->assertFalse(isset($bowl['foo']));
    }

    /**
     * Tests shared services
     */
    public function testSingleton()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->share('test', function () {
            return new stdClass();
        });

        $this->assertNotSame(new stdClass(), new stdClass());
        $this->assertSame($bowl->get('test'), $bowl->get('test'));
    }

    /**
     * Tests factory services
     */
    public function testFactory()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->factory('test', function () {
            return new stdClass();
        });

        $this->assertNotSame($bowl->get('test'), $bowl->get('test'));
    }

    /**
     * Tests dependencies
     */
    public function testDependencies()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->factory('child', function () {
            $object = new stdClass();
            $object->name = 'foo';

            return $object;
        });
        $bowl->share('parent', function () {
            $object = new stdClass();
            $object->child = $this->get('child');

            return $object;
        });

        $object = $bowl->get('parent');

        $this->assertEquals('foo', $object->child->name);
    }

    /**
     * Tests tagged services
     */
    public function testTaggedServices()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->share('taggedA', function () {
            $object = new stdClass();
            $object->name = 'A';

            return $object;
        }, ['tag']);
        $bowl->share('taggedB', function () {
            $object = new stdClass();
            $object->name = 'B';

            return $object;
        }, ['tag']);

        $this->assertCount(2, $bowl->getTaggedServices('tag'));
        $this->assertEquals([$bowl->get('taggedA'), $bowl->get('taggedB')], iterator_to_array($bowl->getTaggedServices('tag')));
    }

    /**
     * Tests extending services
     */
    public function testExtend()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->share('test', function () {
            return new stdClass();
        });
        $bowl->extend('test', function ($object) {
            $object->name = 'foo';

            return $object;
        });

        $object = $bowl->get('test');
        $this->assertEquals('foo', $object->name);
    }

    /**
     * Tests re-instantiation of services
     */
    public function testResetService()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->share('test', function () {
            return new stdClass();
        });

        $objA = $bowl->get('test');
        $objB = $bowl->get('test');
        $objC = $bowl->reset('test')->get('test');

        $this->assertSame($objA, $objB);
        $this->assertNotSame($objA, $objC);
    }

    /**
     * Tests environment manager
     */
    public function testEnvironments()
    {
        $bowl = new \Bowl\Bowl();
        $bowl['debug'] = false;

        $bowl->configure('prod', function (\Bowl\Bowl $bowl) {
            $bowl->share('test', function () {
                return [1];
            });
        });
        $bowl->configure('dev', function (\Bowl\Bowl $bowl) {
            $bowl['debug'] = true;
            $bowl->share('test', function () {
                return [2];
            });
        });

        $bowl->share('foo', function () {
            return [0, $this->get('test')];
        });

        $bowl->env('dev');

        $this->assertEquals([0, [2]], $bowl->get('foo'));
        $this->assertTrue($bowl['debug']);
    }

    /**
     * Tests environment manager with tags
     */
    public function testEnvironmentsWithTags()
    {
        $bowl = new \Bowl\Bowl();
        $bowl['debug'] = false;

        $bowl->configure('prod', function (\Bowl\Bowl $bowl) {
            $bowl->share('foo', function () { return [1]; }, ['test']);
            $bowl->share('bar', function () { return [1]; }, ['test']);
            $bowl->share('boo', function () { return [1]; }, ['boo']);
        });
        $bowl->configure('dev', function (\Bowl\Bowl $bowl) {
            $bowl['debug'] = true;
            $bowl->share('test', function () {
                return [2];
            });
        });

        $bowl->share('baz', function () { return [1]; }, ['test']);

        $bowl->env('prod');

        $this->assertCount(3, $bowl->getTaggedServices('test'));
        $this->assertCount(1, $bowl->getTaggedServices('boo'));
    }

    /**
     * Tests exception
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidServiceName()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->get('foo.bar');
    }

    /**
     * Tests exception
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidServiceNameOnExtend()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->extend('foo.bar', function () {});
    }

    /**
     * Tests exception
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidTag()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->getTaggedServices('foo.bar');
    }

    /**
     * Tests exception
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidEnv()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->env('prod');
    }

    /**
     * Test env() can be called twice if the environment is the same
     */
    public function testSwitchToSameEnv()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->configure('prod', function () {});
        $bowl->env('prod');
        $bowl->env('prod');
    }

    /**
     * Tests exception
     *
     * @expectedException LogicException
     */
    public function testSwitchToDifferentEnv()
    {
        $bowl = new \Bowl\Bowl();
        $bowl->configure('prod', function () {});
        $bowl->configure('dev', function () {});
        $bowl->env('prod');
        $bowl->env('dev');
    }

    /**
     * Tests Bowl as an iterator
     */
    public function testIterator()
    {
        $bowl = new \Bowl\Bowl(['foo' => 'bar', 'baz' => true]);

        $this->assertEquals(['foo' => 'bar', 'baz' => true], iterator_to_array($bowl));
    }

} 