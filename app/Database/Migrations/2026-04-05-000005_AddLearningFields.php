<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLearningFields extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users')) {
            $userFields = [];

            if (! $this->db->fieldExists('college', 'users')) {
                $userFields['college'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'email',
                ];
            }

            if (! $this->db->fieldExists('bio', 'users')) {
                $userFields['bio'] = [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'college',
                ];
            }

            if ($userFields !== []) {
                $this->forge->addColumn('users', $userFields);
            }
        }

        if ($this->db->tableExists('posts')) {
            $postFields = [];

            if (! $this->db->fieldExists('category', 'posts')) {
                $postFields['category'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'title',
                ];
            }

            if (! $this->db->fieldExists('video_url', 'posts')) {
                $postFields['video_url'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'content',
                ];
            }

            if (! $this->db->fieldExists('is_published', 'posts')) {
                $postFields['is_published'] = [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'video_url',
                ];
            }

            if ($postFields !== []) {
                $this->forge->addColumn('posts', $postFields);
            }
        }
    }

    public function down()
    {
        // Non-destructive rollback by design.
    }
}
