<?php namespace modification;

use Pages\Admin\OrderPositionsAdminPage;
use Pages\Admin\OrdersAdminPage;
use Pages\Client\BasketClientPage;
use Pages\Client\MakeOrderClientPage;
use Pages\Client\MyOrdersClientPage;
use Pages\Client\SearchClientPage;

/**
 * 445822 Данный класс проверяет проверяет работу доработки “Замена направления в заказах”
 *
 * @group ReplaceDeliveryDirectionCest
 * @package modification
 * Class ReplaceDeliveryDirectionCest
 */
class ReplaceDeliveryDirectionCest
{
    const DB_NAME_AR = 'db_ar';

    /**
     * Заполнение форм для поиска
     */
    private $positionData = [
        'article' => 'KL9',
        'brand' => 'MAHLE',
        'name' => 'ФИЛЬТР ТОПЛИВНЫЙ',
        'term' => '4',
        'remains ' => '4',
        'price' => '844.00',
        'providerPrice' => '795.59',
        'provider' => 'Сокол-Авто ООО(#МСК#)',
        'crossArticle' => 'KL9',
        'crossBrand' => 'MAHLE',
        'nameAndBrand' => 'MAHLE, ФИЛЬТР ТОПЛИВНЫЙ'
    ];

    private $supplierDirection = 'TATPARTS 565';

    private $replaceDirection = 'Тестовое направление';

    private $newDirection = 'Киров';

    /**
     * @var SearchClientPage
     */
    private $searchClientPage;
    /**
     * @var MakeOrderClientPage
     */
    private $makeOrderClientPage;
    /**
     * @var BasketClientPage
     */
    private $basketClientPage;
    /**
     * @var MyOrdersClientPage
     */
    private $myOrdersClientPage;
    /**
     * @var OrderPositionsAdminPage
     */
    private $orderPositionsAdminPage;
    /**
     * @var OrdersAdminPage
     */
    private $ordersAdminPage;


    public function _before(\AcceptanceTester $I)
    {

    }

    public function prepareBeforeTestStart(\AcceptanceTester $I)
    {
        $I->wantTo('Выполнить подготовку к тестам');
        $this->searchClientPage = new SearchClientPage($I);
        $this->makeOrderClientPage = new MakeOrderClientPage($I);
        $this->basketClientPage = new BasketClientPage($I);
        $this->myOrdersClientPage = new MyOrdersClientPage($I);
        $this->orderPositionsAdminPage = new OrderPositionsAdminPage($I);
        $this->ordersAdminPage = new OrdersAdminPage($I);

        $I->amGoingTo('Добавить замену направления');
        $query = <<<SQL
INSERT INTO complex_search_replace
(cre_provider_id, cre_destination_replace, cre_hide, cre_last_time, cre_destination_replace_mode)
VALUES (NULL, "$this->replaceDirection", 1, NOW(), 1);
SQL;
        $I->amConnectedToDb(self::DB_NAME_AR);
        $I->executeSql($query);
        $I->clearAllCookies();

        $I->amGoingTo('Создать тестовый заказ');
        $makeOrderClientPage = $this->makeOrderClientPage;
        $makeOrderClientPage->resetLoginClient();
        $makeOrderClientPage->authClient();
        $this->testMakeOrder($I);

    }

    /**
     * Проверка, что направления в админке в колонках "Направление" и
     * "Направление которое ввидит клиент" не совпадают
     */
    public function directionsNotMatchAdmin(\AcceptanceTester $I)
    {
        $I->wantTo('Направления в админке не совпадают');

        $orderPositionsAdminPage = $this->orderPositionsAdminPage;
        $orderPositionsAdminPage->authAdmin();
        $orderPositionsAdminPage->goPage();
        $orderPositionsAdminPage->checkDisplaySupplierDirection($this->positionData, $this->supplierDirection);
        $orderPositionsAdminPage->checkDisplayClientDirection($this->positionData, $this->replaceDirection);
    }

    /**
     * Проверка, что направление позиции ЗК в КЧ соответствует ожидаемому
     */
    public function directionIsEqualToExpected(\AcceptanceTester $I)
    {
        $I->wantTo('Проверить направление в клиенской части');

        $makeOrderClientPage = $this->makeOrderClientPage;
        $makeOrderClientPage->resetLoginClient();
        $makeOrderClientPage->authClient();

        $myOrdersClientPage = $this->myOrdersClientPage;
        $myOrdersClientPage->goPage();
        $myOrdersClientPage->checkDisplayDirection($this->positionData, $this->replaceDirection);
    }

    /**
     * Проверка, что после изменения направления позиции ЗК в админке, направления
     * в колонках "Направление" и "Направление которое видит клиент" в АЧ и в КЧ
     * соотвествуют ожидаемым
     */
    public function directionsHaveNotChanged(\AcceptanceTester $I)
    {
        $I->wantTo('Изменить направление позиции ЗК');
        $orderPositionsAdminPage = $this->orderPositionsAdminPage;
        $orderPositionsAdminPage->authAdmin();
        $orderPositionsAdminPage->goPage();
        $orderPositionsAdminPage->changeDirection($this->positionData, $this->newDirection);

        $I->amGoingTo('Проверить направления в админке');
        $orderPositionsAdminPage->goPage();
        $orderPositionsAdminPage->checkDisplaySupplierDirection($this->positionData, $this->newDirection);
        $orderPositionsAdminPage->checkDisplayClientDirection($this->positionData, $this->replaceDirection);

        $I->amGoingTo('Проверить направление в клиентской части');
        $makeOrderClientPage = $this->makeOrderClientPage;
        $makeOrderClientPage->resetLoginClient();
        $makeOrderClientPage->authClient();

        $myOrdersClientPage = $this->myOrdersClientPage;
        $myOrdersClientPage->goPage();
        $myOrdersClientPage->checkDisplayDirection($this->positionData, $this->replaceDirection);
    }

    public function addReplaceDirectionAndCheck(\AcceptanceTester $I)
    {
        $I->wantTo('Изменить замену направления доставки');
        $query = <<<SQL
UPDATE complex_search_replace
  SET cre_destination_replace = "$this->newDirection"
WHERE cre_provider_id IS NULL;
SQL;
        $I->amConnectedToDb(self::DB_NAME_AR);
        $I->executeSql($query);
        $I->clearAllCookies();

        $searchClientPage = $this->searchClientPage;
        $searchClientPage->goPage();

        $I->amGoingTo('Проверить направление в поиске по коду КЧ');
        $searchClientPage->searchByArticle($this->positionData);
        $searchClientPage->selectBrandInFilter($this->positionData['brand']);
        $searchClientPage->checkDisplayDirection($this->positionData, $this->newDirection);

        $I->amGoingTo('Проверить направление в клиентской части');
        $makeOrderClientPage = $this->makeOrderClientPage;
        $makeOrderClientPage->resetLoginClient();
        $makeOrderClientPage->authClient();

        $myOrdersClientPage = $this->myOrdersClientPage;
        $myOrdersClientPage->goPage();
        $myOrdersClientPage->checkDisplayDirection($this->positionData, $this->replaceDirection);

        $I->amGoingTo('Проверить направление в административной части');
        $orderPositionsAdminPage = $this->orderPositionsAdminPage;
        $orderPositionsAdminPage->authAdmin();
        $orderPositionsAdminPage->goPage();
        $orderPositionsAdminPage->checkDisplayClientDirection($this->positionData, $this->replaceDirection);

    }

    public function clearTestData(\AcceptanceTester $I)
    {
        $I->wantTo('Очистить тестовые данные');
        $I->amConnectedToDb(self::DB_NAME_AR);
        $query = <<<SQL
DELETE FROM complex_search_replace
WHERE cre_destination_replace = "$this->newDirection";
SQL;
        $I->executeSql($query);

        $I->amGoingTo('Удалить тестовый заказ');
        $ordersAdminPage = $this->ordersAdminPage;
        $ordersAdminPage->authAdmin();
        $ordersAdminPage->goPage();
        $ordersAdminPage->deleteLastOrder();
    }

    /**
     * Оформляет тестовый заказ
     * @param \AcceptanceTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    private function testMakeOrder(\AcceptanceTester $I)
    {
        $I->wantTo('Оформить тестовый заказ');

        $basketClientPage = $this->basketClientPage;
        $basketClientPage->goPage();
        $basketClientPage->cleanBasket();

        $searchClientPage = $this->searchClientPage;
        $searchClientPage->goPage();

        $I->amGoingTo('Поиск по KL9, выбор бренда MAHLE');
        $searchClientPage->searchByArticle($this->positionData);
        $searchClientPage->selectBrandInFilter($this->positionData['brand']);

        $I->amGoingTo('Перейти в корзину для данной строки поиска');
        $searchClientPage->addToBasket($this->positionData);
        $searchClientPage->goBasket();


        $I->amGoingTo('Оформить заказ и перейти к нему без капчи');
        $basketClientPage->selectAllPositions();
        $basketClientPage->acceptOferta();
        $basketClientPage->makeOrder();

        $makeOrderClientPage = $this->makeOrderClientPage;
        $makeOrderClientPage->goToOrder();
        $makeOrderClientPage->selectPaymentType('наличный расчет');

        $I->amGoingTo('Сохранить заказ');
        $makeOrderClientPage->saveOrder();
        $makeOrderClientPage->checkOrderSuccess();
    }
}
