<?php

namespace Flugg\Responder\Tests\Feature;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Contracts\Transformers\TransformerResolver;
use Flugg\Responder\Serializers\SuccessSerializer;
use Flugg\Responder\Tests\Product;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Testing\Constraints\ArraySubset;

/**
 * Feature tests asserting that you can transform response data of success responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class TransformResponseDataTest extends TestCase
{
    /**
     * Assert that you can transform the response data using a basic closure transformer.
     */
    public function testTransformDataUsingClosure(): void
    {
        $response = responder()->success($product = Product::create(['name' => 'foo']), function ($product) {
            return [
                'name' => strtoupper($product->name),
            ];
        })->respond();

        $this->assertEquals($this->responseData(['name' => 'FOO']), $response->getData(true));
    }

    /**
     * Assert that you can transform the response data using a dedicated transformer class.
     */
    public function testTransformDataUsingTransformerClass(): void
    {
        $response = responder()->success(Product::create(['name' => 'foo']), new ProductNameTransformer())->respond();

        $this->assertEquals($this->responseData(['name' => 'FOO']), $response->getData(true));
    }

    /**
     * Assert that you can transform the response data using a transformer resolved from
     * class name.
     */
    public function testTransformDataUsingTransformerClassName(): void
    {
        $response = responder()->success(Product::create(['name' => 'foo']), ProductNameTransformer::class)->respond();

        $this->assertEquals($this->responseData(['name' => 'FOO']), $response->getData(true));
    }

    /**
     * Assert that you can transform the response data using a transformer binding using
     * a closure transformer.
     */
    public function testTransformDataUsingTransformerBindingUsingClosure(): void
    {
        $response = responder()->success(ProductWithClosureTransformer::create(['name' => 'foo']))->respond();

        $this->assertEquals($this->responseData(['name' => 'FOO']), $response->getData(true));
    }

    /**
     * Assert that you can transform the response data using a transformer binding with a
     * dedicated transformer class.
     */
    public function testTransformDataUsingTransformerBindingUsingTransformerClass(): void
    {
        $response = responder()->success((ProductWithTransformerClass::create(['name' => 'foo'])))->respond();

        $this->assertEquals($this->responseData(['name' => 'FOO']), $response->getData(true));
    }

    /**
     * Assert that you can transform the response data using a transformer binding with a
     * dedicated transformer class resolved from the container using the class name.
     */
    public function testTransformDataUsingTransformerBindingUsingTransformerClassName(): void
    {
        $response = responder()->success((ProductWithTransformerClassName::create(['name' => 'foo'])))->respond();

        $this->assertEquals($this->responseData(['name' => 'FOO']), $response->getData(true));
    }

    /**
     * Assert that you can transform the response data using a transformer binding set
     * using the transformer resolver service directly.
     */
    public function testTransformDataUsingTransformerBindingUsingTransformerResolver(): void
    {
        $this->app->make(TransformerResolver::class)->bind([
            Product::class => ProductNameTransformer::class,
        ]);

        $response = responder()->success(Product::create(['name' => 'foo']))->respond();

        $this->assertEquals($this->responseData(['name' => 'FOO']), $response->getData(true));
    }

    /**
     * Assert that it resolves a resource key from the table name. The resource key is only
     * accessible from the serializer, so we use a dummy serializer to assert for it.
     */
    public function testItResolvesResourceKeyFromTableName(): void
    {
        $response = responder()->success($this->product)->serializer(ResourceKeySerializer::class)->respond();

        $this->assertTrue(
            (
            new ArraySubset(['products' => $this->product->toArray()]))->evaluate($response->getData(true), '', true)
        );
    }

    /**
     * Assert that you can set the resource key for the transformation.
     */
    public function testSetResourceKeyOnResponse(): void
    {
        $response = responder()
            ->success($this->product, null, 'foo')
            ->serializer(ResourceKeySerializer::class)
            ->respond();


        $this->assertTrue(
            (new ArraySubset(['foo' => $this->product->toArray()]))->evaluate($response->getData(true), '', true)
        );
    }

    /**
     * Assert that you can set the resource key on the actual model.
     */
    public function testSetResourceKeyOnModel(): void
    {
        $response = responder()
            ->success($product = ProductWithResourceKey::create())
            ->serializer(ResourceKeySerializer::class)
            ->respond();


        $this->assertTrue(
            (new ArraySubset(['foo' => $product->toArray()]))->evaluate($response->getData(true), '', true)
        );
    }
}

class ProductNameTransformer extends Transformer
{
    public function transform(Product $product)
    {
        return [
            'name' => strtoupper($product->name),
        ];
    }
}

class ResourceKeySerializer extends SuccessSerializer
{
    public function item($resourceKey, array $data): array
    {
        return [$resourceKey => $data];
    }
}

class ProductWithClosureTransformer extends Product implements Transformable
{
    public function transformer()
    {
        return function ($product) {
            return [
                'name' => strtoupper($product->name),
            ];
        };
    }
}

class ProductWithTransformerClass extends Product implements Transformable
{
    public function transformer()
    {
        return new ProductNameTransformer();
    }
}

class ProductWithTransformerClassName extends Product implements Transformable
{
    public function transformer()
    {
        return ProductNameTransformer::class;
    }
}

class ProductWithResourceKey extends Product
{
    public function getResourceKey()
    {
        return 'foo';
    }
}
