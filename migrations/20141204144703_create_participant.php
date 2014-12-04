<?php

use Phinx\Migration\AbstractMigration;

class CreateParticipant extends AbstractMigration {
    public function change() {
        $table = $this->table('participant');
        $table->addColumn('match_id', 'string')
              ->addColumn('match_request_uuid', 'string')
              ->addColumn('player_id', 'string')
              ->addColumn('opponent_id', 'string')
              ->create();
    }

    public function up() {}

    public function down() {}
}
