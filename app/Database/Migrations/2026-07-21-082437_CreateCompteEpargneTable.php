<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEpargneClientTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'id_client_source' => [
                'type' => 'INTEGER',
            ],
            'id_client_destination' => [
                'type' => 'INTEGER',
            ],
            'pourcentage_epargne' => [
                'type'    => 'REAL',
            ],
            'date_creation' => [
                'type'    => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_client_source', 'client', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_client_destination', 'client', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('epargne_client');
    }

    public function down()
    {
        $this->forge->dropTable('epargne_client');
    }
}

