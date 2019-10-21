<?php
declare(strict_types=1);

namespace ElasticAdaptor\Tests\Unit\Indices;

use BadMethodCallException;
use ElasticAdaptor\Indices\Mapping;
use ElasticAdaptor\Support\Str;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ElasticAdaptor\Indices\Mapping
 * @uses   \ElasticAdaptor\Support\Str
 */
class MappingTest extends TestCase
{
    public function test_field_names_can_be_disabled(): void
    {
        $mapping = (new Mapping())->disableFieldNames();

        $this->assertSame([
            '_field_names' => [
                'enabled' => false
            ]
        ], $mapping->toArray());
    }

    public function test_field_names_can_be_enabled(): void
    {
        $mapping = (new Mapping())->enableFieldNames();

        $this->assertSame([
            '_field_names' => [
                'enabled' => true
            ]
        ], $mapping->toArray());
    }

    public function test_source_can_be_disabled(): void
    {
        $mapping = (new Mapping())->disableSource();

        $this->assertSame([
            '_source' => [
                'enabled' => false
            ]
        ], $mapping->toArray());
    }

    public function test_source_can_be_enabled(): void
    {
        $mapping = (new Mapping())->enableSource();

        $this->assertSame([
            '_source' => [
                'enabled' => true
            ]
        ], $mapping->toArray());
    }

    /**
     * @return array
     */
    public function callParametersProvider(): array
    {
        return [
            ['boolean', []],
            ['geoPoint', ['foo', ['null_value' => null]]],
            ['text', ['bar', ['boost' => 1], ['store' => true]]],
            ['keyword', ['foobar']],
        ];
    }

    /**
     * @dataProvider callParametersProvider
     * @testdox Test $method property magic setter
     * @param string $method
     * @param array $arguments
     */
    public function test_property_magic_setter(string $method, array $arguments): void
    {
        if (count($arguments) == 0 || count($arguments) > 2) {
            $this->expectException(BadMethodCallException::class);
        }

        $mapping = (new Mapping())->$method(...$arguments);

        $this->assertSame([
            'properties' => [
                $arguments[0] => ['type' => Str::toSnakeCase($method)] + ($arguments[1] ?? [])
            ]
        ], $mapping->toArray());
    }

    public function test_default_array_conversion(): void
    {
        $this->assertSame([], (new Mapping())->toArray());
    }

    public function test_configured_array_conversion(): void
    {
        $mapping = (new Mapping())
            ->disableFieldNames()
            ->enableSource()
            ->text('foo')
            ->boolean('bar', [
                'boost' => 1
            ]);

        $this->assertSame([
            '_field_names' => [
                'enabled' => false
            ],
            '_source' => [
                'enabled' => true
            ],
            'properties' => [
                'foo' => [
                    'type' => 'text'
                ],
                'bar' => [
                    'type' => 'boolean',
                    'boost' => 1
                ]
            ]
        ], $mapping->toArray());
    }
}
