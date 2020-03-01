<?php
/**
 * Created by PhpStorm.
 * User: t.chervyakova
 * Date: 16.01.2019
 * Time: 16:11
 */

namespace AutoResource\Tests\Client\Search;

use AcceptanceTester;
use Pages\Client\SearchClientPage;

/**
 * Данный тест-комплект проверяет проценку
 *
 * @group smoke
 * @group SearchCest
 */
class SearchCest
{
    /**
     * KL9/AMC FILTER
     */
    protected $searchRowOriginal1;
    /**
     * KL9/MAHLE
     */
    protected $searchRowOriginal2;
    /**
     * J1332021/NIPPARTS
     */
    protected $searchRowAnalog1;
    /**
     * PS908/FILTRON
     */
    protected $searchRowAnalog2;

    public function _before(AcceptanceTester $I)
    {

        /** Заполнение форм для поиска */
        $this->searchRowOriginal1 = [
            'article' => 'KL9',
            'brand' => 'AMC FILTER'
        ];

        $this->searchRowOriginal2 = [
            'article' => 'KL9',
            'brand' => 'MAHLE'
        ];

        $this->searchRowAnalog1 = [
            'article' => 'J1332021',
            'brand' => 'NIPPARTS'
        ];

        $this->searchRowAnalog2 = [
            'article' => 'PS908',
            'brand' => 'FILTRON'
        ];
    }

    /**
    * ищем KL9/AMC FILTER
    * есть сообщение, что необходимо авторизоваться
    * в фильтре отображается AMC FILTER
     * есть деталь KL9/AMC FILTER
    *
    * выбираем в фильтре MAHLE
    * в фильтре отображается MAHLE
    * есть деталь KL9/MAHLE
    */
    public function testFilterByBrandWithoutAuth(AcceptanceTester $I)
    {
        $SearchPage = new SearchClientPage($I);

        $I->wantTo('Проверить работу фильтра по производителю без авторизации');

        $brandAmc = $this->searchRowOriginal1['brand'];
        $brandMahle = $this->searchRowOriginal2['brand'];

        $I->amGoingTo('Разлогиниться');
        $I->amOnPage('?logout');

        $SearchPage->goPage();
        $SearchPage->searchByArticle($this->searchRowOriginal1);

        $I->expect('в заглавном фильтре отображается производитель ' . $brandAmc);
        $SearchPage->checkSearchBrandIs($brandAmc);

        $I->expect('есть сообщение, что необходимо авторизоваться');
        $I->waitForElementVisible($SearchPage::MSG_PLEASE_AUTH);

        $I->expect('в результатах поиска есть искомая деталь');
        $SearchPage->seeDetail($this->searchRowOriginal1);

        $I->amGoingTo('выбираем в фильтре ' . $brandMahle);
        $SearchPage->selectBrandInFilter($brandMahle);

        $I->expect('в заглавном фильтре отображается производитель ' . $brandMahle);
        $SearchPage->checkSearchBrandIs($brandMahle);

        $I->expect('в результатах поиска есть искомая деталь');
        $SearchPage->seeDetail($this->searchRowOriginal2);
    }

    /**
     * ищем KL9/AMC FILTER
     * в фильтре отображается AMC FILTER
     * есть деталь KL9/AMC FILTER
     *
     * выбираем в фильтре MAHLE
     * в фильтре отображается MAHLE
     * есть деталь KL9/MAHLE
     */
    public function testFilterByBrandWithAuth(AcceptanceTester $I)
    {
        $SearchPage = new SearchClientPage($I);

        $I->wantTo('Авторизуемся под клиентом');
        $SearchPage->authClient();

        $I->wantTo('Проверить работу фильтра по производителю с авторизацией');
        $SearchPage->goPage();
        $SearchPage->searchByArticle($this->searchRowOriginal1);

        $I->expect('в заглавном фильтре отображается производитель ' . $this->searchRowOriginal1['brand']);
        $SearchPage->checkSearchBrandIs($this->searchRowOriginal1['brand']);

        $I->expect('нет сообщения, что необходимо авторизоваться');
        $I->waitForElementNotVisible($SearchPage::MSG_PLEASE_AUTH);

        $I->expect('в результатах поиска есть искомая деталь');
        $SearchPage->seeDetail($this->searchRowOriginal1);

        $I->amGoingTo('выбираем в фильтре ' . $this->searchRowOriginal2['brand']);
        $SearchPage->selectBrandInFilter($this->searchRowOriginal2['brand']);

        $I->expect('в заглавном фильтре отображается производитель ' . $this->searchRowOriginal2['brand']);
        $SearchPage->checkSearchBrandIs($this->searchRowOriginal2['brand']);

        $I->expect('в результатах поиска есть искомая деталь');
        $SearchPage->seeDetail($this->searchRowOriginal2);
    }

    /**
     * выбираем фильтр с аналогами
     * ищем KL9/MAHLE
     * выбираем производителя MAHLE
     *
     * есть раздел 'Запрашиваемый код детали'
     * Он содержит KL9/MAHLE
     *
     * есть раздел 'Неоригинальные аналоги'
     * Он содержит J1332021/NIPPARTS
     * Он содержит PS908/FILTRON
     */
    public function testFilterGroupWithAnalogs(AcceptanceTester $I)
    {
        $SearchPage = new SearchClientPage($I);

        $I->wantTo('Проверить работу фильтра с аналогами');

        $I->amGoingTo('выбираем фильтр с аналогами');
        $SearchPage->goPage();
        $SearchPage->selectSearchWithAnalogs();

        $I->amGoingTo('ищем KL9/MAHLE');
        $SearchPage->searchByArticle($this->searchRowOriginal2);
        $SearchPage->selectBrandInFilter($this->searchRowOriginal2['brand']);

        $I->expect('есть раздел Запрашиваемый код детали');
        $I->seeElement($SearchPage::BLOCK_REQUESTED_CODE);

        $I->expect('Он содержит KL9/MAHLE');
        $SearchPage->seeDetailInRequestedCodeBlock($this->searchRowOriginal2);

        $I->expect('есть раздел Неоригинальные аналоги');
        $I->seeElement($SearchPage::BLOCK_ANALOGS_UNORIGINAL);

        $I->expect('Он содержит J1332021/NIPPARTS');
        $SearchPage->seeDetailInAnalogBlock($this->searchRowAnalog1);
    }

    /**
     * выбираем фильтр без аналогов
     * ищем KL9/MAHLE
     * выбираем производителя MAHLE
     *
     * есть раздел 'Запрашиваемый код детали'
     * Он содержит KL9/MAHLE
     *
     * нет раздела 'Неоригинальные аналоги'
     * нет J1332021/NIPPARTS
     * нет PS908/FILTRON
     */
    public function testFilterGroupNoAnalogs(AcceptanceTester $I)
    {
        $SearchPage = new SearchClientPage($I);

        $I->wantTo('Проверить работу фильтра без аналогов');

        $I->amGoingTo('выбираем фильтр без аналогов');
        $SearchPage->goPage();
        $SearchPage->unselectSearchWithAnalogs();

        $I->amGoingTo('ищем KL9/MAHLE');
        $SearchPage->searchByArticle($this->searchRowOriginal2);
        $SearchPage->selectBrandInFilter($this->searchRowOriginal2['brand']);

        $I->expect('есть раздел Запрашиваемый код детали');
        $I->seeElement($SearchPage::BLOCK_REQUESTED_CODE);

        $I->expect('Он содержит KL9/MAHLE');
        $SearchPage->seeDetailInRequestedCodeBlock($this->searchRowOriginal2);

        $I->expect('нет раздела Неоригинальные аналоги');
        $I->dontSeeElement($SearchPage::BLOCK_ANALOGS_UNORIGINAL);

        $I->expect('нет детали J1332021/NIPPARTS');
        $SearchPage->dontSeeDetail($this->searchRowAnalog1);
    }
}
