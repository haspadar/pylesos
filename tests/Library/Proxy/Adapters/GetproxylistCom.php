<?php

class GetproxylistCom extends TestCase
{
    public function testAdapterName()
    {
        $this->assertEquals(
            'GetproxylistCom',
            \App\Library\Proxy\Adapters\GetproxylistCom::getAdapterName()
        );
    }
}
