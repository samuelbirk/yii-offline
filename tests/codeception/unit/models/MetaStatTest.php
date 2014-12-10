<?php

namespace tests\codeception\unit\models;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;

class MetaStatTest extends TestCase
{
    use Specify;

    protected function setUp()
    {
        parent::setUp();
        /*Yii::$app->mailer->fileTransportCallback = function ($mailer, $message) {
            return 'testing_message.eml';
        };*/
    }

    protected function tearDown()
    {
        //unlink($this->getMessageFile());
        parent::tearDown();
    }

    /*
    * Pooled SD formula (from pg 173 of Lipsey & Wilson):
    * Sqrt[((treatment N – 1)*treatment SD squared + (control N – 1)*control SD squared)/(treatment N + control N – 2)]
    */
    public function testHedges()
    {

        $r = array(.15,.25,.35,.44,.22);
        $n = array(100,200,250,50,85);
        $labels = array(1,2,3,4,5);
        $level=.95;
        $converted = Yii::$app->meta->Hedges($r, $n, $labels, $level);
        \Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(3.2052548317701);
    }
}