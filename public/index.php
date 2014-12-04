<?php
require dirname(__FILE__).'/../vendor/autoload.php';
require dirname(__FILE__).'/../src/DatabaseUrlParser.php';

if (array_key_exists('DATABASE_URL', $_ENV)) {
    $databaseUrl = $_ENV['DATABASE_URL'];
} else {
    $databaseUrl = 'mysql2://slimpong:slimpong@127.0.0.1:3306/pong_matcher_slim_development';
}

$parser = new DatabaseUrlParser();
$parsedUrl = $parser->toRedBean($databaseUrl);

R::setup($parsedUrl['connection'], $parsedUrl['user'], $parsedUrl['pass']);

$app = new \Slim\Slim();

$app->delete('/all', function() {
    R::wipe('matchrequest');
    R::wipe('participant');
});

$app->put('/match_requests/:uuid', function($uuid) use($app) {
    $matchRequest = R::dispense('matchrequest');
    $attributes = json_decode($app->request->getBody());
    $matchRequest->uuid = $uuid;
    $matchRequest->player = $attributes->player;

    $opponentRequest = R::findOne(
        'matchrequest',
        'player <> :player
        AND uuid NOT IN (
            SELECT match_request_uuid
            FROM participant
        )
        AND player NOT IN (
            SELECT opponent_id
            FROM participant
            WHERE player_id = :player
        )', [ ':player' => $attributes->player ]
    );

    $matchId = str_replace('.', '-', uniqid('', true));

    if ($opponentRequest) {
        $participant1 = R::dispense('participant');
        $participant1->matchId = $matchId;
        $participant1->matchRequestUuid = $opponentRequest->uuid;
        $participant1->playerId = $opponentRequest->player;
        $participant1->opponentId = $matchRequest->player;

        $participant2 = R::dispense('participant');
        $participant2->matchId = $matchId;
        $participant2->matchRequestUuid = $matchRequest->uuid;
        $participant2->playerId = $matchRequest->player;
        $participant2->opponentId = $opponentRequest->player;

        R::store($participant1);
        R::store($participant2);
    }

    R::store($matchRequest);

    echo "{}";
});

$app->get('/match_requests/:uuid', function($uuid) use($app) {
    $matchRequest = R::findOne('matchrequest', "uuid = ?", [ $uuid ]);
    if ($matchRequest) {
        $matchId = R::getCell("SELECT match_id
                               FROM participant
                               WHERE match_request_uuid = :match_request_uuid
                               LIMIT 1", [ ':match_request_uuid' => $uuid ]);
        echo json_encode([
            'id' => $matchRequest->uuid,
            'player' => $matchRequest->player,
            'match_id' => $matchId
        ]);
    } else {
        $app->pass();
    }
});

$app->get('/matches/:uuid', function($uuid) use($app) {
    $participants = R::find('participant', 'match_id = ?', [ $uuid ]);
    if (empty($participants)) {
        $app->pass();
    } else {
        echo json_encode([
            'id' => array_values($participants)[0]->matchId,
            'match_request_1_id' => array_values($participants)[0]->matchRequestUuid,
            'match_request_2_id' => array_values($participants)[1]->matchRequestUuid
        ]);
    }
});

$app->run();
