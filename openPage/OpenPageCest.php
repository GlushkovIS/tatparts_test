<?php

/**
 * Класс содержит функционал для тестирования страниц проекта на код ответа и наличие ошибок на страницах.
 *
 * Class OpenPageCest
 * @package AutoResource\Tests\OpenPage
 */
class OpenPageCest
{
    /**
     * Имена коннекторов БД из acceptance.suite
     */
    const DB_NAME_AR = 'db_ar';
    const DB_NAME_AUTOPRICE = 'db_autoprice';

    const CLIENT_LOGIN = "testme";
    const CLIENT_PASS = "123";

    const LINK_ADD = "//a[./img[contains(@src,'ar_add.png')]]";

    private $flagError = false;
    private $errorLog = "------------------------------\nНайденные страницы с ошибками\n";
    private $count = 1;

    /**
     * Метод выполняется перед тестом.
     * Включает через интерфейс debug mode на проекте (debug_mode = 1)
     *
     * @param OpenPageTester $I
     */
    public function beforeTest(OpenPageTester $I)
    {
        $I->wantTo('Подготовка');
        $I->amOnPage('/admin/settings.html');
        $I->seeElement("//div[@data-key='SessionSettings']");
        $I->click("//div[@data-key='SessionSettings']");
        $I->selectOption('select[name=debug_mode]', '1');
        $I->click("input[name='saveSettings_SessionSettings']");
        $I->seeElement("div[class='notice']");
    }

    /**
     * Тест, который перебирает страницы проекта и проверяет их код ответа и наличие ошибок на страницах.
     *
     * @param OpenPageTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function checkOpenPage(OpenPageTester $I)
    {
        $I->wantTo('Проверка страниц проекта на работоспособность');
        $arResUrl = [];
        $arResUrl = $this->getUrlListFromDB($I, "_common_objects_structure", $arResUrl);
        $arResUrl = $this->getUrlListFromDB($I, "_objects_structure", $arResUrl);

        $arResUrl = $this->verifyUrlList($arResUrl);

        $arrayLength = count($arResUrl);
        print_r("Основных страниц: " . $arrayLength . ".\nС основных страниц ещё берутся дополнительные - по одной ссылке-странице на добавление\n");

        foreach ($arResUrl as &$path) {
            $this->checkPageOnError($I, $path, true);
        }

        if ($this->flagError) {
            print_r($this->errorLog);
            $I->fail("Найдены страницы с ошибками");
        }
    }

    /**
     * Берет список страниц проекта из БД
     *
     * @param OpenPageTester $I
     * @param string $tableName - имя таблицы из БД, содержащей страницы
     * @param array $arResUrl - массив, в который заносятся страницы
     *
     * @return array
     */
    protected function getUrlListFromDB(OpenPageTester $I, $tableName, array $arResUrl)
    {
        $I->amConnectedToDB(self::DB_NAME_AR);
        $arUrlFromDB = $I->executeSql("	
SELECT o1.str_url AS str_url 
FROM " . $tableName . " o1
LEFT JOIN " . $tableName . " o2 ON o2.str_left < o1.str_left AND o2.str_right > o1.str_right AND o2.str_published != 'Y'
WHERE o1.str_url IS NOT NULL 
    AND o1.str_url NOT LIKE '/ajax/%'
    AND o1.str_url NOT LIKE '/_ajax/%'
    AND o1.str_url NOT LIKE '/json/%'
    AND o1.str_url NOT LIKE '#'
    AND o1.str_published = 'Y'
    AND o2.str_id IS NULL
    AND o1.str_obt_id != 'sysnode'
		");

        $arUrl = [];
        array_walk_recursive($arUrlFromDB, function ($item) use (&$arUrl) {
            $arUrl[] = $item;
        });

        array_walk($arUrl, array($this, 'cleanUrlFromRegexp'));

        $arUrl = array_values(array_unique($arUrl));

        foreach ($arUrl as &$url) {
            $this->getTransformUrl($url, $arResUrl);
        }

        return $arResUrl;
    }

    /**
     * Чистка URL от синтаксиса регулярных выражений. Убираются следующие сочетания символов: * ([^/]+)/, .*, (.*), (/), ?, [, ]
     *
     * @param $url - url, который чиститься
     */
    protected function cleanUrlFromRegexp(&$url)
    {
        $url = str_replace(array("?.html", "\.html"), ".html", $url);
        $url = preg_replace(array("<(\.\*)+>", "<\?$>", "<(\[)?(\]\?)?(\])?(\\\\)?>", "<(\((\[)?\^\/(\])?\+\)\/)+>"),
            "", $url);
        $url = str_replace(array("()"), "", $url);
        $url = str_replace("(/)?", "/", $url);

        $url = preg_replace('<\(0-9\*\)>', "0", $url);
    }

    /**
     * Разжложение страниц, содержащих регулярки, на все возможные варианты
     * Пример: /admin/(eshop|providers)/ref_multiple.html -> /admin/eshop/ref_multiple.html и /admin/providers/ref_multiple.html
     *
     * @param $transformUrl
     * @param $arResUrl
     */
    protected function getTransformUrl($transformUrl, &$arResUrl)
    {
        $pos1 = strpos($transformUrl, "(");
        if ($pos1 === false) {
            $arResUrl[] = $transformUrl;
            return;
        }

        $pos1++;
        $pos2 = strpos($transformUrl, ")") - $pos1;
        $cutStr = mb_substr($transformUrl, $pos1, $pos2);

        if (strpos($cutStr, "|") === false) {
            $urlWithReplace = str_replace("(" . $cutStr . ")", "", $transformUrl);
            $this->getTransformUrl($urlWithReplace, $arResUrl);
            $urlWithReplace = preg_replace('<\)+|\(+>', "", $transformUrl);
            $this->getTransformUrl($urlWithReplace, $arResUrl);

            return;
        }

        $arStr = explode("|", $cutStr);
        foreach ($arStr as &$str) {
            $urlWithReplace = str_replace("(" . $cutStr . ")", $str, $transformUrl);
            $this->getTransformUrl($urlWithReplace, $arResUrl);
        }

    }

    /**
     * Удаление из общего списка тех url, которые пока не надо проверять
     *
     * @param $arUrl - общий список url
     *
     * @return array
     */
    protected function verifyUrlList($arUrl)
    {
        $arUrl = array_values(array_diff($arUrl, $this->getArDeletedPages()));

        foreach ($arUrl as &$url) {
            if (strpos($url, "http://") !== false || strpos($url, "https://") !== false) {
                unset ($url);
            }
        }
        $arUrl = array_values(array_unique($arUrl));
        return $arUrl;
    }

    /**
     * Проверка страницы на ошибки. Если ошибки найдены - печать в консоль
     *
     * @param OpenPageTester $I
     * @param                $path - путь, часть url без домена
     * @param                $flag - выставляется false, если идет проверка страницы, взятой из ссылки add. Иначе - true
     *
     * @throws \Codeception\Exception\ModuleException
     */
    protected function checkPageOnError(OpenPageTester $I, $path, $flag)
    {
        $time1 = time();

        $this->httpAuthOnPage($I, $path);
        $I->amOnPage($path);

        //авторизуемся в клиентской части, если видим, что неавторизованы
        if ($I->seeElementExist("div#war-authorization__error")) {
            $I->amOnPage($path . "?userlogin=" . self::CLIENT_LOGIN . "&userpassword=" . self::CLIENT_PASS . "&loginform=1");
        }

        $errorOnPage = $this->findErrorOnPage($I, $path);

        $time2 = time();
        $diff = $time2 - $time1;
        print_r("$this->count $path - $diff сек.\n");
        if ($errorOnPage) {
            $this->flagError = true;
            print_r("Error!\n");
        }
        $this->count++;

        //проверяем наличие ссылок add на странице
        if ($I->seeElementExist(self::LINK_ADD) && $flag) {

            $arLnkAdd = $this->getLinkAdd($I, $path, $this->getArUrlWithSingleLinkAdd());
            foreach ($arLnkAdd as &$lnkUrl) {
                //проверяем ссылки add. $flag = false, т.к.не надо проверять наличие ссылок add на найденных страницах
                $this->checkPageOnError($I, $lnkUrl, false);
            }
        }
    }

    /**
     * @param OpenPageTester $I
     * @param $path
     */
    protected function httpAuthOnPage(OpenPageTester $I, $path)
    {
        if(strcmp('/autoprice/auth.html', $path) === 0 || strcmp('/autoprice/auth-safe.html', $path) === 0) {
            $I->amHttpAuthenticated('autoprice', '123');
        } else {
            $I->amHttpAuthenticated('admin', '123');
        }
    }

    /**
     * Проверка на валидность кода ответа страницы и на ниличие на странице fatal error.
     *
     * @param OpenPageTester $I
     * @param                $path - путь, часть url без домена
     * @return bool
     *
     * @throws \Codeception\Exception\ModuleException
     */
    protected function findErrorOnPage(OpenPageTester $I, $path)
    {
        $msgError = '';
        $responseCode = $I->getResponseStatusCode();

        if ($responseCode === 200 ||
            $responseCode === 403 ||
            (strpos($path, "/error404.html") !== false & $responseCode === 404)) {
            return false;
        }

        if ($I->seeElementExist("//*[contains(text(),'Fatal error')]")) {
            $msgError = $I->grabTextFrom("//*[contains(text(),'Fatal error')]/ancestor::div[1]");
        }

        if ($I->seeElementExist("//div[@class='base-container']//div[@class='trace-file']")) {
            $msgError = "Fatal error! Подробнее см. на странице";
        }

        $this->addError($I, $path, $responseCode, $msgError);
        return true;
    }


    /** Функция возвращает массив ссылок на страницы, которые были взяты из кнопок "Добавить" на странице
     *
     * @param OpenPageTester $I
     * @param                $path - путь, часть url без домена
     * @param                $arUrlWithSingleLinkAdd - массив страниц (часть url без домена), с которых нужно взять только 1 ссылку "Добавить"
     *
     * @return array
     */
    protected function getLinkAdd(OpenPageTester $I, $path, $arUrlWithSingleLinkAdd)
    {
        $arLnkAdd = [];
        $count = 0;
        str_ireplace($arUrlWithSingleLinkAdd, "", $path, $count);
        if ($count > 0) {
            $arLnkAdd[] = $I->grabAttributeFrom(self::LINK_ADD, "href");
        } else {
            // иначе берем все ссылки на добавление
            $arLnkAdd = array_unique($I->grabMultiple(self::LINK_ADD, "href"));
        }

        return $arLnkAdd;
    }


    /**
     * Печать ошибки в консоль
     * @param OpenPageTester $I
     * @param                $responseCode - код ответа страницы
     * @param                $msgError - текст ошибки
     */
    protected function addError(OpenPageTester $I, $path, $responseCode, $msgError)
    {
        $this->errorLog .= "Страница с ошибкой. URL " . $path . "\nКод страницы: " . $responseCode . "\n";
        $snapshotName = date("Y-m-d_H-i-s_") . preg_replace("#[[:punct:]]#", "_", $path);
        $I->makeHtmlSnapshot($snapshotName);
        $this->errorLog .= "HtmlSnapshot в папке _output/debug/$snapshotName\n";
        if (!empty($msgError)) {
            $this->errorLog .= $msgError . "\n";
        }
        $this->errorLog .= "------------------------------\n";
    }

    /**
     * Массив страниц, которые не проверяются
     * @return array
     */
    protected function getArDeletedPages()
    {

        return [
            "/admin/assist/settings.html",
            "/admin/assist/trans.html",
            "/admin/chronopay/settings.html",
            "/admin/chronopay/trans.html",
            "/admin/payonlinesystem/settings.html",
            "/admin/payonlinesystem/trans.html",
            "/admin/paypal/settings.html",
            "/admin/paypal/trans.html",
            "/admin/qiwi/settings.html",
            "/admin/rbkmoney/settings.html",
            "/admin/rbkmoney/trans.html",
            "/admin/rbs/settings.html",
            "/admin/rbs/trans.html",
            "/admin/robokassa/settings.html",
            "/admin/robokassa/trans.html",
            "/admin/webmoney/settings.html",
            "/admin/webmoney/trans.html",
            "/news/",
            "/actions/",
            "/admin/catstat.html",
            "/admin/eshop/baskets/all_basket_header.html",
            "/admin/eshop/utils/car_directory.html",
            "/api",
            "/api/",
            "/createSitemap.html",
            "/info/car_application.html",
            "/admin/edit_url/",
            "/catalogs",
            "/catalogs/",
            "/admin/return_client_log.html",
            "/admin/start_price.html",
            "/tires-discs-search/ajax",
            "/tires-discs-search/ajax/",
            "/shop/basket_check.html",
            "/admin/eshop/basket_check.html",
            "/shop/personal/cars-list.html",
            "/shop/personal/delivery-list.html",
            "/choose-city.html",
            "/export/price.html",
            "/admin/questions/",
            "/totalcatalog/",
            "/admin/eshop/message_preview.html",
            "/service/selfprice/download_file",
            "/service/selfprice/download_file/",
            "https://www.tradesoft.ru/products/internet-magazin-avtozapchastej/",

            // когда мы доберемся до тестов по ним
            // надо будет сделать автоматический пулл из мастера в ФС
            // и тогда можно будет тестить ее как более полную сборку
            "/admin/autoorder/schedule/",
            "/admin/autoorder/mails/",
            "/service/autoorder/",
            "/multisearch/",
            "/multisearch-history/",
            "/shop/system-check.html",
            "/admin/self-pricelists/ai-prices/",
            "/update-basket-positions.html",
            "/shop/basket-research.html",
            "/info/autoresource.html",
            "/autoresource/common_info.html"
        ];

    }

    /**
     * массив страниц (часть url без домена), с которых нужно взять только 1 ссылку "Добавить"
     * @return array
     */
    protected function getArUrlWithSingleLinkAdd()
    {

        return [
            "/content/structure3.0.html",
            "/admin/access/control_panel.html",
            "/admin/content/structure_global.html",
            "/admin/content/structure.html"
        ];

    }
}