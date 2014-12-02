<?php
require '../vendor/autoload.php';

R::setup(
    'mysql:host=localhost;dbname=pong_matcher_slim_development',
    'slimpong', 'slimpong'
);

$app = new \Slim\Slim();

$app->delete('/all', function() {
    R::wipe('matchrequest');
});

$app->put('/match_requests/:uuid', function($uuid) use($app) {
    $matchRequest = R::dispense('matchrequest');
    $attributes = json_decode($app->request->getBody());
    $matchRequest->uuid = $uuid;
    $matchRequest->player = $attributes->player;
    R::store($matchRequest);
    echo "{}";
});

$app->get('/match_requests/:uuid', function($uuid) {
    $matchRequest = R::findOne('matchrequest', "uuid = ?", [ $uuid ]);
    if ($matchRequest) {
        echo json_encode([
            'id' => $matchRequest->uuid,
            'player' => $matchRequest->player,
            'match_id' => $matchRequest->matchId
        ]);
    }
});

$app->run();
