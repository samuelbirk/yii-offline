<?php

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that home page works');
$I->amOnPage(Yii::$app->homeUrl);
$I->see('Welcome to OrionShoulders!');
$I->seeLink('My Projects');
$I->seeLink('Contact');
