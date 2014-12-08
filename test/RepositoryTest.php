<?php
require dirname(__FILE__).'/../src/MatchRequestRepository.php';
require dirname(__FILE__).'/../src/MatchRepository.php';

system('php -d date.timezone=UTC '.dirname(__FILE__).'/../vendor/bin/phinx migrate -e testing > /dev/null');
R::setup('sqlite:'.dirname(__FILE__).'/test.sqlite3');
R::freeze(true);

class RepositoryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $deleteMatchRequestRepo = new MatchRequestRepository();
        $deleteMatchRequestRepo->nuke();
    }

    public function testThatMatchRequestCanBeStoredAndRetrieved() {
        $storeRepo = new MatchRequestRepository();
        $attributes = [ 'uuid' => 'some-uuid', 'player' => 'some-player' ];
        $storeRepo->persist($attributes);

        $retrieveRepo = new MatchRequestRepository();
        $retrievedMatchRequest = $retrieveRepo->get('some-uuid');
        $this->assertEquals(
            array_merge($attributes, [ 'matchId' => null ]),
            $retrievedMatchRequest
        );
    }

    public function testThatTwoConsecutivelyStoredPlayersGetSameMatchId() {
        $repo = new MatchRequestRepository();
        $repo->persist([
            'uuid' => 'request1',
            'player' => 'player1'
        ]);

        $repo->persist([
            'uuid' => 'request2',
            'player' => 'player2'
        ]);

        $request1 = $repo->get('request1');
        $request2 = $repo->get('request2');

        $this->assertGreaterThan(8, strlen($request1['matchId']));
        $this->assertEquals($request1['matchId'], $request2['matchId']);
    }

    public function testThatMatchedPlayersCanBeRetrievedAsAMatch() {
        $matchRequestRepo = new MatchRequestRepository();
        $matchRepo = new MatchRepository();

        $matchRequestRepo->persist([
            'uuid' => 'request1',
            'player' => 'player1'
        ]);

        $matchRequestRepo->persist([
            'uuid' => 'request2',
            'player' => 'player2'
        ]);

        $request1 = $matchRequestRepo->get('request1');

        $match = $matchRepo->get($request1['matchId']);

        $this->assertEquals(
            [
                'uuid' => $request1['matchId'],
                'matchRequest1Uuid' => 'request1',
                'matchRequest2Uuid' => 'request2'
            ],
            $match
        );
    }

    public function testThatRequestingANonExistentMatchRequestReturnsNull() {
        $repo = new MatchRequestRepository();
        $matchRequest = $repo->get('madeupuuid');
        $this->assertNull($matchRequest);
    }

    public function testThatRequestingANonExistentMatchReturnsNull() {
        $repo = new MatchRepository();
        $match = $repo->get('madeupuuid');
        $this->assertNull($match);
    }
}
