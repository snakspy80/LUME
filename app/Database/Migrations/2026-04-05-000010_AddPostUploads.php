<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPostUploads extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('posts') && ! $this->db->fieldExists('video_file', 'posts')) {
            $this->forge->addColumn('posts', [
                'video_file' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'video_url',
                ],
            ]);
        }

        if (! $this->db->tableExists('post_assets')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'post_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                ],
                'user_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                ],
                'file_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'file_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'mime_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'file_size' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'asset_kind' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'note',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('post_id');
            $this->forge->addKey('user_id');
            $this->forge->addKey('asset_kind');
            $this->forge->createTable('post_assets');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('post_assets')) {
            $this->forge->dropTable('post_assets');
        }

        if ($this->db->tableExists('posts') && $this->db->fieldExists('video_file', 'posts')) {
            $this->forge->dropColumn('posts', 'video_file');
        }
    }
}
