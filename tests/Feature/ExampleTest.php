<?php

test('welcome page returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('LuminaRAG');
    $response->assertSee('استخلص الحقيقة من');
    $response->assertSee('إنشاء حساب');
    $response->assertSee(asset('images/lumina-rag-hero.png'), false);
    $response->assertSee(route('login'), false);
    $response->assertSee(route('register'), false);
});
