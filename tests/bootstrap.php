<?php
require_once(
  dirname(__FILE__).'/../vendor/papaya/test-framework/src/PapayaTestCase.php'
);
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleTwitter' => dirname(__FILE__).'/../src'
  )
);