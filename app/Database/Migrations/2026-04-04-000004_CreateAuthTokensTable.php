<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTokensTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('auth_tokens')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
            ],
            'token_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['type', 'email']);
        $this->forge->addKey(['type', 'token_hash']);
        $this->forge->addKey('expires_at');
        $this->forge->createTable('auth_tokens');
    }

    public function down()
    {
        if ($this->db->tableExists('auth_tokens')) {
            $this->forge->dropTable('auth_tokens');
        }
    }
}
