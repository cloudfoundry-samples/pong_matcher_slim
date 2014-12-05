<?php
require dirname(__FILE__).'/../vendor/autoload.php';

class MatchRequestRepository {
    public function nuke() {
        R::wipe('matchrequest');
        R::wipe('participant');
    }

    public function persist($attributes) {
        $matchRequest = R::dispense('matchrequest');
        $matchRequest->uuid = $attributes['uuid'];
        $matchRequest->player = $attributes['player'];

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
            )', [ ':player' => $attributes['player'] ]
        );

        if ($opponentRequest) {
            $matchId = str_replace('.', '-', uniqid('', true));

            $participant1 = R::dispense('participant');
            $participant1->matchId = $matchId;
            $participant1->matchRequestUuid = $opponentRequest->uuid;
            $participant1->playerId = $opponentRequest->player;
            $participant1->opponentId = $attributes['player'];

            $participant2 = R::dispense('participant');
            $participant2->matchId = $matchId;
            $participant2->matchRequestUuid = $attributes['uuid'];
            $participant2->playerId = $attributes['player'];
            $participant2->opponentId = $opponentRequest->player;

            R::store($participant1);
            R::store($participant2);
        }

        R::store($matchRequest);
    }

    public function get($uuid) {
        $matchRequest = R::findOne('matchrequest', "uuid = ?", [ $uuid ]);

        if ($matchRequest) {
            $matchId = R::getCell("SELECT match_id
                FROM participant
                WHERE match_request_uuid = :match_request_uuid
                LIMIT 1", [ ':match_request_uuid' => $uuid ]);

            $attributes = [
                'uuid' => $uuid,
                'player' => $matchRequest->player
            ];

            return array_merge($attributes, [ 'matchId' => $matchId ]);
        }
    }
}
