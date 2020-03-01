<?php
use Pages\Client\SearchClientPage;
use Pages\Client\MakeOrderClientPage;
use Pages\Client\BasketClientPage;

/**
 * Данный класс проверяет оформление заказа под авторизованным пользователем
 *
 * @group smoke
 * @group MakeOrderCest
 * Class MakeOrderCest
 */
class MakeOrderCest
{
    protected $searchRow;
    /**
     * @var SearchClientPage
     */
    protected $searchPage;
    /**
     * @var MakeOrderClientPage
     */
    protected $makeOrderPage;
    /**
     * @var BasketClientPage
     */
    protected $basketPage;

    public function _before(AcceptanceTester $I)
    {
        $this->searchPage = new \Pages\Client\SearchClientPage($I);
        $this->makeOrderPage = new \Pages\Client\MakeOrderClientPage($I);
        $this->basketPage =  new \Pages\Client\BasketClientPage($I);

        /**
         * Заполнение форм для поиска
         */
        $this->searchRow = [
            'article' => 'KL9',
            'brand' => 'MAHLE',
            'name' => 'Фильтр топливный BMW',
            'term' => 'наличие',
            'remains ' => '1',
            'price' => '100500.00',
            'providerPrice' => '100500.00',
            'provider' => 'test_prov',
            'crossArticle' => 'kl9',
            'crossBrand' => 'MAHLE'
        ];
    }

    public function prepareBeforeTestStart(AcceptanceTester $I)
    {
        $MakeOrderClientPage = $this->makeOrderPage;

        $I->wantTo('Выполнить подготовку к тестам');
        $MakeOrderClientPage->resetLoginClient();
        $MakeOrderClientPage->authClient();
    }

    /**
     * @depends prepareBeforeTestStart
     */
    public function testMakeOrder(AcceptanceTester $I)
    {
        $SearchClientPage = $this->searchPage;
        $BasketClientPage = $this->basketPage;
        $MakeOrderClientPage = $this->makeOrderPage;

        $I->wantTo('Проверить оформление заказа');

        $BasketClientPage->goPage();
        $BasketClientPage->cleanBasket();

        $SearchClientPage->goPage();

        $I->amGoingTo('Поиск по KL9, выбор бренда MAHLE');
        $SearchClientPage->searchByArticle($this->searchRow);
        $SearchClientPage->selectBrandInFilter($this->searchRow['brand']);

        $I->amGoingTo('Получить цифру цены (float) детали в Поиске');
        $searchPrice = $SearchClientPage->getPositionPrice($this->searchRow);

        $I->amGoingTo('Перейти в корзину для данной строки поиска');
        $SearchClientPage->addToBasket($this->searchRow);
        $SearchClientPage->goBasket();

        $I->amGoingTo('Получить цифру цены (float) детали в Корзине');
        $basketPrice = $BasketClientPage->getPositionPrice($this->searchRow);

        $I->expect('Цены в Поиске и в Корзине идентичны');
        $I->assertEquals($searchPrice, $basketPrice);

        $I->amGoingTo('Оформить заказ и перейти к нему без капчи');
        $BasketClientPage->selectAllPositions();
        $BasketClientPage->acceptOferta();
        $BasketClientPage->makeOrder();
        $MakeOrderClientPage->goToOrder();

        $I->amGoingTo('Получить цифру цены детали в Оформлении заказа');
        $orderPrice = $MakeOrderClientPage->getPositionPrice($this->searchRow);

        $I->expect('Цены в Корзине и Оформлении заказа идентичны');
        $I->assertEquals($orderPrice, $basketPrice);

        $MakeOrderClientPage->selectPaymentType('наличный расчет');

        $I->amGoingTo('Сохранить заказ');
        $MakeOrderClientPage->saveOrder();
        $MakeOrderClientPage->checkOrderSuccess();
    }
}
