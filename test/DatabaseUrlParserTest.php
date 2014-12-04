<?php
require dirname(__FILE__).'/../src/DatabaseUrlParser.php';

class DatabaseUrlParserTest extends PHPUnit_Framework_TestCase {
    public function testConvertsIntoFormatThatRedBeanNeeds() {
        $parser = new DatabaseUrlParser();
        $this->assertEquals(
            [
                'connection' => 'mysql:host=127.0.0.1:3306;dbname=pong_matcher_slim_development',
                'user' => 'someuser',
                'pass' => 'somepass'
            ],
            $parser->toRedBean('mysql2://someuser:somepass@127.0.0.1:3306/pong_matcher_slim_development')
        );
    }
}
