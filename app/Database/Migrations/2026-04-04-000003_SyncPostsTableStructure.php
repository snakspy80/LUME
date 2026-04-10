<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SyncPostsTableStructure extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('posts')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('user_id', 'posts')) {
            $fields['user_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ];
        }

        if (! $this->db->fieldExists('title', 'posts')) {
            $fields['title'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'user_id',
            ];
        }

        if (! $this->db->fieldExists('content', 'posts')) {
            $fields['content'] = [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'title',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('posts', $fields);
        }

        if (! $this->db->fieldExists('user_id', 'posts')) {
            return;
        }

        // Backfill nullable columns for old rows before enforcing NOT NULL.
        $this->db->query('UPDATE posts SET title = COALESCE(title, "Untitled Post")');
        $this->db->query('UPDATE posts SET content = COALESCE(content, "")');
        $this->db->query('UPDATE posts SET user_id = COALESCE(user_id, 1)');

        $this->db->query('ALTER TABLE posts MODIFY user_id BIGINT UNSIGNED NOT NULL');
        $this->db->query('ALTER TABLE posts MODIFY title VARCHAR(150) NOT NULL');
        $this->db->query('ALTER TABLE posts MODIFY content LONGTEXT NOT NULL');

        // Add index if missing.
        $indexes = $this->db->query('SHOW INDEX FROM posts WHERE Key_name = "posts_user_id_index"')->getResultArray();
        if ($indexes === []) {
            $this->db->query('ALTER TABLE posts ADD INDEX posts_user_id_index (user_id)');
        }

        // Add FK if possible and missing.
        $fk = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'posts_user_id_foreign'")->getResultArray();
        if ($fk === [] && $this->db->tableExists('users') && $this->db->fieldExists('id', 'users')) {
            try {
                $this->db->query('ALTER TABLE posts ADD CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE');
            } catch (\Throwable $e) {
                // Ignore FK add failure if existing data cannot satisfy constraints.
            }
        }
    }

    public function down()
    {
        // Keep as no-op to avoid destructive schema changes on rollback.
    }
}
