<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // サーバーの側のテストを、画面のビルドの結果（public/build）や開発用のサーバーに頼らせない
        $this->withoutVite();
    }
}
