<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */

    /**
     * Метод заполнения селекта в кл.части
     * @param $field - xpath вида //div[@id='add_region_id_ds']
     * @param $value - выбираемое значение
     * @throws Exception
     */
    public function setSelectInClient($field, $value)
    {
        $I = $this;
        $I->click($field);
        $I->waitForElementVisible($field . "//li[.='" . $value . "']");
        $I->click($field . "//li[.='" . $value . "']");
        $I->waitForElementNotVisible($field . "//li[.='" . $value . "']");
    }
}
