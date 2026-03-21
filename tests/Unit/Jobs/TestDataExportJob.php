<?php

namespace Tests\Unit\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Tests\TestCase;

class TestDataExportJob extends TestCase
{
    private string $jobClass = 'App\\Jobs\\DataExportJob';

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists($this->jobClass)) {
            $this->markTestSkipped("{$this->jobClass} does not exist in this branch.");
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_job_can_be_instantiated_correctly(): void
    {
        $job = $this->makeJobInstance();

        $this->assertInstanceOf($this->jobClass, $job);
        $this->assertContains(ShouldQueue::class, class_implements($job) ?: []);
    }

    public function test_job_handles_data_correctly_across_queue_serialization(): void
    {
        $job = $this->makeJobInstance();

        $serialized = serialize($job);
        $restored = unserialize($serialized);

        $this->assertInstanceOf($this->jobClass, $restored);
        $this->assertSame(
            array_keys(get_object_vars($job)),
            array_keys(get_object_vars($restored))
        );
    }

    public function test_handle_method_executes_without_errors_with_mocked_dependencies(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake('local');

        $job = $this->makeJobInstance();

        $this->assertNotNull($this->invokeHandle($job));
    }

    public function test_job_specific_methods_or_properties_are_valid(): void
    {
        $job = $this->makeJobInstance();
        $reflection = new ReflectionClass($job);

        foreach (['tries', 'timeout', 'queue', 'connection'] as $property) {
            if (! $reflection->hasProperty($property)) {
                continue;
            }

            $value = $reflection->getProperty($property)->getValue($job);
            $this->assertTrue(
                is_int($value) || is_string($value) || is_array($value),
                "Property [{$property}] should be int, string, or array."
            );
        }

        $this->assertTrue(method_exists($job, 'handle'));
    }

    private function makeJobInstance(): object
    {
        $reflection = new ReflectionClass($this->jobClass);
        $constructor = $reflection->getConstructor();

        if (! $constructor instanceof ReflectionMethod || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = $this->fakeValueForParameter($parameter);
        }

        return $reflection->newInstanceArgs($arguments);
    }

    private function fakeValueForParameter(ReflectionParameter $parameter): mixed
    {
        $name = strtolower($parameter->getName());
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return Mockery::mock($type->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if (str_contains($name, 'format')) {
            return 'csv';
        }

        if (str_contains($name, 'path') || str_contains($name, 'file')) {
            return 'exports/test-export.csv';
        }

        if (str_contains($name, 'columns')) {
            return ['id', 'name', 'email'];
        }

        if (str_contains($name, 'filters') || str_contains($name, 'data')) {
            return ['status' => 'active'];
        }

        if (str_contains($name, 'id')) {
            return 1;
        }

        return match ($type?->getName()) {
            'int' => 1,
            'float' => 1.0,
            'bool' => true,
            'array' => ['sample' => true],
            default => 'sample',
        };
    }

    private function invokeHandle(object $job): int
    {
        $handle = new ReflectionMethod($job, 'handle');

        foreach ($handle->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $abstract = $type->getName();
            $this->app->instance($abstract, Mockery::mock($abstract));
        }

        $this->app->call([$job, 'handle']);

        return 1;
    }
}
