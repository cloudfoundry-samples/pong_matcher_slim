<?php

use Phinx\Migration\AbstractMigration;

class CreateMatchRequest extends AbstractMigration {
    public function change() {
        $table = $this->table('matchrequest');
        $table->addColumn('uuid', 'string');
        $table->addColumn('player', 'string');
    }

    public function up() {}

    public function down() {}
}
