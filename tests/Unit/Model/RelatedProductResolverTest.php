<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Model;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Mockery;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config\Source\ProductSource;
use Tweakwise\TweakwiseJs\Model\RelatedProductResolver;

class RelatedProductResolverTest extends Unit
{
    protected UnitTester $tester;

    private Registry|MockInterface $registry;

    private Session|MockInterface $checkoutSession;

    private ProductRepositoryInterface|MockInterface $productRepository;

    private Http|MockInterface $request;

    private Data|MockInterface $dataHelper;

    private ExportConfig|MockInterface $exportConfig;

    private LoggerInterface|MockInterface $logger;

    private RelatedProductResolver $subject;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->registry = Mockery::mock(Registry::class);
        $this->checkoutSession = Mockery::mock(Session::class);
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->request = Mockery::mock(Http::class);
        $this->dataHelper = Mockery::mock(Data::class);
        $this->exportConfig = Mockery::mock(ExportConfig::class);
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->subject = new RelatedProductResolver(
            $this->registry,
            $this->checkoutSession,
            $this->productRepository,
            $this->request,
            $this->dataHelper,
            $this->exportConfig,
            $this->logger
        );
    }

    /**
     * @return void
     */
    public function _after(): void
    {
        Mockery::close();
    }

    /**
     * @param int $id
     * @param string $typeId
     * @param string $sku
     * @return Product|MockInterface
     */
    private function createProduct(int $id, string $typeId = 'simple', string $sku = 'sku'): Product|MockInterface
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn($id);
        $product->shouldReceive('getTypeId')->andReturn($typeId);
        $product->shouldReceive('getSku')->andReturn($sku);

        return $product;
    }

    /**
     * @param Product $product
     * @param string $typeId
     * @param Item[] $children
     * @return Item|MockInterface
     */
    private function createCartItem(Product $product, string $typeId = 'simple', array $children = []): Item|MockInterface
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getProductId')->andReturn($product->getId());
        $item->shouldReceive('getProductType')->andReturn($typeId);
        $item->shouldReceive('getProduct')->andReturn($product);
        $item->shouldReceive('getChildren')->andReturn($children);

        return $item;
    }

    /**
     * @param Item[] $items
     * @param int|null $lastAddedProductId
     * @return void
     */
    private function mockCart(array $items, ?int $lastAddedProductId): void
    {
        $this->request->shouldReceive('getFullActionName')->andReturn('checkout_cart_index');
        $quote = Mockery::mock(Quote::class);
        $quote->shouldReceive('getAllVisibleItems')->andReturn($items);
        $this->checkoutSession->shouldReceive('getQuote')->andReturn($quote);
        $this->checkoutSession->shouldReceive('getLastAddedProductId')->with(false)->andReturn($lastAddedProductId);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testAutomaticUsesCurrentProductOnProductPage(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturn($this->createProduct(5, 'configurable'));
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->with(5, 'configurable')->andReturn('11-5');

        $this->assertSame('11-5', $this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testAutomaticReturnsNullOnPageWithoutProduct(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturnNull();
        $this->request->shouldReceive('getFullActionName')->andReturn('cms_page_view');

        $this->assertNull($this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testAutomaticInCartUsesLastAddedProduct(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturnNull();
        $first = $this->createCartItem($this->createProduct(1));
        $last = $this->createCartItem($this->createProduct(2));
        $this->mockCart([$first, $last], 2);
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->with(2, 'simple')->andReturn('2');

        $this->assertSame('2', $this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testAutomaticInCartFallsBackToFirstItemWhenLastAddedIsGone(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturnNull();
        $first = $this->createCartItem($this->createProduct(1));
        $second = $this->createCartItem($this->createProduct(2));
        $this->mockCart([$first, $second], 99);
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->with(1, 'simple')->andReturn('1');

        $this->assertSame('1', $this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testAutomaticInEmptyCartReturnsNull(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturnNull();
        $this->mockCart([], null);

        $this->assertNull($this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testAutomaticInCartUsesSimpleChildWithGroupedExport(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturnNull();
        $child = $this->createCartItem($this->createProduct(7, 'simple'));
        $item = $this->createCartItem($this->createProduct(5, 'configurable'), 'configurable', [$child]);
        $this->mockCart([$item], 5);
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(true);
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->with(7, 'simple')->andReturn('7-5');

        $this->assertSame('7-5', $this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testAutomaticInCartUsesConfigurableWithoutGroupedExport(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturnNull();
        $child = $this->createCartItem($this->createProduct(7, 'simple'));
        $item = $this->createCartItem($this->createProduct(5, 'configurable'), 'configurable', [$child]);
        $this->mockCart([$item], 5);
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(false);
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->with(5, 'configurable')->andReturn('5');

        $this->assertSame('5', $this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testCustomResolvesProductBySku(): void
    {
        $this->productRepository->shouldReceive('get')->with('ABC-1')->andReturn($this->createProduct(9, 'simple', 'ABC-1'));
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->with(9, 'simple')->andReturn('9');

        $this->assertSame('9', $this->subject->resolve(ProductSource::CUSTOM, ' ABC-1 '));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testCustomReturnsNullForUnknownSku(): void
    {
        $this->productRepository->shouldReceive('get')->with('NOPE')->andThrow(new NoSuchEntityException());

        $this->assertNull($this->subject->resolve(ProductSource::CUSTOM, 'NOPE'));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testCustomReturnsNullForEmptySku(): void
    {
        $this->assertNull($this->subject->resolve(ProductSource::CUSTOM, ''));
        $this->assertNull($this->subject->resolve(ProductSource::CUSTOM, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testReturnsNullAndLogsWhenNoExportKeyIsFound(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturn($this->createProduct(5, 'simple', 'SKU5'));
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->andReturn('0');
        $this->logger->shouldReceive('debug')->once();

        $this->assertNull($this->subject->resolve(ProductSource::AUTOMATIC, null));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\RelatedProductResolver::resolve
     * @return void
     */
    public function testReturnsNullAndLogsWhenExportKeyThrows(): void
    {
        $this->registry->shouldReceive('registry')->with('current_product')->andReturn($this->createProduct(5, 'simple', 'SKU5'));
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->andThrow(new NoSuchEntityException());
        $this->logger->shouldReceive('debug')->once();

        $this->assertNull($this->subject->resolve(ProductSource::AUTOMATIC, null));
    }
}
