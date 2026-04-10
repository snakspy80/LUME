<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCourseCommentsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('course_comments')) {
            return;
        }

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
            'parent_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'review',
            ],
            'content' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('parent_id');
        $this->forge->addKey(['type', 'created_at']);
        $this->forge->createTable('course_comments');
    }

    public function down()
    {
        if ($this->db->tableExists('course_comments')) {
            $this->forge->dropTable('course_comments');
        }
    }
}
