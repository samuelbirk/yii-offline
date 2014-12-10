<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that index page works');
//Debug::debug(Yii::$app->homeUrl);
$I->amOnPage('/');
$I->see('Welcome to OrionShoulders!');
$I->seeLink('My Projects');
$I->seeLink('Contact');
$I->click('My Projects');
$I->see('data-entry/index');
