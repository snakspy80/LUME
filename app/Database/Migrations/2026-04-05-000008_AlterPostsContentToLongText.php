<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPostsContentToLongText extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('posts') || ! $this->db->fieldExists('content', 'posts')) {
            return;
        }

        $this->db->query('ALTER TABLE posts MODIFY content LONGTEXT NOT NULL');
    }

    public function down()
    {
        if (! $this->db->tableExists('posts') || ! $this->db->fieldExists('content', 'posts')) {
            return;
        }

        $this->db->query('ALTER TABLE posts MODIFY content TEXT NOT NULL');
    }
}
