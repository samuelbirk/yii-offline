<?php

namespace tests\codeception\unit\models;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;

class ConvertESTest extends TestCase
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
    public function testPooledSampleSD()
    {
        $TreatmentSD = 1;
        $TreatmentN = 10;
        $ControlSD = 1;
        $ControlN = 10;
        $converted = Yii::$app->convertES->PooledSampleSDs($TreatmentSD,$TreatmentN,$ControlSD,$ControlN);
        verify($converted)->equals(1);

        $TreatmentSD = 1.2;
        $TreatmentN = 130;
        $ControlSD = 4;
        $ControlN = 200;
        $converted = Yii::$app->convertES->PooledSampleSDs($TreatmentSD,$TreatmentN,$ControlSD,$ControlN);
        //\Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(3.2052548317701);

        $TreatmentSD = 1.2;
        $TreatmentN = 340;
        $ControlSD = 4;
        $ControlN = 105;
        $converted = Yii::$app->convertES->PooledSampleSDs($TreatmentSD,$TreatmentN,$ControlSD,$ControlN);
        //\Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(2.2041209096142);

        $TreatmentSD = 1.2;
        $TreatmentN = 500;
        $ControlSD = 4;
        $ControlN = 105;
        $converted = Yii::$app->convertES->PooledSampleSDs($TreatmentSD,$TreatmentN,$ControlSD,$ControlN);
        //\Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(1.9877568880783);
        
    }

    /**
    * Post-test only, with control group:
    * (Post-test Mean for treatment group – Post-test Mean for control group)/Pooled SD across groups
    */
    public function testPostTestBetweenGroups()
    {
        $TreatmentMean = 1;
        $TreatmentSD = 1;
        $TreatmentN = 10;
        $ControlMean = 1;
        $ControlSD = 1;
        $ControlN = 10;
        $converted = Yii::$app->convertES->PostTestBetweenGroups($TreatmentMean,$TreatmentSD,$TreatmentN,$ControlMean,$ControlSD,$ControlN);
        verify($converted)->equals(0);

        $TreatmentMean = -5;
        $TreatmentSD = 1.2;
        $TreatmentN = 130;
        $ControlMean = 15;
        $ControlSD = 4;
        $ControlN = 200;
        $converted = Yii::$app->convertES->PostTestBetweenGroups($TreatmentMean,$TreatmentSD,$TreatmentN,$ControlMean,$ControlSD,$ControlN);
        verify($converted)->equals(-6.2397534828628);

        $TreatmentMean = 5;
        $TreatmentSD = 1.2;
        $TreatmentN = 340;
        $ControlMean = 15;
        $ControlSD = 4;
        $ControlN = 105;
        $converted = Yii::$app->convertES->PostTestBetweenGroups($TreatmentMean,$TreatmentSD,$TreatmentN,$ControlMean,$ControlSD,$ControlN);
        verify($converted)->equals(-4.5369561880116);

        $TreatmentMean = 255;
        $TreatmentSD = 1.2;
        $TreatmentN = 340;
        $ControlMean = 15;
        $ControlSD = 4;
        $ControlN = 105;
        $converted = Yii::$app->convertES->PostTestBetweenGroups($TreatmentMean,$TreatmentSD,$TreatmentN,$ControlMean,$ControlSD,$ControlN);
        //\Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(108.88694851228);
        
    }

    /*
    Single group, pretest-posttest:
    (Post-test Mean for treatment group – pre-test Mean for treatement group)/treatement group pre-test SD 
    */

    function testPreTestPostTestTreatmentD(){

        $PreMean=2;
        $PreSD=1.03;
        $PostMean=3;
        $PostSD=1.19;
        $converted = Yii::$app->convertES->PreTestPostTestTreatmentD($PreMean,$PreSD,$PostMean,$PostSD);
        //\Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(0.97087378640777);
    }

    /*
    Pretest-Posttest, with control group:
    [(Post-test Mean for treatment group – Pre-test Mean for treatment group)/ treatment group pre-test SD] – [(Post-test Mean for control group – Pre-test Mean for control group)/ control group pre-test SD] 
    */
    function testPretestPosttestWControlD(){
        $TreatmentPostTestMean = 5;
        $TreatmentPreTestMean=1.35;
        $TreatmentPreTestSD=2;
        $ControlPreTestMean=1.47;
        $ControlPreTestSD=2;
        $ControlPostTestMean=1.35;

        $converted = Yii::$app->convertES->PretestPosttestWControlD($TreatmentPostTestMean,$TreatmentPreTestMean, $TreatmentPreTestSD,$ControlPreTestMean,$ControlPreTestSD,$ControlPostTestMean);
        \Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(1.8849999999999998);
    }

    /*
    Once ds have been converted to rs, they are really point-biserial rs except in the single group design and should be converted to biserial correlations as follows:

    From H&S 2004 formula 7.17 and 7.18 (p. 280):

    r corrected = ar/(sqrt[(a squared – 1)(r squared) + 1]),

    where a = sqrt[(.25/pq)],

    where p = percent in treatment group (i.e., .56), q = percent in control group (i.e., 1-p).
    */

    function testrCorrected(){
        $r=.15;
        $TreatmentN = 100;
        $ControlN = 200;
        $converted = Yii::$app->convertES->rCorrected($r,$TreatmentN,$ControlN);
        \Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(0.44502135879073423);
    }

    /*
    I think this formula is used in the excel spreadsheet that comes with the H&S 2004 book. Robbins used this formula BEFORE combining studies meta-anlytically (thus, it increased the uncorrected mean correlation and the corrected mean correlation).  I would suggest doing the same.  Just make sure we don’t report this corrected correlation as the uncorrected correlation in the appendix.  

    When using this formula, H&S say you’re supposed to correct the sampling error variance of the corrected correlation (see p. 280) or it will overestimate the SD of rho (i.e., the corrected correlation) and your results will be conservative.  However, I’ve never seen it mentioned in a manuscript.  I say we don’t worry about it b/c I’d rather just have more conservative (i.e. overestimated) sampling error variance than deal with the formula.  If you did want to correct the sampling error variance, you’d want to enter the r first and then correct the mean corrected correlation using the formula above and correct the sampling error variance of the corrected correlation with this formula (see p. 280):

    Sampling error variance of the corrected correlation = ((corrected r/uncorrected r)squared)*sampling error variance of the uncorrected correlation
    */

    function testSamplingErrorVarianceOfTheCorrectedCorrelation(){
        $r=.15;
        $TreatmentN = 100;
        $ControlN = 200;
        $correctedR = Yii::$app->convertES->rCorrected($r,$TreatmentN,$ControlN);
        verify($correctedR)->equals(0.44502135879073423);
        $uncorrectedR = $r;
        $SamplingErrorVarianceOfTheUncorrectedCorrelation = .56;

        $converted = Yii::$app->convertES->SamplingErrorVarianceOfTheCorrectedCorrelation($correctedR,$uncorrectedR,$SamplingErrorVarianceOfTheUncorrectedCorrelation);
        //\Codeception\Util\Debug::debug($converted);
         verify($converted)->equals(4.9290953545232);
    }

    /**
    * From Comprehensive Meta Analysis Chapter 7
    * r = d / sqrt(d^2 +a);
    * where a = (n1+n2)/n1*n2
    **/
    function testConvertD2R(){
        $d=1.56;
        $n1=100;
        $n2=100;
        $converted = Yii::$app->convertES->convertD2R($d,$n1,$n2);
        //\Codeception\Util\Debug::debug($converted);
        verify($converted)->equals(0.10964360316276);
    }
}
