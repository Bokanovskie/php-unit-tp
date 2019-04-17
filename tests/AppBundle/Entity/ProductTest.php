<?php

namespace Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use AppBundle\Entity\Product;


class ProductTest extends TestCase
{
    /**
     * @dataProvider pricesForFoodProduct
     *
     * @param $price
     * @param $excpectedTva
     */
    public function testComputeTVAFoodProduct($price, $excpectedTva)
    {
        $product = new Product("A product", Product::FOOD_PRODUCT, $price);

        $this->assertSame($excpectedTva, $product->computeTVA());
    }

    public function testComputeTVAOtherPRoduct()
    {
        $product = new Product("A other product", "Other product type", 20);

        $this->assertSame(3.92, $product->computeTVA());
    }

    public function testNegativePriceComputeTVA()
    {
        $product = new Product("A product", Product::FOOD_PRODUCT, -20);

        $this->expectException('LogicException');

        $product->computeTVA();
    }

    public function pricesForFoodProduct()
    {
        return [
            [0, 0.0],
            [20, 1.1],
            [100, 5.5]
        ];
    }
}
