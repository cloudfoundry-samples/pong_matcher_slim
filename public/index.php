<?php
require dirname(__FILE__).'/../vendor/autoload.php';
require dirname(__FILE__).'/../src/DatabaseUrlParser.php';
require dirname(__FILE__).'/../src/MatchRequestRepository.php';

if (array_key_exists('DATABASE_URL', $_ENV)) {
    $databaseUrl = $_ENV['DATABASE_URL'];
} else {
    $databaseUrl = 'mysql2://slimpong:slimpong@127.0.0.1:3306/pong_matcher_slim_development';
}

$parser = new DatabaseUrlParser();
$parsedUrl = $parser->toRedBean($databaseUrl);

R::setup($parsedUrl['connection'], $parsedUrl['user'], $parsedUrl['pass']);
R::freeze(true);

$app = new \Slim\Slim();
$matchRequestRepository = new MatchRequestRepository();

$app->delete('/all', function() use($matchRequestRepository) {
    $matchRequestRepository->nuke();
});

$app->put('/match_requests/:uuid', function($uuid) use($app, $matchRequestRepository) {
    $attributes = json_decode($app->request->getBody());

    $matchRequestRepository->persist([
        'uuid' => $uuid,
        'player' => $attributes->player
    ]);

    echo "{}";
});

$app->get('/match_requests/:uuid', function($uuid) use($app, $matchRequestRepository) {
    $matchRequest = $matchRequestRepository->get($uuid);
    if ($matchRequest) {
        echo json_encode([
            'id' => $matchRequest['uuid'],
            'player' => $matchRequest['player'],
            'match_id' => $matchRequest['matchId']
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

$app->post('/results', function() use($app) {
    $app->response->setStatus(201);
});

$app->run();
