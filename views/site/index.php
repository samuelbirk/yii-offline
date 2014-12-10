<?php
/* @var $this yii\web\View */
use yii\helpers\Url;
$this->title = 'OrionShoulders';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Welcome to OFFLINE FIRST!</h1>

        <p class="lead">An amazing offline first PHP second utility.</p>

        <p><a class="btn btn-lg btn-success" href="<?= (Yii::$app->user->isGuest ? Url::to('@web/user/registration/register', true) : Url::to('@web/data-entry', true)); ?>">Get started with Yii</a></p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Advanced, Intuitive Interface</h2>

                <p>Orion Shoulders meta-analysis software offers an easy-to-use yet powerful interface, designed to walk you through the meta-analysis process and help you avoid unneccesary repetetive tasks.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/doc/">Yii Documentation &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Pioneered Visualization</h2>

                <p>We have developed the most progressive visualization tools designed to improve your review and analysis. You will be able to view the impact of each article on your analysis.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/forum/">Yii Forum &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>World-wide Access and Collaboration</h2>

                <p>As an online tool, Orion Shoulders provides a cross-platform service designed to be accessed from anywhere with Internet access. This will enhance collaboration efforts among research partners.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/extensions/">Yii Extensions &raquo;</a></p>
            </div>
        </div>

    </div>
</div>
