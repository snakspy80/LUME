<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSocialTables extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('user_follows')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'follower_user_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                ],
                'followed_user_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['follower_user_id', 'followed_user_id']);
            $this->forge->addKey('followed_user_id');
            $this->forge->createTable('user_follows');
        }

        if (! $this->db->tableExists('notifications')) {
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
                ],
                'actor_user_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
                'type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                ],
                'message' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'link' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'related_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => true,
                ],
                'related_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
                'read_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['user_id', 'read_at']);
            $this->forge->addKey('created_at');
            $this->forge->createTable('notifications');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('notifications')) {
            $this->forge->dropTable('notifications');
        }

        if ($this->db->tableExists('user_follows')) {
            $this->forge->dropTable('user_follows');
        }
    }
}
