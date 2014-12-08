<?php
require dirname(__FILE__).'/../vendor/autoload.php';

class MatchRepository {
    public function get($uuid) {
        $participants = R::find('participant', 'match_id = ?', [ $uuid ]);
        if (sizeof($participants) > 0) {
            return [
                'uuid' => array_values($participants)[0]->matchId,
                    'matchRequest1Uuid' => array_values($participants)[0]->matchRequestUuid,
                    'matchRequest2Uuid' => array_values($participants)[1]->matchRequestUuid
                ];
        }
    }
}
