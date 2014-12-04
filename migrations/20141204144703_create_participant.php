<?php

use Phinx\Migration\AbstractMigration;

class CreateParticipant extends AbstractMigration {
    public function change() {
        $table = $this->table('participant');
        $table->addColumn('match_id', 'string');
        $table->addColumn('match_request_uuid', 'string');
        $table->addColumn('player_id', 'string');
        $table->addColumn('opponent_id', 'string');
    }

    public function up() {}

    public function down() {}
}
